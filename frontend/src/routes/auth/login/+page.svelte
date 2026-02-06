<script>
	import LoginForm from '$lib/components/auth/LoginForm.svelte';
</script>

<svelte:head>
	<title>Sign In - Victorian Poems</title>
</svelte:head>

<div class="container mx-auto px-4 py-16">
	<div class="text-center mb-8">
		<h1 class="text-5xl font-bold mb-4">Sign In</h1>
		<p class="text-lg text-sepia-700">Access your poetic creations</p>
	</div>

	<LoginForm />
</div>
