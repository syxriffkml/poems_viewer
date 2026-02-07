<script>
	import { onMount } from 'svelte';
	import { db } from '$lib/services/firebase';
	import { collection, query, where, getDocs, limit } from 'firebase/firestore';
	import LoadingOverlay from '$lib/components/LoadingOverlay.svelte';
	import { Search, SlidersHorizontal, X, Tag, Heart, TrendingUp, Scroll, User, Calendar, Eye, Sparkles, BookOpen, ArrowUp, ArrowDown } from 'lucide-svelte';

	let poems = [];
	let loading = true;
	let error = '';
	let selectedCategory = '';
	let selectedSentiment = '';
	let searchQuery = '';
	let showFilters = false;
	let showScrollButtons = false;

	// Unique categories and sentiments from loaded poems
	let categories = [];
	let sentiments = [];

	onMount(() => {
		loadPublicPoems();

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

	async function loadPublicPoems() {
		loading = true;
		error = '';

		try {
			const q = query(
				collection(db, 'poems'),
				where('isPublic', '==', true),
				limit(100)
			);

			const querySnapshot = await getDocs(q);
			poems = querySnapshot.docs.map(doc => ({
				id: doc.id,
				...doc.data()
			}));

			// Sort by createdAt in JavaScript
			poems.sort((a, b) => {
				const aTime = a.createdAt?.toMillis ? a.createdAt.toMillis() : 0;
				const bTime = b.createdAt?.toMillis ? b.createdAt.toMillis() : 0;
				return bTime - aTime;
			});

			// Extract unique categories and sentiments
			extractFilters();
		} catch (err) {
			console.error('Error loading poems:', err);
			error = 'Failed to load gallery. Please try again.';
		} finally {
			loading = false;
		}
	}

	function extractFilters() {
		const categorySet = new Set();
		const sentimentSet = new Set();

		poems.forEach(poem => {
			if (poem.categories && Array.isArray(poem.categories)) {
				poem.categories.forEach(cat => categorySet.add(cat));
			}
			if (poem.sentiment) {
				sentimentSet.add(poem.sentiment);
			}
		});

		categories = Array.from(categorySet).sort();
		sentiments = Array.from(sentimentSet).sort();
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

	// Filter poems based on selected filters and search query
	$: filteredPoems = poems.filter(poem => {
		// Category filter
		if (selectedCategory && (!poem.categories || !poem.categories.includes(selectedCategory))) {
			return false;
		}

		// Sentiment filter
		if (selectedSentiment && poem.sentiment !== selectedSentiment) {
			return false;
		}

		// Search filter
		if (searchQuery) {
			const search = searchQuery.toLowerCase();
			const titleMatch = poem.title?.toLowerCase().includes(search);
			const contentMatch = poem.content?.toLowerCase().includes(search);
			const authorMatch = poem.authorUsername?.toLowerCase().includes(search);

			if (!titleMatch && !contentMatch && !authorMatch) {
				return false;
			}
		}

		return true;
	});

	function clearFilters() {
		selectedCategory = '';
		selectedSentiment = '';
		searchQuery = '';
	}
</script>

<svelte:head>
	<title>Gallery - Victorian Poems</title>
</svelte:head>

<LoadingOverlay show={loading} message="Loading gallery..." />

<div class="container mx-auto px-4 py-8">
	<!-- Header -->
	<div class="mb-8 text-center">
		<div class="flex items-center justify-center gap-3 mb-3">
			<Scroll size={40} class="text-gold-600" />
			<h1 class="text-4xl md:text-5xl font-bold">Poetry Gallery</h1>
		</div>
		<p class="text-sepia-700 text-lg">Explore poems from our community</p>
	</div>

	<!-- Search and Filter -->
	<div class="mb-6 space-y-3">
		<!-- Search Bar (Always Visible) -->
		<div class="card-victorian">
			<div class="flex gap-3 items-center">
				<Search size={20} class="text-sepia-600" />
				<input
					type="text"
					bind:value={searchQuery}
					class="input-victorian flex-1"
					placeholder="Search by title, content, or author..."
				/>
				<button
					on:click={() => showFilters = !showFilters}
					class="btn-victorian-secondary py-2 px-4 flex items-center gap-2 whitespace-nowrap"
				>
					<SlidersHorizontal size={18} />
					<span>Filters</span>
					{#if selectedCategory || selectedSentiment}
						<span class="px-2 py-0.5 bg-gold-500 text-ink-900 rounded-full text-xs font-bold">
							{(selectedCategory ? 1 : 0) + (selectedSentiment ? 1 : 0)}
						</span>
					{/if}
				</button>
			</div>
		</div>

		<!-- Collapsible Filters -->
		{#if showFilters}
			<div class="card-victorian fade-in">
				<div class="flex items-center justify-between mb-4">
					<h3 class="text-lg font-semibold flex items-center gap-2">
						<SlidersHorizontal size={20} />
						<span>Filter Options</span>
					</h3>
					{#if selectedCategory || selectedSentiment}
						<button on:click={clearFilters} class="text-sm text-burgundy-500 hover:text-burgundy-600 font-semibold flex items-center gap-1">
							<X size={16} />
							<span>Clear All</span>
						</button>
					{/if}
				</div>

				<div class="grid md:grid-cols-2 gap-4">
					<div>
						<label for="category" class="flex items-center gap-2 text-sm font-semibold mb-2">
							<Tag size={16} />
							<span>Category</span>
						</label>
						<select
							id="category"
							bind:value={selectedCategory}
							class="input-victorian"
						>
							<option value="">All Categories</option>
							{#each categories as category}
								<option value={category}>{category}</option>
							{/each}
						</select>
					</div>

					<div>
						<label for="sentiment" class="flex items-center gap-2 text-sm font-semibold mb-2">
							<Heart size={16} />
							<span>Sentiment</span>
						</label>
						<select
							id="sentiment"
							bind:value={selectedSentiment}
							class="input-victorian"
						>
							<option value="">All Sentiments</option>
							{#each sentiments as sentiment}
								<option value={sentiment}>{sentiment}</option>
							{/each}
						</select>
					</div>
				</div>
			</div>
		{/if}
	</div>

	<!-- Results Count -->
	{#if !loading && poems.length > 0}
		<div class="mb-4 text-center text-sm text-sepia-600 flex items-center justify-center gap-2">
			<TrendingUp size={16} />
			<span>Showing <strong>{filteredPoems.length}</strong> of <strong>{poems.length}</strong> poems</span>
		</div>
	{/if}

	<!-- Loading State -->
	{#if loading}
		<!-- Loading handled by overlay -->
	{:else if error}
		<div class="card-victorian text-center py-16">
			<X size={64} class="text-burgundy-500 mx-auto mb-4" />
			<h2 class="text-2xl font-bold mb-3">{error}</h2>
			<button on:click={loadPublicPoems} class="btn-victorian mt-4">
				Try Again
			</button>
		</div>
	{:else if filteredPoems.length === 0}
		<div class="card-victorian text-center py-16">
			<Search size={64} class="text-sepia-400 mx-auto mb-4" />
			<h2 class="text-2xl font-bold mb-3">No poems found</h2>
			<p class="text-sepia-700 mb-6">
				{#if selectedCategory || selectedSentiment || searchQuery}
					Try adjusting your filters or search query
				{:else}
					Be the first to share a poem with the community!
				{/if}
			</p>
			{#if selectedCategory || selectedSentiment || searchQuery}
				<button on:click={clearFilters} class="btn-victorian">
					Clear Filters
				</button>
			{:else}
				<a href="/create" class="btn-victorian">
					Create a Poem
				</a>
			{/if}
		</div>
	{:else}
		<!-- Poems Grid -->
		<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
			{#each filteredPoems as poem (poem.id)}
				<div class="card-victorian hover-lift">
					<!-- Poem Header -->
					<div class="mb-4">
						<h3 class="text-xl font-bold mb-2 line-clamp-2">{poem.title}</h3>
						<div class="flex items-center gap-2 text-sm text-sepia-600 mb-2">
							<a
								href="/users/{poem.authorUsername}"
								class="hover:text-gold-600 transition-colors flex items-center gap-1"
							>
								<User size={14} />
								<span>{poem.authorUsername}</span>
							</a>
							<span>•</span>
							<span class="flex items-center gap-1">
								<Calendar size={14} />
								<span>{formatDate(poem.createdAt)}</span>
							</span>
						</div>
						<div class="flex flex-wrap items-center gap-2 text-xs">
							{#if poem.isAIGenerated}
								<span class="px-2 py-0.5 bg-gold-100 border border-gold-400 rounded flex items-center gap-1">
									<Sparkles size={12} />
									<span>AI Generated</span>
								</span>
							{/if}
							{#if poem.sentiment}
								<span class="px-2 py-0.5 bg-parchment-200 border border-sepia-400 rounded">
									{poem.sentiment}
								</span>
							{/if}
							{#if poem.categories && poem.categories.length > 0}
								{#each poem.categories.slice(0, 2) as category}
									<span class="px-2 py-0.5 bg-sepia-100 border border-sepia-300 rounded">
										{category}
									</span>
								{/each}
							{/if}
						</div>
					</div>

					<!-- Poem Preview -->
					<div class="bg-parchment-50 p-4 border border-sepia-300 rounded mb-4 min-h-[120px]">
						<p class="font-cormorant text-sm leading-relaxed whitespace-pre-wrap text-ink-700">
							{truncateContent(poem.content)}
						</p>
					</div>

					<!-- View Button -->
					<a
						href="/poems/{poem.id}"
						class="btn-victorian w-full text-center py-2 text-sm flex items-center justify-center gap-2"
					>
						<Eye size={16} />
						<span>Read Full Poem</span>
					</a>
				</div>
			{/each}
		</div>
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
