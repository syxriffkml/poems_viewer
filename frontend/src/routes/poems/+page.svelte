<script>
	import { onMount } from 'svelte';
	import { authStore } from '$lib/stores/auth';
	import { goto } from '$app/navigation';
	import { db } from '$lib/services/firebase';
	import { collection, query, where, getDocs, deleteDoc, doc, updateDoc } from 'firebase/firestore';
	import Modal from '$lib/components/Modal.svelte';
	import LoadingOverlay from '$lib/components/LoadingOverlay.svelte';
	import { Eye, Edit, Globe, Lock, Trash2, Sparkles, ArrowUp, ArrowDown, BookMarked, Search } from 'lucide-svelte';

	let poems = [];
	let loading = true;
	let error = '';
	let showScrollButtons = false;
	let searchQuery = '';

	// Modal state
	let showModal = false;
	let modalTitle = '';
	let modalMessage = '';
	let modalType = 'info';
	let modalOnConfirm = null;

	onMount(() => {
		if (!$authStore.user) {
			goto('/auth/login');
			return;
		}
		loadPoems();

		// Show scroll buttons when user scrolls
		window.addEventListener('scroll', handleScroll);
		return () => window.removeEventListener('scroll', handleScroll);
	});

	function handleScroll() {
		showScrollButtons = window.scrollY > 300;
	}

	function scrollToTop() {
		window.scrollTo({ top: 0, behavior: 'smooth' });
	}

	function scrollToBottom() {
		window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
	}

	async function loadPoems() {
		loading = true;
		error = '';

		try {
			const user = $authStore.user;
			const q = query(
				collection(db, 'poems'),
				where('authorId', '==', user.uid)
			);

			const querySnapshot = await getDocs(q);
			poems = querySnapshot.docs.map(doc => ({
				id: doc.id,
				...doc.data()
			}));

			// Sort by createdAt in JavaScript (to avoid Firestore composite index requirement)
			poems.sort((a, b) => {
				const aTime = a.createdAt?.toMillis ? a.createdAt.toMillis() : 0;
				const bTime = b.createdAt?.toMillis ? b.createdAt.toMillis() : 0;
				return bTime - aTime;
			});
		} catch (err) {
			console.error('Error loading poems:', err);
			error = `Failed to load poems: ${err.message}`;
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

			// Success notification
			showModal = true;
			modalType = 'success';
			modalTitle = 'Updated';
			modalMessage = `Poem is now ${!currentStatus ? 'public' : 'private'}`;
		} catch (err) {
			console.error('Error updating poem:', err);
			showModal = true;
			modalType = 'error';
			modalTitle = 'Error';
			modalMessage = 'Failed to update poem visibility';
		}
	}

	function confirmDelete(poemId, title) {
		showModal = true;
		modalType = 'confirm';
		modalTitle = 'Delete Poem';
		modalMessage = `Are you sure you want to delete "${title}"? This action cannot be undone.`;
		modalOnConfirm = () => deletePoem(poemId, title);
	}

	async function deletePoem(poemId, title) {
		try {
			await deleteDoc(doc(db, 'poems', poemId));
			poems = poems.filter(p => p.id !== poemId);

			// Success notification
			showModal = true;
			modalType = 'success';
			modalTitle = 'Deleted';
			modalMessage = 'Poem deleted successfully';
		} catch (err) {
			console.error('Error deleting poem:', err);
			showModal = true;
			modalType = 'error';
			modalTitle = 'Error';
			modalMessage = 'Failed to delete poem';
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

	$: filteredPoems = searchQuery.trim()
		? poems.filter(p => {
			const q = searchQuery.toLowerCase();
			return p.title?.toLowerCase().includes(q) ||
				p.content?.toLowerCase().includes(q) ||
				p.categories?.some(c => c.toLowerCase().includes(q)) ||
				p.tags?.some(t => t.toLowerCase().includes(q));
		})
		: poems;
</script>

<svelte:head>
	<title>My Poems - Victorian Poems</title>
</svelte:head>

<Modal
	bind:show={showModal}
	title={modalTitle}
	type={modalType}
	onConfirm={modalOnConfirm}
>
	<p>{modalMessage}</p>
</Modal>

<LoadingOverlay show={loading} message="Loading your poems..." />

<div class="container mx-auto px-4 py-8">
	<div class="mb-8">
		<div class="flex items-center gap-3 mb-2">
			<BookMarked size={36} class="text-gold-600" />
			<h1 class="text-4xl font-bold">My Poems</h1>
		</div>
		<p class="text-sepia-700">Manage your poetry collection</p>
	</div>

	{#if error}
		<div class="bg-burgundy-500 text-parchment-50 p-4 rounded mb-6">
			{error}
		</div>
	{/if}

	{#if loading}
		<!-- Loading handled by overlay -->
	{:else if poems.length === 0}
		<div class="card-victorian text-center py-16">
			<Edit size={64} class="text-sepia-400 mx-auto mb-4" />
			<h2 class="text-2xl font-bold mb-3">No poems yet</h2>
			<p class="text-sepia-700 mb-6">Start creating beautiful poetry with AI assistance</p>
			<a href="/create" class="btn-victorian inline-flex items-center gap-2">
				<Sparkles size={18} />
				<span>Create Your First Poem</span>
			</a>
		</div>
	{:else}
		<!-- Stats (Moved to Top) -->
		<div class="mb-8 card-victorian">
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

		<!-- Search Bar -->
		<div class="mb-6 relative">
			<div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
				<Search size={18} class="text-sepia-500" />
			</div>
			<input
				type="text"
				bind:value={searchQuery}
				placeholder="Search your poems by title, content, or tags..."
				class="input-victorian pl-11"
			/>
			{#if searchQuery.trim()}
				<p class="text-sm text-sepia-600 mt-2">
					{filteredPoems.length} {filteredPoems.length === 1 ? 'poem' : 'poems'} found
				</p>
			{/if}
		</div>

		<!-- Poems Grid -->
		{#if filteredPoems.length === 0 && searchQuery.trim()}
			<div class="card-victorian text-center py-12">
				<Search size={48} class="text-sepia-400 mx-auto mb-4" />
				<h2 class="text-xl font-bold mb-2">No poems found</h2>
				<p class="text-sepia-600">Try a different search term</p>
			</div>
		{:else}
		<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
			{#each filteredPoems as poem (poem.id)}
				<div class="card-victorian hover-lift">
					<!-- Poem Header -->
					<div class="mb-4">
						<h3 class="text-xl font-bold mb-2 line-clamp-2">{poem.title}</h3>
						<div class="flex items-center gap-2 text-sm text-sepia-600">
							<span>{formatDate(poem.createdAt)}</span>
							{#if poem.isAIGenerated}
								<span class="px-2 py-0.5 bg-gold-100 border border-gold-400 rounded text-xs flex items-center gap-1">
									<Sparkles size={12} />
									<span>AI Generated</span>
								</span>
							{/if}
							<span class="px-2 py-0.5 rounded text-xs flex items-center gap-1"
								class:bg-green-100={poem.isPublic}
								class:border-green-400={poem.isPublic}
								class:border={poem.isPublic}
								class:bg-sepia-100={!poem.isPublic}
								class:border-sepia-400={!poem.isPublic}
							>
								{#if poem.isPublic}
									<Globe size={12} />
								{:else}
									<Lock size={12} />
								{/if}
								<span>{poem.isPublic ? 'Public' : 'Private'}</span>
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
								class="btn-victorian-secondary flex-1 text-center py-2 text-sm flex items-center justify-center gap-1"
							>
								<Eye size={16} />
								<span>View</span>
							</a>
							<a
								href="/poems/{poem.id}/edit"
								class="btn-victorian flex-1 text-center py-2 text-sm flex items-center justify-center gap-1"
							>
								<Edit size={16} />
								<span>Edit</span>
							</a>
						</div>
						<div class="flex gap-2">
							<button
								on:click={() => togglePublic(poem.id, poem.isPublic)}
								class="btn-victorian-secondary flex-1 text-sm py-2 flex items-center justify-center gap-1"
							>
								{#if poem.isPublic}
									<Lock size={16} />
									<span>Make Private</span>
								{:else}
									<Globe size={16} />
									<span>Make Public</span>
								{/if}
							</button>
							<button
								on:click={() => confirmDelete(poem.id, poem.title)}
								class="bg-burgundy-500 hover:bg-burgundy-600 text-parchment-50 border-2 border-burgundy-700 px-4 py-2 rounded transition-all flex-1 text-sm flex items-center justify-center gap-1"
							>
								<Trash2 size={16} />
								<span>Delete</span>
							</button>
						</div>
					</div>
				</div>
			{/each}
		</div>
		{/if}
	{/if}
</div>

<!-- Scroll Buttons -->
{#if showScrollButtons}
	<div class="fixed bottom-8 right-8 flex flex-col gap-2 z-40">
		<button
			on:click={scrollToTop}
			class="btn-victorian p-3 rounded-full shadow-lg hover:shadow-xl transition-all"
			aria-label="Scroll to top"
		>
			<ArrowUp size={20} />
		</button>
		<button
			on:click={scrollToBottom}
			class="btn-victorian-secondary p-3 rounded-full shadow-lg hover:shadow-xl transition-all"
			aria-label="Scroll to bottom"
		>
			<ArrowDown size={20} />
		</button>
	</div>
{/if}
