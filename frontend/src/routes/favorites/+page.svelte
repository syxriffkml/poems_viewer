<script>
	import { onMount } from 'svelte';
	import { goto } from '$app/navigation';
	import { db } from '$lib/services/firebase';
	import { authStore } from '$lib/stores/auth';
	import { collection, query, where, getDocs, deleteDoc, doc } from 'firebase/firestore';
	import LoadingOverlay from '$lib/components/LoadingOverlay.svelte';
	import Modal from '$lib/components/Modal.svelte';
	import { calculateReadingTime } from '$lib/utils/readingTime';
	import { Heart, Search, User, Calendar, Eye, Sparkles, Tag, Trash2, ArrowUp, ArrowDown, Clock } from 'lucide-svelte';

	let favorites = [];
	let loading = true;
	let error = '';
	let searchQuery = '';
	let selectedCategory = '';
	let selectedSentiment = '';
	let showScrollButtons = false;

	let categories = [];
	let sentiments = [];

	// Modal state
	let showModal = false;
	let modalTitle = '';
	let modalMessage = '';
	let modalType = 'success';
	let modalOnConfirm = null;

	onMount(() => {
		if (!$authStore.user) {
			goto('/auth/login');
			return;
		}

		loadFavorites();

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

	async function loadFavorites() {
		loading = true;
		error = '';

		try {
			const q = query(
				collection(db, 'favorites'),
				where('userId', '==', $authStore.user.uid)
			);

			const querySnapshot = await getDocs(q);
			favorites = querySnapshot.docs.map(doc => ({
				id: doc.id,
				...doc.data()
			}));

			// Sort by createdAt (most recent bookmarks first)
			favorites.sort((a, b) => {
				const aTime = a.createdAt?.toMillis ? a.createdAt.toMillis() : new Date(a.createdAt).getTime();
				const bTime = b.createdAt?.toMillis ? b.createdAt.toMillis() : new Date(b.createdAt).getTime();
				return bTime - aTime;
			});

			extractFilters();
		} catch (err) {
			console.error('Error loading favorites:', err);
			error = 'Failed to load favorites. Please try again.';
		} finally {
			loading = false;
		}
	}

	function extractFilters() {
		const categorySet = new Set();
		const sentimentSet = new Set();

		favorites.forEach(fav => {
			if (fav.poemCategories && Array.isArray(fav.poemCategories)) {
				fav.poemCategories.forEach(cat => categorySet.add(cat));
			}
			if (fav.poemSentiment) {
				sentimentSet.add(fav.poemSentiment);
			}
		});

		categories = Array.from(categorySet).sort();
		sentiments = Array.from(sentimentSet).sort();
	}

	function confirmRemove(favoriteId, poemTitle) {
		modalTitle = 'Remove Bookmark?';
		modalMessage = `Remove "${poemTitle}" from your favorites?`;
		modalType = 'confirm';
		modalOnConfirm = () => removeFavorite(favoriteId);
		showModal = true;
	}

	async function removeFavorite(favoriteId) {
		try {
			await deleteDoc(doc(db, 'favorites', favoriteId));
			favorites = favorites.filter(f => f.id !== favoriteId);
			extractFilters(); // Refresh filters

			modalTitle = 'Removed';
			modalMessage = 'Bookmark removed from favorites';
			modalType = 'success';
			showModal = true;
		} catch (err) {
			console.error('Error removing favorite:', err);
			modalTitle = 'Error';
			modalMessage = 'Failed to remove bookmark. Please try again.';
			modalType = 'error';
			showModal = true;
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
		if (!content) return '';
		if (content.length <= maxLength) return content;
		return content.substring(0, maxLength) + '...';
	}

	// Reactive filtering
	$: filteredFavorites = favorites.filter(fav => {
		const matchesSearch = !searchQuery ||
			fav.poemTitle?.toLowerCase().includes(searchQuery.toLowerCase()) ||
			fav.poemContent?.toLowerCase().includes(searchQuery.toLowerCase()) ||
			fav.poemAuthorUsername?.toLowerCase().includes(searchQuery.toLowerCase());

		const matchesCategory = !selectedCategory ||
			(fav.poemCategories && fav.poemCategories.includes(selectedCategory));

		const matchesSentiment = !selectedSentiment ||
			fav.poemSentiment === selectedSentiment;

		return matchesSearch && matchesCategory && matchesSentiment;
	});
</script>

<svelte:head>
	<title>My Favorites - Victorian Poems</title>
</svelte:head>

<LoadingOverlay show={loading} />

<div class="container mx-auto px-4 py-8">
	<!-- Header -->
	<div class="mb-8">
		<div class="flex items-center gap-3 mb-2">
			<Heart size={36} class="text-gold-600" />
			<h1 class="text-4xl font-bold font-playfair text-ink-900">Favorite Poems</h1>
		</div>
		<p class="text-sepia-700 text-lg">Your bookmarked poetry collection</p>
	</div>

	{#if error}
		<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
			{error}
		</div>
	{:else if !loading && favorites.length === 0}
		<!-- Empty State -->
		<div class="text-center py-16">
			<Heart size={64} class="text-sepia-400 mx-auto mb-4" />
			<h2 class="text-2xl font-bold text-sepia-700 mb-2">No bookmarks yet</h2>
			<p class="text-sepia-600 mb-6">Discover beautiful poetry and bookmark your favorites</p>
			<a href="/gallery" class="btn-victorian inline-flex items-center gap-2">
				Browse Gallery
			</a>
		</div>
	{:else if !loading}
		<!-- Stats -->
		<div class="grid md:grid-cols-3 gap-4 mb-6">
			<div class="card-victorian text-center">
				<div class="text-3xl font-bold text-gold-600 mb-1">{favorites.length}</div>
				<div class="text-sm text-sepia-700">Total Bookmarks</div>
			</div>
			<div class="card-victorian text-center">
				<div class="text-3xl font-bold text-gold-600 mb-1">
					{favorites.filter(f => f.poemIsAIGenerated).length}
				</div>
				<div class="text-sm text-sepia-700">AI Generated</div>
			</div>
			<div class="card-victorian text-center">
				<div class="text-3xl font-bold text-gold-600 mb-1">{categories.length}</div>
				<div class="text-sm text-sepia-700">Categories</div>
			</div>
		</div>

		<!-- Search Bar -->
		<div class="mb-6">
			<div class="relative">
				<Search class="absolute left-4 top-1/2 transform -translate-y-1/2 text-sepia-500" size={20} />
				<input
					type="text"
					bind:value={searchQuery}
					placeholder="Search by title, content, or author..."
					class="input-victorian w-full pl-12 pr-4 py-3"
				/>
			</div>
		</div>

		<!-- Filters -->
		{#if categories.length > 0 || sentiments.length > 0}
			<div class="grid md:grid-cols-2 gap-4 mb-6">
				{#if categories.length > 0}
					<div>
						<label class="block text-sm font-semibold text-sepia-700 mb-2">
							<Tag size={14} class="inline" /> Category
						</label>
						<select bind:value={selectedCategory} class="input-victorian w-full">
							<option value="">All Categories</option>
							{#each categories as category}
								<option value={category}>{category}</option>
							{/each}
						</select>
					</div>
				{/if}

				{#if sentiments.length > 0}
					<div>
						<label class="block text-sm font-semibold text-sepia-700 mb-2">
							<Heart size={14} class="inline" /> Sentiment
						</label>
						<select bind:value={selectedSentiment} class="input-victorian w-full">
							<option value="">All Sentiments</option>
							{#each sentiments as sentiment}
								<option value={sentiment}>{sentiment}</option>
							{/each}
						</select>
					</div>
				{/if}
			</div>
		{/if}

		<!-- Results Count -->
		{#if searchQuery || selectedCategory || selectedSentiment}
			<div class="text-sm text-sepia-600 mb-4">
				Showing {filteredFavorites.length} of {favorites.length} bookmarks
			</div>
		{/if}

		<!-- Favorites Grid -->
		{#if filteredFavorites.length === 0}
			<div class="text-center py-12 text-sepia-600">
				No bookmarks match your filters
			</div>
		{:else}
			<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
				{#each filteredFavorites as favorite (favorite.id)}
					<div class="card-victorian hover-lift">
						<!-- Header -->
						<div class="mb-4">
							<h3 class="text-xl font-bold text-ink-900 mb-2 line-clamp-2 font-playfair">
								{favorite.poemTitle}
							</h3>
							<div class="flex items-center gap-2 text-sm text-sepia-600 mb-2">
								<a href="/profile/{favorite.poemAuthorId}" class="hover:text-gold-600 flex items-center gap-1">
									<User size={14} />
									{favorite.poemAuthorUsername}
								</a>
								<span>•</span>
								<span class="flex items-center gap-1">
									<Calendar size={14} />
									{formatDate(favorite.createdAt)}
								</span>
								<span>•</span>
								<span class="flex items-center gap-1">
									<Clock size={14} />
									{calculateReadingTime(favorite.poemContent)}
								</span>
							</div>

							<!-- Badges -->
							<div class="flex flex-wrap items-center gap-2 text-xs">
								{#if favorite.poemIsAIGenerated}
									<span class="px-2 py-0.5 bg-gold-100 text-gold-800 border border-gold-400 rounded flex items-center gap-1">
										<Sparkles size={12} />
										AI Generated
									</span>
								{/if}
								{#if favorite.poemSentiment}
									<span class="px-2 py-0.5 bg-parchment-200 text-sepia-800 border border-sepia-400 rounded">
										{favorite.poemSentiment}
									</span>
								{/if}
								{#each (favorite.poemCategories || []).slice(0, 2) as category}
									<span class="px-2 py-0.5 bg-sepia-100 text-sepia-800 border border-sepia-300 rounded">
										{category}
									</span>
								{/each}
							</div>
						</div>

						<!-- Preview -->
						<div class="bg-parchment-50 p-4 border border-sepia-300 rounded mb-4 min-h-[120px]">
							<p class="font-cormorant text-sm text-ink-800 leading-relaxed whitespace-pre-wrap">
								{truncateContent(favorite.poemContent)}
							</p>
						</div>

						<!-- Actions -->
						<div class="space-y-2">
							<a
								href="/poems/{favorite.poemId}"
								class="btn-victorian w-full py-2 text-center flex items-center justify-center gap-2"
							>
								<Eye size={16} />
								<span>Read Full Poem</span>
							</a>
							<button
								on:click={() => confirmRemove(favorite.id, favorite.poemTitle)}
								class="btn-victorian-secondary w-full py-2 flex items-center justify-center gap-2 text-red-700 border-red-400 hover:bg-red-50"
							>
								<Trash2 size={16} />
								<span>Remove Bookmark</span>
							</button>
						</div>
					</div>
				{/each}
			</div>
		{/if}
	{/if}
</div>

<!-- Scroll Buttons -->
{#if showScrollButtons}
	<div class="fixed bottom-6 right-6 flex flex-col gap-2 z-40">
		<button
			on:click={scrollToTop}
			class="btn-victorian p-3 rounded-full shadow-lg"
			aria-label="Scroll to top"
		>
			<ArrowUp size={20} />
		</button>
		<button
			on:click={scrollToBottom}
			class="btn-victorian p-3 rounded-full shadow-lg"
			aria-label="Scroll to bottom"
		>
			<ArrowDown size={20} />
		</button>
	</div>
{/if}

<!-- Modal -->
<Modal
	bind:show={showModal}
	title={modalTitle}
	type={modalType}
	onConfirm={modalOnConfirm}
>
	<p>{modalMessage}</p>
</Modal>
