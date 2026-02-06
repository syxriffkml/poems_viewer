<script>
	import { onMount } from 'svelte';
	import { authStore } from '$lib/stores/auth';
	import { goto } from '$app/navigation';
	import { aiService } from '$lib/services/api';
	import { auth } from '$lib/services/firebase';
	import { signOut } from 'firebase/auth';

	let prompt = '';
	let generatedPoem = '';
	let loading = false;
	let error = '';
	let $authStore;

	authStore.subscribe(value => {
		$authStore = value;
	});

	onMount(() => {
		// Redirect to login if not authenticated
		if (!$authStore.loading && !$authStore.user) {
			goto('/auth/login');
		}
	});

	async function handleGenerate() {
		if (!prompt.trim()) {
			error = 'Please enter a prompt';
			return;
		}

		loading = true;
		error = '';
		generatedPoem = '';

		try {
			const response = await aiService.generatePoem(prompt);
			if (response.success) {
				generatedPoem = response.data.poem;
			} else {
				error = 'Failed to generate poem';
			}
		} catch (err) {
			error = err.response?.data?.error?.message || 'Failed to generate poem. Please try again.';
		} finally {
			loading = false;
		}
	}

	async function handleLogout() {
		await signOut(auth);
		goto('/');
	}

	function copyToClipboard() {
		navigator.clipboard.writeText(generatedPoem);
		alert('Poem copied to clipboard!');
	}
</script>

<svelte:head>
	<title>Create Poem - Victorian Poems</title>
</svelte:head>

{#if $authStore.loading}
	<div class="min-h-screen flex items-center justify-center">
		<div class="text-2xl text-gold-600">Loading...</div>
	</div>
{:else if $authStore.user}
	<div class="container mx-auto px-4 py-8">
		<!-- Header with user info -->
		<div class="flex justify-between items-center mb-8">
			<div>
				<h1 class="text-4xl font-bold">Create a Poem</h1>
				<p class="text-sepia-700">Welcome, {$authStore.user.displayName || $authStore.user.email}</p>
			</div>
			<button on:click={handleLogout} class="btn-victorian-secondary">
				Sign Out
			</button>
		</div>

		<div class="grid md:grid-cols-2 gap-8">
			<!-- Input Section -->
			<div>
				<div class="card-victorian">
					<h2 class="text-2xl font-bold mb-4">🪶 AI Poem Generator</h2>

					{#if error}
						<div class="bg-burgundy-500 text-parchment-50 p-3 rounded mb-4">
							{error}
						</div>
					{/if}

					<form on:submit|preventDefault={handleGenerate} class="space-y-4">
						<div>
							<label for="prompt" class="block text-sm font-semibold mb-2">
								Enter your prompt
							</label>
							<textarea
								id="prompt"
								bind:value={prompt}
								class="input-victorian min-h-[120px]"
								placeholder="Write about moonlight and roses..."
								disabled={loading}
							></textarea>
							<p class="text-xs text-sepia-600 mt-1">
								Describe the theme, mood, or subject of your poem
							</p>
						</div>

						<button
							type="submit"
							class="btn-victorian w-full"
							disabled={loading}
						>
							{loading ? 'Generating...' : 'Generate Poem'}
						</button>
					</form>

					<div class="divider-flourish my-6">
						<span>✦</span>
					</div>

					<div class="text-center">
						<p class="text-sm text-sepia-700 mb-2">Quick prompts:</p>
						<div class="flex flex-wrap gap-2 justify-center">
							<button
								class="px-3 py-1 bg-parchment-200 hover:bg-parchment-300 border border-sepia-300 text-sm rounded"
								on:click={() => prompt = 'A melancholic poem about autumn leaves'}
								disabled={loading}
							>
								Autumn
							</button>
							<button
								class="px-3 py-1 bg-parchment-200 hover:bg-parchment-300 border border-sepia-300 text-sm rounded"
								on:click={() => prompt = 'A romantic poem about moonlit gardens'}
								disabled={loading}
							>
								Romance
							</button>
							<button
								class="px-3 py-1 bg-parchment-200 hover:bg-parchment-300 border border-sepia-300 text-sm rounded"
								on:click={() => prompt = 'A hopeful poem about new beginnings'}
								disabled={loading}
							>
								Hope
							</button>
						</div>
					</div>
				</div>
			</div>

			<!-- Output Section -->
			<div>
				<div class="card-victorian min-h-[400px]">
					<h2 class="text-2xl font-bold mb-4">📜 Generated Poem</h2>

					{#if loading}
						<div class="flex items-center justify-center h-64">
							<div class="text-center">
								<div class="text-6xl mb-4">🪶</div>
								<p class="text-xl text-gold-600 animate-pulse">Crafting your poem...</p>
							</div>
						</div>
					{:else if generatedPoem}
						<div class="bg-parchment-50 p-6 border-2 border-sepia-400 rounded-sm mb-4">
							<div class="font-cormorant text-lg leading-relaxed whitespace-pre-wrap">
								{generatedPoem}
							</div>
						</div>

						<div class="flex gap-2">
							<button
								on:click={copyToClipboard}
								class="btn-victorian-secondary flex-1"
							>
								Copy
							</button>
							<button class="btn-victorian flex-1">
								Save Poem
							</button>
						</div>
					{:else}
						<div class="flex items-center justify-center h-64 text-center">
							<div>
								<div class="text-6xl mb-4">✒️</div>
								<p class="text-lg text-sepia-600">
									Enter a prompt and generate your first poem
								</p>
							</div>
						</div>
					{/if}
				</div>
			</div>
		</div>

		<!-- Tips Section -->
		<div class="mt-8 card-victorian">
			<h3 class="text-xl font-bold mb-3">✨ Tips for Better Poems</h3>
			<ul class="grid md:grid-cols-3 gap-4 text-sm">
				<li class="flex gap-2">
					<span class="text-gold-600">•</span>
					<span>Be specific about mood and setting</span>
				</li>
				<li class="flex gap-2">
					<span class="text-gold-600">•</span>
					<span>Include sensory details (sights, sounds)</span>
				</li>
				<li class="flex gap-2">
					<span class="text-gold-600">•</span>
					<span>Mention poetic style if desired (sonnet, haiku, etc.)</span>
				</li>
			</ul>
		</div>
	</div>
{/if}
