<script>
	import { onMount } from 'svelte';
	import { page } from '$app/stores';
	import { authStore } from '$lib/stores/auth';
	import { goto } from '$app/navigation';
	import { db } from '$lib/services/firebase';
	import { doc, getDoc } from 'firebase/firestore';
	import Modal from '$lib/components/Modal.svelte';
	import LoadingOverlay from '$lib/components/LoadingOverlay.svelte';
	import { X, Sparkles, Globe, Lock, Copy, Edit, ArrowLeft, BookOpen, User, Calendar, Share2 } from 'lucide-svelte';

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
		showModal = true;
		modalType = 'success';
		modalTitle = 'Copied!';
		modalMessage = 'Poem copied to clipboard';
	}

	function sharePoem() {
		if (!poem.shareId) return;
		const shareUrl = `${window.location.origin}/share/${poem.shareId}`;
		navigator.clipboard.writeText(shareUrl);
		showModal = true;
		modalType = 'success';
		modalTitle = 'Share Link Copied!';
		modalMessage = 'Anyone with this link can view the poem';
	}
</script>

<svelte:head>
	<title>{poem?.title || 'View Poem'} - Victorian Poems</title>
</svelte:head>

<Modal bind:show={showModal} title={modalTitle} type={modalType}>
	<p>{modalMessage}</p>
</Modal>

<LoadingOverlay show={loading} message="Loading poem..." />

<div class="container mx-auto px-4 py-8 max-w-4xl">
	{#if loading}
		<!-- Loading handled by overlay -->
	{:else if error}
		<div class="card-victorian text-center py-16">
			<X size={64} class="text-burgundy-500 mx-auto mb-4" />
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
				<a href="/profile/{poem.authorId}" class="flex items-center gap-1 hover:text-gold-600 transition-colors"><User size={14} /> {poem.authorUsername}</a>
				<span>•</span>
				<span class="flex items-center gap-1"><Calendar size={14} /> {formatDate(poem.createdAt)}</span>
				{#if poem.isAIGenerated}
					<span class="px-2 py-1 bg-gold-100 border border-gold-400 rounded flex items-center gap-1">
						<Sparkles size={14} /> AI Generated
					</span>
				{/if}
				<span class="px-2 py-1 rounded flex items-center gap-1"
					class:bg-green-100={poem.isPublic}
					class:border-green-400={poem.isPublic}
					class:border={poem.isPublic}
					class:bg-sepia-100={!poem.isPublic}
					class:border-sepia-400={!poem.isPublic}
				>
					{#if poem.isPublic}
						<Globe size={14} /> Public
					{:else}
						<Lock size={14} /> Private
					{/if}
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
				<h3 class="text-lg font-bold mb-2 flex items-center gap-2"><Sparkles size={20} class="text-gold-600" /> AI Prompt</h3>
				<p class="text-sepia-700 italic">{poem.aiPrompt}</p>
			</div>
		{/if}

		<!-- Actions -->
		<div class="card-victorian">
			<div class="flex flex-wrap gap-3">
				<button on:click={copyToClipboard} class="btn-victorian-secondary flex items-center gap-2">
					<Copy size={18} /> Copy to Clipboard
				</button>
				{#if poem.shareId}
					<button on:click={sharePoem} class="btn-victorian flex items-center gap-2">
						<Share2 size={18} /> Share Poem
					</button>
				{/if}
				{#if $authStore.user && poem.authorId === $authStore.user.uid}
					<a href="/poems/{poem.id}/edit" class="btn-victorian flex items-center gap-2">
						<Edit size={18} /> Edit Poem
					</a>
					<a href="/poems" class="btn-victorian-secondary flex items-center gap-2">
						<ArrowLeft size={18} /> Back to My Poems
					</a>
				{:else}
					<a href="/gallery" class="btn-victorian-secondary flex items-center gap-2">
						<BookOpen size={18} /> Back to Gallery
					</a>
				{/if}
			</div>
		</div>
	{/if}
</div>
