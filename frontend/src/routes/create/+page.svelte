<script>
	import { authStore } from '$lib/stores/auth';
	import { goto } from '$app/navigation';
	import { aiService } from '$lib/services/api';
	import { db } from '$lib/services/firebase';
	import { collection, addDoc, serverTimestamp } from 'firebase/firestore';

	let prompt = '';
	let generatedPoem = '';
	let loading = false;
	let error = '';
	let showSavePrompt = false;
	let showSaveForm = false;
	let poemTitle = '';
	let saving = false;

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

	function copyToClipboard() {
		navigator.clipboard.writeText(generatedPoem);
		alert('Poem copied to clipboard!');
	}

	function handleSaveClick() {
		if (!$authStore.user) {
			showSavePrompt = true;
		} else {
			showSaveForm = true;
			poemTitle = prompt.slice(0, 50); // Default title from prompt
		}
	}

	async function handleSavePoem() {
		if (!poemTitle.trim()) {
			error = 'Please enter a title for your poem';
			return;
		}

		saving = true;
		error = '';

		try {
			const user = $authStore.user;
			const shareId = crypto.randomUUID();

			await addDoc(collection(db, 'poems'), {
				authorId: user.uid,
				authorUsername: user.displayName || user.email.split('@')[0],
				title: poemTitle.trim(),
				content: generatedPoem,
				isAIGenerated: true,
				aiPrompt: prompt,
				isPublic: false,
				shareId: shareId,
				formatting: {
					fontFamily: 'Crimson Text',
					fontSize: 16,
					color: '#1f1e1a',
					alignment: 'left',
					lineSpacing: 1.6
				},
				categories: [],
				tags: [],
				sentiment: '',
				views: 0,
				createdAt: serverTimestamp(),
				updatedAt: serverTimestamp()
			});

			// Success! Reset and redirect
			alert('Poem saved successfully!');
			goto('/create');
			generatedPoem = '';
			prompt = '';
			poemTitle = '';
			showSaveForm = false;
		} catch (err) {
			console.error('Error saving poem:', err);
			error = 'Failed to save poem. Please try again.';
		} finally {
			saving = false;
		}
	}
</script>

<svelte:head>
	<title>Create Poem - Victorian Poems</title>
</svelte:head>

<div class="container mx-auto px-4 py-8">
	<!-- Header -->
	<div class="mb-8">
		<h1 class="text-4xl font-bold">Create a Poem</h1>
		<p class="text-sepia-700">Generate beautiful poems with AI • Free, no login required</p>
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
							<button on:click={handleSaveClick} class="btn-victorian flex-1">
								Save Poem
							</button>
						</div>

						{#if showSavePrompt}
							<div class="mt-4 p-4 bg-gold-100 border-2 border-gold-500 rounded text-center">
								<p class="text-ink-900 mb-3">
									<strong>Sign in to save this poem!</strong><br/>
									<span class="text-sm">Create an account to save and manage your poems</span>
								</p>
								<div class="flex gap-2 justify-center">
									<a href="/auth/register" class="btn-victorian text-sm py-2 px-4">
										Register
									</a>
									<a href="/auth/login" class="btn-victorian-secondary text-sm py-2 px-4">
										Sign In
									</a>
								</div>
							</div>
						{/if}

						{#if showSaveForm}
							<div class="mt-4 p-4 bg-parchment-200 border-2 border-sepia-400 rounded">
								<h3 class="text-lg font-bold mb-3">Save Your Poem</h3>
								<form on:submit|preventDefault={handleSavePoem} class="space-y-3">
									<div>
										<label for="poemTitle" class="block text-sm font-semibold mb-2">
											Poem Title
										</label>
										<input
											type="text"
											id="poemTitle"
											bind:value={poemTitle}
											class="input-victorian"
											placeholder="Enter a title for your poem"
											required
											disabled={saving}
										/>
									</div>
									<div class="flex gap-2">
										<button
											type="submit"
											class="btn-victorian flex-1"
											disabled={saving}
										>
											{saving ? 'Saving...' : 'Save'}
										</button>
										<button
											type="button"
											on:click={() => showSaveForm = false}
											class="btn-victorian-secondary flex-1"
											disabled={saving}
										>
											Cancel
										</button>
									</div>
								</form>
							</div>
						{/if}
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