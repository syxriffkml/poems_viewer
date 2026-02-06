<script>
	import { onMount } from 'svelte';
	import { page } from '$app/stores';
	import { authStore } from '$lib/stores/auth';
	import { goto } from '$app/navigation';
	import { db } from '$lib/services/firebase';
	import { doc, getDoc } from 'firebase/firestore';

	let poem = null;
	let loading = true;
	let error = '';

	onMount(() => {
		loadPoem();
	});

	async function loadPoem() {
		loading = true;
		error = '';

		try {
			const poemId = $page.params.id;
			const docRef = doc(db, 'poems', poemId);
			const docSnap = await getDoc(docRef);

			if (!docSnap.exists()) {
				error = 'Poem not found';
				return;
			}

			poem = { id: docSnap.id, ...docSnap.data() };

			// Check permissions
			const user = $authStore.user;
			if (!poem.isPublic && (!user || poem.authorId !== user.uid)) {
				error = 'You do not have permission to view this poem';
				poem = null;
			}
		} catch (err) {
			console.error('Error loading poem:', err);
			error = 'Failed to load poem. Please try again.';
		} finally {
			loading = false;
		}
	}

	function formatDate(timestamp) {
		if (!timestamp) return 'Unknown date';
		const date = timestamp.toDate ? timestamp.toDate() : new Date(timestamp);
		return date.toLocaleDateString('en-US', {
			year: 'numeric',
			month: 'long',
			day: 'numeric'
		});
	}

	function copyToClipboard() {
		navigator.clipboard.writeText(poem.content);
		alert('Poem copied to clipboard!');
	}
</script>

<svelte:head>
	<title>{poem?.title || 'View Poem'} - Victorian Poems</title>
</svelte:head>

<div class="container mx-auto px-4 py-8 max-w-4xl">
	{#if loading}
		<div class="flex items-center justify-center py-20">
			<div class="text-center">
				<div class="text-6xl mb-4">📜</div>
				<p class="text-xl text-gold-600 animate-pulse">Loading poem...</p>
			</div>
		</div>
	{:else if error}
		<div class="card-victorian text-center py-16">
			<div class="text-6xl mb-4">❌</div>
			<h2 class="text-2xl font-bold mb-3">{error}</h2>
			<div class="flex gap-3 justify-center mt-6">
				<a href="/poems" class="btn-victorian-secondary">
					Back to My Poems
				</a>
				<a href="/gallery" class="btn-victorian">
					Browse Gallery
				</a>
			</div>
		</div>
	{:else if poem}
		<!-- Poem Header -->
		<div class="mb-8">
			<h1 class="text-4xl md:text-5xl font-bold mb-4">{poem.title}</h1>
			<div class="flex flex-wrap items-center gap-3 text-sm text-sepia-600">
				<span>By {poem.authorUsername}</span>
				<span>•</span>
				<span>{formatDate(poem.createdAt)}</span>
				{#if poem.isAIGenerated}
					<span class="px-2 py-1 bg-gold-100 border border-gold-400 rounded">
						AI Generated
					</span>
				{/if}
				<span class="px-2 py-1 rounded"
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

		<!-- Poem Content -->
		<div class="card-victorian mb-6">
			<div class="bg-parchment-50 p-8 border-2 border-sepia-400 rounded">
				<div class="font-cormorant text-xl leading-relaxed whitespace-pre-wrap text-ink-800"
					style="font-family: {poem.formatting?.fontFamily || 'Crimson Text'};
					       font-size: {poem.formatting?.fontSize || 16}px;
					       color: {poem.formatting?.color || '#1f1e1a'};
					       text-align: {poem.formatting?.alignment || 'left'};
					       line-height: {poem.formatting?.lineSpacing || 1.6};">
					{poem.content}
				</div>
			</div>
		</div>

		<!-- AI Prompt (if AI generated) -->
		{#if poem.isAIGenerated && poem.aiPrompt}
			<div class="card-victorian mb-6 bg-parchment-100">
				<h3 class="text-lg font-bold mb-2">✨ AI Prompt</h3>
				<p class="text-sepia-700 italic">{poem.aiPrompt}</p>
			</div>
		{/if}

		<!-- Actions -->
		<div class="card-victorian">
			<div class="flex flex-wrap gap-3">
				<button on:click={copyToClipboard} class="btn-victorian-secondary">
					Copy to Clipboard
				</button>
				{#if $authStore.user && poem.authorId === $authStore.user.uid}
					<a href="/poems/{poem.id}/edit" class="btn-victorian">
						Edit Poem
					</a>
					<a href="/poems" class="btn-victorian-secondary">
						Back to My Poems
					</a>
				{:else}
					<a href="/gallery" class="btn-victorian-secondary">
						Back to Gallery
					</a>
				{/if}
			</div>
		</div>
	{/if}
</div>
