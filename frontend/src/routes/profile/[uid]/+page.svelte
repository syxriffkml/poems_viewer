<script>
	import { onMount } from 'svelte';
	import { page } from '$app/stores';
	import { db } from '$lib/services/firebase';
	import { collection, query, where, getDocs } from 'firebase/firestore';
	import LoadingOverlay from '$lib/components/LoadingOverlay.svelte';
	import { calculateReadingTime } from '$lib/utils/readingTime';
	import { User, Calendar, Eye, Sparkles, Globe, BookMarked, Feather, ArrowUp, ArrowDown, Clock } from 'lucide-svelte';

	let poems = [];
	let authorName = '';
	let loading = true;
	let error = '';
	let showScrollButtons = false;

	onMount(() => {
		loadProfile();
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

	async function loadProfile() {
		loading = true;
		error = '';

		try {
			const uid = $page.params.uid;
			const q = query(
				collection(db, 'poems'),
				where('authorId', '==', uid),
				where('isPublic', '==', true)
			);

			const querySnapshot = await getDocs(q);
			poems = querySnapshot.docs.map(doc => ({
				id: doc.id,
				...doc.data()
			}));

			// Sort by createdAt client-side
			poems.sort((a, b) => {
				const aTime = a.createdAt?.toMillis ? a.createdAt.toMillis() : 0;
				const bTime = b.createdAt?.toMillis ? b.createdAt.toMillis() : 0;
				return bTime - aTime;
			});

			if (poems.length > 0) {
				authorName = poems[0].authorUsername || 'Unknown Poet';
			} else {
				authorName = 'Poet';
			}
		} catch (err) {
			console.error('Error loading profile:', err);
			error = 'Failed to load profile. Please try again.';
		} finally {
			loading = false;
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
	<title>{authorName}'s Poems - Victorian Poems</title>
</svelte:head>

<LoadingOverlay show={loading} message="Loading profile..." />

<div class="container mx-auto px-4 py-8">
	{#if loading}
		<!-- Loading handled by overlay -->
	{:else if error}
		<div class="card-victorian text-center py-16">
			<User size={64} class="text-sepia-400 mx-auto mb-4" />
			<h2 class="text-2xl font-bold mb-3">{error}</h2>
			<a href="/gallery" class="btn-victorian inline-flex items-center gap-2 mt-4">
				<Feather size={18} /> Back to Gallery
			</a>
		</div>
	{:else}
		<!-- Profile Header -->
		<div class="card-victorian mb-8">
			<div class="flex items-center gap-4 mb-4">
				<div class="w-16 h-16 rounded-full bg-gold-100 border-2 border-gold-400 flex items-center justify-center">
					<User size={32} class="text-gold-600" />
				</div>
				<div>
					<h1 class="text-3xl font-bold">{authorName}</h1>
					<p class="text-sepia-600 flex items-center gap-1">
						<BookMarked size={16} /> {poems.length} public {poems.length === 1 ? 'poem' : 'poems'}
					</p>
				</div>
			</div>

			<!-- Stats -->
			<div class="grid grid-cols-2 gap-4 text-center mt-4 pt-4 border-t border-sepia-300">
				<div>
					<div class="text-2xl font-bold text-gold-600">{poems.length}</div>
					<div class="text-sm text-sepia-700">Public Poems</div>
				</div>
				<div>
					<div class="text-2xl font-bold text-gold-600">
						{poems.filter(p => p.isAIGenerated).length}
					</div>
					<div class="text-sm text-sepia-700">AI Generated</div>
				</div>
			</div>
		</div>

		<!-- Poems -->
		{#if poems.length === 0}
			<div class="card-victorian text-center py-12">
				<Feather size={48} class="text-sepia-400 mx-auto mb-4" />
				<h2 class="text-xl font-bold mb-2">No public poems yet</h2>
				<p class="text-sepia-600">This poet hasn't published any poems yet</p>
			</div>
		{:else}
			<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
				{#each poems as poem (poem.id)}
					<a href="/poems/{poem.id}" class="card-victorian hover-lift block">
						<div class="mb-3">
							<h3 class="text-xl font-bold mb-2 line-clamp-2">{poem.title}</h3>
							<div class="flex items-center gap-2 text-sm text-sepia-600">
								<span class="flex items-center gap-1">
									<Calendar size={12} /> {formatDate(poem.createdAt)}
								</span>
								<span>•</span>
								<span class="flex items-center gap-1">
									<Clock size={12} /> {calculateReadingTime(poem.content)}
								</span>
								{#if poem.isAIGenerated}
									<span class="px-2 py-0.5 bg-gold-100 border border-gold-400 rounded text-xs flex items-center gap-1">
										<Sparkles size={12} /> AI
									</span>
								{/if}
							</div>
						</div>
						<div class="bg-parchment-50 p-4 border border-sepia-300 rounded">
							<p class="font-cormorant text-sm leading-relaxed whitespace-pre-wrap text-ink-700">
								{truncateContent(poem.content)}
							</p>
						</div>
						{#if poem.categories?.length > 0}
							<div class="flex flex-wrap gap-1 mt-3">
								{#each poem.categories.slice(0, 3) as category}
									<span class="px-2 py-0.5 bg-gold-100 border border-gold-400 rounded text-xs">{category}</span>
								{/each}
							</div>
						{/if}
					</a>
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
