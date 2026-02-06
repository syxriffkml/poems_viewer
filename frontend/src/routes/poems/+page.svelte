<script>
	import { onMount } from 'svelte';
	import { authStore } from '$lib/stores/auth';
	import { goto } from '$app/navigation';
	import { db } from '$lib/services/firebase';
	import { collection, query, where, orderBy, getDocs, deleteDoc, doc, updateDoc } from 'firebase/firestore';

	let poems = [];
	let loading = true;
	let error = '';

	onMount(() => {
		if (!$authStore.user) {
			goto('/auth/login');
			return;
		}
		loadPoems();
	});

	async function loadPoems() {
		loading = true;
		error = '';

		try {
			const user = $authStore.user;
			const q = query(
				collection(db, 'poems'),
				where('authorId', '==', user.uid),
				orderBy('createdAt', 'desc')
			);

			const querySnapshot = await getDocs(q);
			poems = querySnapshot.docs.map(doc => ({
				id: doc.id,
				...doc.data()
			}));
		} catch (err) {
			console.error('Error loading poems:', err);
			error = 'Failed to load poems. Please try again.';
		} finally {
			loading = false;
		}
	}

	async function togglePublic(poemId, currentStatus) {
		try {
			await updateDoc(doc(db, 'poems', poemId), {
				isPublic: !currentStatus
			});
			// Update local state
			poems = poems.map(p =>
				p.id === poemId ? { ...p, isPublic: !currentStatus } : p
			);
		} catch (err) {
			console.error('Error updating poem:', err);
			alert('Failed to update poem visibility');
		}
	}

	async function deletePoem(poemId, title) {
		if (!confirm(`Are you sure you want to delete "${title}"?`)) {
			return;
		}

		try {
			await deleteDoc(doc(db, 'poems', poemId));
			poems = poems.filter(p => p.id !== poemId);
			alert('Poem deleted successfully');
		} catch (err) {
			console.error('Error deleting poem:', err);
			alert('Failed to delete poem');
		}
	}

	function formatDate(timestamp) {
		if (!timestamp) return 'Unknown date';
		const date = timestamp.toDate ? timestamp.toDate() : new Date(timestamp);
		return date.toLocaleDateString('en-US', {
			year: 'numeric',
			month: 'short',
			day: 'numeric'
		});
	}

	function truncateContent(content, maxLength = 150) {
		if (content.length <= maxLength) return content;
		return content.slice(0, maxLength) + '...';
	}
</script>

<svelte:head>
	<title>My Poems - Victorian Poems</title>
</svelte:head>

<div class="container mx-auto px-4 py-8">
	<div class="mb-8">
		<h1 class="text-4xl font-bold">My Poems</h1>
		<p class="text-sepia-700">Manage your poetry collection</p>
	</div>

	{#if error}
		<div class="bg-burgundy-500 text-parchment-50 p-4 rounded mb-6">
			{error}
		</div>
	{/if}

	{#if loading}
		<div class="flex items-center justify-center py-20">
			<div class="text-center">
				<div class="text-6xl mb-4">📜</div>
				<p class="text-xl text-gold-600 animate-pulse">Loading your poems...</p>
			</div>
		</div>
	{:else if poems.length === 0}
		<div class="card-victorian text-center py-16">
			<div class="text-6xl mb-4">✒️</div>
			<h2 class="text-2xl font-bold mb-3">No poems yet</h2>
			<p class="text-sepia-700 mb-6">Start creating beautiful poetry with AI assistance</p>
			<a href="/create" class="btn-victorian">
				Create Your First Poem
			</a>
		</div>
	{:else}
		<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
			{#each poems as poem (poem.id)}
				<div class="card-victorian hover-lift">
					<!-- Poem Header -->
					<div class="mb-4">
						<h3 class="text-xl font-bold mb-2 line-clamp-2">{poem.title}</h3>
						<div class="flex items-center gap-2 text-sm text-sepia-600">
							<span>{formatDate(poem.createdAt)}</span>
							{#if poem.isAIGenerated}
								<span class="px-2 py-0.5 bg-gold-100 border border-gold-400 rounded text-xs">
									AI Generated
								</span>
							{/if}
							<span class="px-2 py-0.5 rounded text-xs"
								class:bg-green-100={poem.isPublic}
								class:border-green-400={poem.isPublic}
								class:border={poem.isPublic}
								class:bg-sepia-100={!poem.isPublic}
								class:border-sepia-400={!poem.isPublic}
							>
								{poem.isPublic ? 'Public' : 'Private'}
							</span>
						</div>
					</div>

					<!-- Poem Preview -->
					<div class="bg-parchment-50 p-4 border border-sepia-300 rounded mb-4 min-h-[120px]">
						<p class="font-cormorant text-sm leading-relaxed whitespace-pre-wrap text-ink-700">
							{truncateContent(poem.content)}
						</p>
					</div>

					<!-- Actions -->
					<div class="space-y-2">
						<div class="flex gap-2">
							<a
								href="/poems/{poem.id}"
								class="btn-victorian-secondary flex-1 text-center py-2 text-sm"
							>
								View
							</a>
							<a
								href="/poems/{poem.id}/edit"
								class="btn-victorian flex-1 text-center py-2 text-sm"
							>
								Edit
							</a>
						</div>
						<div class="flex gap-2">
							<button
								on:click={() => togglePublic(poem.id, poem.isPublic)}
								class="btn-victorian-secondary flex-1 text-sm py-2"
							>
								{poem.isPublic ? 'Make Private' : 'Make Public'}
							</button>
							<button
								on:click={() => deletePoem(poem.id, poem.title)}
								class="bg-burgundy-500 hover:bg-burgundy-600 text-parchment-50 border-2 border-burgundy-700 px-4 py-2 rounded transition-all flex-1 text-sm"
							>
								Delete
							</button>
						</div>
					</div>
				</div>
			{/each}
		</div>

		<!-- Stats -->
		<div class="mt-8 card-victorian">
			<div class="grid grid-cols-3 gap-4 text-center">
				<div>
					<div class="text-3xl font-bold text-gold-600">{poems.length}</div>
					<div class="text-sm text-sepia-700">Total Poems</div>
				</div>
				<div>
					<div class="text-3xl font-bold text-gold-600">
						{poems.filter(p => p.isPublic).length}
					</div>
					<div class="text-sm text-sepia-700">Public</div>
				</div>
				<div>
					<div class="text-3xl font-bold text-gold-600">
						{poems.filter(p => p.isAIGenerated).length}
					</div>
					<div class="text-sm text-sepia-700">AI Generated</div>
				</div>
			</div>
		</div>
	{/if}
</div>
