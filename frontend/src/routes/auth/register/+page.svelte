<script>
	import RegisterForm from '$lib/components/auth/RegisterForm.svelte';
</script>

<svelte:head>
	<title>Register - Victorian Poems</title>
</svelte:head>

<div class="container mx-auto px-4 py-16">
	<div class="text-center mb-8">
		<h1 class="text-5xl font-bold mb-4">Join Our Community</h1>
		<p class="text-lg text-sepia-700">Begin your poetic journey</p>
	</div>

	<RegisterForm />
</div>
