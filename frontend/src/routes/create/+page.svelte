<script>
	import { onMount } from 'svelte';
	import { authStore } from '$lib/stores/auth';
	import { goto } from '$app/navigation';
	import { aiService } from '$lib/services/api';
	import { db } from '$lib/services/firebase';
	import { collection, addDoc, serverTimestamp } from 'firebase/firestore';
	import Modal from '$lib/components/Modal.svelte';
	import LoadingOverlay from '$lib/components/LoadingOverlay.svelte';
	import { Sparkles, Loader2, Copy, Save, Feather, ChevronDown, ChevronUp } from 'lucide-svelte';

	let prompt = '';
	let generatedPoem = '';
	let generatedTitle = '';
	let loading = false;
	let error = '';
	let showSavePrompt = false;
	let saving = false;
	let view = 'form'; // 'form' | 'result'

	// Customization options
	let style = '';
	let length = '';
	let rhymeScheme = '';
	let tone = '';
	let showAdvanced = false;

	// Modal state
	let showModal = false;
	let modalTitle = '';
	let modalMessage = '';
	let modalType = 'info';
	let modalOnConfirm = null;

	// Option definitions
	const styleOptions = [
		{ value: '', label: 'Default (AI chooses)' },
		{ value: 'sonnet', label: 'Sonnet' },
		{ value: 'haiku', label: 'Haiku' },
		{ value: 'free_verse', label: 'Free Verse' },
		{ value: 'limerick', label: 'Limerick' },
		{ value: 'ballad', label: 'Ballad' },
		{ value: 'romantic', label: 'Romantic' },
		{ value: 'dark', label: 'Dark' },
		{ value: 'nature', label: 'Nature' }
	];

	const lengthOptions = [
		{ value: '', label: 'Default (Medium)' },
		{ value: 'short', label: 'Short (4-8 lines)' },
		{ value: 'medium', label: 'Medium (12-20 lines)' },
		{ value: 'long', label: 'Long (24+ lines)' }
	];

	const rhymeSchemeOptions = [
		{ value: '', label: 'Default (AI chooses)' },
		{ value: 'AABB', label: 'AABB (Couplets)' },
		{ value: 'ABAB', label: 'ABAB (Alternating)' },
		{ value: 'ABCB', label: 'ABCB (Simple)' },
		{ value: 'free', label: 'Free (No rhyme)' }
	];

	const toneOptions = [
		{ value: '', label: 'Default (AI chooses)' },
		{ value: 'joyful', label: 'Joyful' },
		{ value: 'melancholic', label: 'Melancholic' },
		{ value: 'dramatic', label: 'Dramatic' },
		{ value: 'peaceful', label: 'Peaceful' },
		{ value: 'playful', label: 'Playful' }
	];

	onMount(() => {
		// Restore poem from localStorage if returning from login
		const savedPoem = localStorage.getItem('pendingPoem');
		const savedTitle = localStorage.getItem('pendingPoemTitle');
		const savedPrompt = localStorage.getItem('pendingPrompt');

		if (savedPoem && $authStore.user) {
			generatedPoem = savedPoem;
			generatedTitle = savedTitle || '';
			prompt = savedPrompt || '';
			view = 'result';

			// Clear localStorage
			localStorage.removeItem('pendingPoem');
			localStorage.removeItem('pendingPoemTitle');
			localStorage.removeItem('pendingPrompt');

			// Auto-save the poem
			handleSaveClick();
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
		generatedTitle = '';

		try {
			const response = await aiService.generatePoem(prompt, {
				style,
				length,
				rhyme_scheme: rhymeScheme,
				tone
			});
			if (response.success) {
				generatedPoem = response.data.poem;
				generatedTitle = response.data.title || 'Untitled';
				view = 'result';
			} else {
				error = 'Failed to generate poem';
			}
		} catch (err) {
			error = err.response?.data?.error?.message || 'Failed to generate poem. Please try again.';
		} finally {
			loading = false;
		}
	}

	function createAnother() {
		view = 'form';
		generatedPoem = '';
		generatedTitle = '';
		prompt = '';
		error = '';
		showSavePrompt = false;
	}

	function copyToClipboard() {
		navigator.clipboard.writeText(generatedPoem);
		showModal = true;
		modalType = 'success';
		modalTitle = 'Copied!';
		modalMessage = 'Poem copied to clipboard';
	}

	async function handleSaveClick() {
		if (!$authStore.user) {
			// Save to localStorage before showing login prompt
			localStorage.setItem('pendingPoem', generatedPoem);
			localStorage.setItem('pendingPoemTitle', generatedTitle);
			localStorage.setItem('pendingPrompt', prompt);
			showSavePrompt = true;
			return;
		}

		// Auto-save with AI-generated title
		saving = true;
		error = '';

		try {
			const user = $authStore.user;
			const shareId = crypto.randomUUID();
			const title = generatedTitle || prompt.slice(0, 50) || 'Untitled';

			const docRef = await addDoc(collection(db, 'poems'), {
				authorId: user.uid,
				authorUsername: user.displayName || user.email.split('@')[0],
				title: title,
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

			// Redirect to poem view page
			goto(`/poems/${docRef.id}`);
		} catch (err) {
			console.error('Error saving poem:', err);
			showModal = true;
			modalType = 'error';
			modalTitle = 'Error';
			modalMessage = 'Failed to save poem. Please try again.';
		} finally {
			saving = false;
		}
	}
</script>

<svelte:head>
	<title>Create Poem - Victorian Poems</title>
</svelte:head>

<Modal
	bind:show={showModal}
	title={modalTitle}
	type={modalType}
	onConfirm={modalOnConfirm}
>
	<p>{modalMessage}</p>
</Modal>

<LoadingOverlay show={loading} message="Crafting your poem..." />

<div class="container mx-auto px-4 py-8">
	<!-- Header -->
	<div class="mb-8 text-center">
		<div class="flex items-center justify-center gap-3 mb-2">
			<Feather size={36} class="text-gold-600" />
			<h1 class="text-4xl font-bold">Create a Poem</h1>
		</div>
		<p class="text-sepia-700">Generate beautiful poems with AI - Free, no login required</p>
	</div>

	<div class="max-w-2xl mx-auto">
		{#if view === 'form'}
			<!-- Generator Form -->
			<div class="card-victorian">
				<div class="flex items-center gap-2 mb-4">
					<Sparkles size={24} class="text-gold-600" />
					<h2 class="text-2xl font-bold">AI Poem Generator</h2>
				</div>

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

					<!-- Advanced Options Section -->
					<div class="mb-4">
						<button
							type="button"
							on:click={() => showAdvanced = !showAdvanced}
							class="flex items-center gap-2 text-gold-700 hover:text-gold-800 transition-colors font-semibold"
						>
							{#if showAdvanced}
								<ChevronUp size={20} />
							{:else}
								<ChevronDown size={20} />
							{/if}
							<span>Advanced Options</span>
							<span class="text-sm text-sepia-600 font-normal">(Style, Tone, Length, Rhyme)</span>
						</button>

						{#if showAdvanced}
							<div class="mt-4 space-y-4 p-4 bg-parchment-100 border-2 border-sepia-300 rounded-sm">
								<div class="grid md:grid-cols-2 gap-4">
									<!-- Style Select -->
									<div>
										<label for="style" class="block text-sm font-semibold mb-2 text-ink-800">
											Poem Style
										</label>
										<select
											id="style"
											bind:value={style}
											disabled={loading}
											class="input-victorian text-sm w-full"
										>
											{#each styleOptions as option}
												<option value={option.value}>{option.label}</option>
											{/each}
										</select>
										<p class="text-xs text-sepia-600 mt-1">Choose the poetic form or theme</p>
									</div>

									<!-- Tone Select -->
									<div>
										<label for="tone" class="block text-sm font-semibold mb-2 text-ink-800">
											Tone
										</label>
										<select
											id="tone"
											bind:value={tone}
											disabled={loading}
											class="input-victorian text-sm w-full"
										>
											{#each toneOptions as option}
												<option value={option.value}>{option.label}</option>
											{/each}
										</select>
										<p class="text-xs text-sepia-600 mt-1">Set the emotional atmosphere</p>
									</div>
								</div>

								<div class="grid md:grid-cols-2 gap-4">
									<!-- Length Select -->
									<div>
										<label for="length" class="block text-sm font-semibold mb-2 text-ink-800">
											Length
										</label>
										<select
											id="length"
											bind:value={length}
											disabled={loading}
											class="input-victorian text-sm w-full"
										>
											{#each lengthOptions as option}
												<option value={option.value}>{option.label}</option>
											{/each}
										</select>
										<p class="text-xs text-sepia-600 mt-1">Approximate poem length</p>
									</div>

									<!-- Rhyme Scheme Select -->
									<div>
										<label for="rhymeScheme" class="block text-sm font-semibold mb-2 text-ink-800">
											Rhyme Scheme
										</label>
										<select
											id="rhymeScheme"
											bind:value={rhymeScheme}
											disabled={loading}
											class="input-victorian text-sm w-full"
										>
											{#each rhymeSchemeOptions as option}
												<option value={option.value}>{option.label}</option>
											{/each}
										</select>
										<p class="text-xs text-sepia-600 mt-1">Rhyme pattern (if applicable)</p>
									</div>
								</div>

								<div class="text-xs text-sepia-700 italic border-t border-sepia-300 pt-3 mt-2">
									All options are optional. Leave as "Default" to let the AI choose based on your prompt.
								</div>
							</div>
						{/if}
					</div>

					<button
						type="submit"
						class="btn-victorian w-full flex items-center justify-center gap-2"
						disabled={loading}
					>
						<Sparkles size={18} />
						<span>Generate Poem</span>
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
		{:else if view === 'result'}
			<!-- Generated Poem Display -->
			<div class="card-victorian fade-in">
				<div class="flex items-center gap-2 mb-4">
					<Feather size={24} class="text-gold-600" />
					<h2 class="text-2xl font-bold">Your Poem</h2>
				</div>

				{#if generatedTitle}
					<h3 class="text-2xl font-cormorant font-bold mb-4 text-center text-gold-700">
						{generatedTitle}
					</h3>
				{/if}

				<div class="bg-parchment-50 p-6 border-2 border-sepia-400 rounded-sm mb-4">
					<div class="font-cormorant text-lg leading-relaxed whitespace-pre-wrap">
						{generatedPoem}
					</div>
				</div>

				<!-- Action buttons -->
				<div class="flex gap-2 mb-4">
					<button
						on:click={copyToClipboard}
						class="btn-victorian-secondary flex-1 flex items-center justify-center gap-2"
					>
						<Copy size={18} />
						<span>Copy</span>
					</button>
					<button
						on:click={handleSaveClick}
						class="btn-victorian flex-1 flex items-center justify-center gap-2"
						disabled={saving}
					>
						{#if saving}
							<Loader2 size={18} class="animate-spin" />
							<span>Saving...</span>
						{:else}
							<Save size={18} />
							<span>Save Poem</span>
						{/if}
					</button>
				</div>

				<!-- Create Another button -->
				<button
					on:click={createAnother}
					class="btn-victorian-secondary w-full flex items-center justify-center gap-2"
				>
					<Sparkles size={18} />
					<span>Create Another Poem</span>
				</button>

				{#if showSavePrompt}
					<div class="mt-4 p-4 bg-gold-100 border-2 border-gold-500 rounded text-center">
						<p class="text-ink-900 mb-3">
							<strong>Sign in to save this poem!</strong><br/>
							<span class="text-sm">Your poem will be waiting for you after login</span>
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
			</div>
		{/if}
	</div>

	<!-- Tips Section -->
	<div class="mt-8 max-w-2xl mx-auto card-victorian">
		<div class="flex items-center gap-2 mb-3">
			<Sparkles size={20} class="text-gold-600" />
			<h3 class="text-xl font-bold">Tips for Better Poems</h3>
		</div>
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
