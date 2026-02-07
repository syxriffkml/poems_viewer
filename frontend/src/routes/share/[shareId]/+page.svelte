<script>
	import { onMount } from 'svelte';
	import { page } from '$app/stores';
	import { db } from '$lib/services/firebase';
	import { collection, query, where, getDocs } from 'firebase/firestore';
	import Modal from '$lib/components/Modal.svelte';
	import LoadingOverlay from '$lib/components/LoadingOverlay.svelte';
	import { X, Sparkles, Globe, Copy, BookOpen, User, Calendar, Share2, Feather } from 'lucide-svelte';

	let poem = null;
	let loading = true;
	let error = '';

	// Modal state
	let showModal = false;
	let modalTitle = '';
	let modalMessage = '';
	let modalType = 'info';

	onMount(() => {
		loadPoem();
	});

	async function loadPoem() {
		loading = true;
		error = '';

		try {
			const shareId = $page.params.shareId;
			const q = query(collection(db, 'poems'), where('shareId', '==', shareId));
			const querySnapshot = await getDocs(q);

			if (querySnapshot.empty) {
				error = 'Poem not found or link has expired';
				return;
			}

			const docSnap = querySnapshot.docs[0];
			poem = { id: docSnap.id, ...docSnap.data() };
		} catch (err) {
			console.error('Error loading shared poem:', err);
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
		showModal = true;
		modalType = 'success';
		modalTitle = 'Copied!';
		modalMessage = 'Poem copied to clipboard';
	}

	function copyShareLink() {
		navigator.clipboard.writeText(window.location.href);
		showModal = true;
		modalType = 'success';
		modalTitle = 'Link Copied!';
		modalMessage = 'Share link copied to clipboard';
	}
</script>

<svelte:head>
	<title>{poem?.title || 'Shared Poem'} - Victorian Poems</title>
</svelte:head>

<Modal bind:show={showModal} title={modalTitle} type={modalType}>
	<p>{modalMessage}</p>
</Modal>

<LoadingOverlay show={loading} message="Loading shared poem..." />

<div class="container mx-auto px-4 py-8 max-w-4xl">
	{#if loading}
		<!-- Loading handled by overlay -->
	{:else if error}
		<div class="card-victorian text-center py-16">
			<X size={64} class="text-burgundy-500 mx-auto mb-4" />
			<h2 class="text-2xl font-bold mb-3">{error}</h2>
			<p class="text-sepia-600 mb-6">This poem may have been removed or the link is invalid.</p>
			<div class="flex gap-3 justify-center">
				<a href="/gallery" class="btn-victorian flex items-center gap-2">
					<BookOpen size={18} /> Browse Gallery
				</a>
				<a href="/" class="btn-victorian-secondary flex items-center gap-2">
					<Feather size={18} /> Home
				</a>
			</div>
		</div>
	{:else if poem}
		<!-- Shared Badge -->
		<div class="flex items-center gap-2 mb-4 text-sm text-sepia-600">
			<Share2 size={16} />
			<span>Shared poem</span>
		</div>

		<!-- Poem Header -->
		<div class="mb-8">
			<h1 class="text-4xl md:text-5xl font-bold mb-4">{poem.title}</h1>
			<div class="flex flex-wrap items-center gap-3 text-sm text-sepia-600">
				<a href="/profile/{poem.authorId}" class="flex items-center gap-1 hover:text-gold-600 transition-colors"><User size={14} /> {poem.authorUsername}</a>
				<span>·</span>
				<span class="flex items-center gap-1"><Calendar size={14} /> {formatDate(poem.createdAt)}</span>
				{#if poem.isAIGenerated}
					<span class="px-2 py-1 bg-gold-100 border border-gold-400 rounded flex items-center gap-1">
						<Sparkles size={14} /> AI Generated
					</span>
				{/if}
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

		<!-- Categories/Tags -->
		{#if poem.categories?.length > 0 || poem.tags?.length > 0}
			<div class="card-victorian mb-6">
				{#if poem.categories?.length > 0}
					<div class="flex flex-wrap gap-2 mb-3">
						{#each poem.categories as category}
							<span class="px-3 py-1 bg-gold-100 border border-gold-400 rounded text-sm">{category}</span>
						{/each}
					</div>
				{/if}
				{#if poem.tags?.length > 0}
					<div class="flex flex-wrap gap-2">
						{#each poem.tags as tag}
							<span class="px-3 py-1 bg-parchment-100 border border-sepia-300 rounded text-sm text-sepia-700">{tag}</span>
						{/each}
					</div>
				{/if}
			</div>
		{/if}

		<!-- Actions -->
		<div class="card-victorian">
			<div class="flex flex-wrap gap-3">
				<button on:click={copyToClipboard} class="btn-victorian-secondary flex items-center gap-2">
					<Copy size={18} /> Copy Poem
				</button>
				<button on:click={copyShareLink} class="btn-victorian flex items-center gap-2">
					<Share2 size={18} /> Copy Share Link
				</button>
				<a href="/gallery" class="btn-victorian-secondary flex items-center gap-2">
					<BookOpen size={18} /> Browse Gallery
				</a>
			</div>
		</div>
	{/if}
</div>
