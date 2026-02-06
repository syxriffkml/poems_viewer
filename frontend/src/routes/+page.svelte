<script>
	// Home page - Victorian themed landing page
</script>

<svelte:head>
	<title>Victorian Poems - Where Poetry Comes Alive</title>
</svelte:head>

<div class="container mx-auto px-4 py-16">
	<!-- Hero Section -->
	<div class="text-center mb-16 fade-in">
		<div class="ornate-text text-xl mb-4">✦ Est. 2026 ✦</div>
		<h1 class="text-6xl md:text-7xl font-bold mb-6 text-ink-900">
			Victorian Poems
		</h1>
		<p class="text-2xl font-cormorant text-sepia-700 mb-8 italic">
			Where Poetry Comes Alive
		</p>
		<p class="text-lg max-w-2xl mx-auto leading-relaxed text-ink-700 mb-12">
			Create exquisite poetry with the assistance of artificial intelligence, or craft your own masterpieces with elegant Victorian styling. Share your verses with fellow poets in our distinguished gallery.
		</p>
		<div class="flex gap-4 justify-center flex-wrap">
			<button class="btn-victorian">
				Begin Creating
			</button>
			<button class="btn-victorian-secondary">
				Explore Gallery
			</button>
		</div>
	</div>

	<!-- Divider -->
	<div class="divider-flourish">
		<span>✦</span>
	</div>

	<!-- Features Section -->
	<div class="grid md:grid-cols-3 gap-8 mt-16">
		<div class="card-victorian hover-lift text-center">
			<div class="text-4xl text-gold-500 mb-4">🪶</div>
			<h3 class="text-2xl font-bold mb-3">AI-Powered Creation</h3>
			<p class="text-ink-700 leading-relaxed">
				Let our advanced AI assist you in crafting beautiful poems. Simply provide a prompt and watch poetry unfold.
			</p>
		</div>

		<div class="card-victorian hover-lift text-center">
			<div class="text-4xl text-gold-500 mb-4">✒️</div>
			<h3 class="text-2xl font-bold mb-3">Elegant Editor</h3>
			<p class="text-ink-700 leading-relaxed">
				Customize your poems with Victorian fonts, ornate styling, and rich formatting options.
			</p>
		</div>

		<div class="card-victorian hover-lift text-center">
			<div class="text-4xl text-gold-500 mb-4">📜</div>
			<h3 class="text-2xl font-bold mb-3">Share & Export</h3>
			<p class="text-ink-700 leading-relaxed">
				Share your poetry in our public gallery or export as beautiful PDFs and images.
			</p>
		</div>
	</div>

	<!-- Footer Quote -->
	<div class="mt-20 text-center">
		<blockquote class="text-xl font-cormorant italic text-sepia-700">
			"Poetry is the spontaneous overflow of powerful feelings..."
			<footer class="text-sm mt-2 text-sepia-600">— William Wordsworth</footer>
		</blockquote>
	</div>
</div>
