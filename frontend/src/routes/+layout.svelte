<script>
	import '../app.css';
	import Header from '$lib/components/Header.svelte';
</script>

<div class="min-h-screen parchment-bg">
	<Header />
	<slot />
</div>
