<script>
	import { onMount } from 'svelte';
	import { page } from '$app/stores';
	import { authStore } from '$lib/stores/auth';
	import { goto } from '$app/navigation';
	import { db } from '$lib/services/firebase';
	import { doc, getDoc, updateDoc, serverTimestamp } from 'firebase/firestore';
	import Modal from '$lib/components/Modal.svelte';
	import LoadingOverlay from '$lib/components/LoadingOverlay.svelte';
	import { Save, X, Eye, Lock, Globe, Feather, Type, AlignLeft, AlignCenter, AlignRight, Palette } from 'lucide-svelte';

	let poem = null;
	let loading = true;
	let saving = false;
	let error = '';

	// Editable fields
	let title = '';
	let content = '';
	let isPublic = false;
	let categories = [];
	let tags = [];
	let sentiment = '';

	// Formatting options
	let fontFamily = 'Crimson Text';
	let fontSize = 16;
	let color = '#1f1e1a';
	let alignment = 'left';
	let lineSpacing = 1.6;

	// Modal state
	let showModal = false;
	let modalTitle = '';
	let modalMessage = '';
	let modalType = 'info';

	const fontOptions = [
		'Crimson Text',
		'Playfair Display',
		'Cormorant Garamond',
		'Old Standard TT',
		'Georgia',
		'Times New Roman'
	];

	const colorOptions = [
		{ name: 'Ink Black', value: '#1f1e1a' },
		{ name: 'Sepia Brown', value: '#4a3f35' },
		{ name: 'Deep Gold', value: '#8b6914' },
		{ name: 'Burgundy', value: '#722f37' },
		{ name: 'Forest Green', value: '#2d4a2c' }
	];

	onMount(() => {
		loadPoem();
	});

	async function loadPoem() {
		loading = true;
		error = '';

		try {
			const user = $authStore.user;
			if (!user) {
				goto('/auth/login');
				return;
			}

			const poemId = $page.params.id;
			const docRef = doc(db, 'poems', poemId);
			const docSnap = await getDoc(docRef);

			if (!docSnap.exists()) {
				error = 'Poem not found';
				return;
			}

			poem = { id: docSnap.id, ...docSnap.data() };

			// Check permissions - only author can edit
			if (poem.authorId !== user.uid) {
				error = 'You do not have permission to edit this poem';
				poem = null;
				return;
			}

			// Load values into form
			title = poem.title || '';
			content = poem.content || '';
			isPublic = poem.isPublic || false;
			categories = poem.categories || [];
			tags = poem.tags || [];
			sentiment = poem.sentiment || '';

			// Load formatting
			fontFamily = poem.formatting?.fontFamily || 'Crimson Text';
			fontSize = poem.formatting?.fontSize || 16;
			color = poem.formatting?.color || '#1f1e1a';
			alignment = poem.formatting?.alignment || 'left';
			lineSpacing = poem.formatting?.lineSpacing || 1.6;

		} catch (err) {
			console.error('Error loading poem:', err);
			error = 'Failed to load poem. Please try again.';
		} finally {
			loading = false;
		}
	}

	async function handleSave() {
		if (!title.trim() || !content.trim()) {
			showModal = true;
			modalType = 'error';
			modalTitle = 'Validation Error';
			modalMessage = 'Title and content are required';
			return;
		}

		saving = true;

		try {
			const poemId = $page.params.id;
			const docRef = doc(db, 'poems', poemId);

			await updateDoc(docRef, {
				title: title.trim(),
				content: content.trim(),
				isPublic,
				categories,
				tags,
				sentiment,
				formatting: {
					fontFamily,
					fontSize,
					color,
					alignment,
					lineSpacing
				},
				updatedAt: serverTimestamp()
			});

			showModal = true;
			modalType = 'success';
			modalTitle = 'Saved!';
			modalMessage = 'Your poem has been updated successfully';

			// Redirect to view page after a short delay
			setTimeout(() => {
				goto(`/poems/${poemId}`);
			}, 1500);

		} catch (err) {
			console.error('Error saving poem:', err);
			showModal = true;
			modalType = 'error';
			modalTitle = 'Save Failed';
			modalMessage = 'Failed to save changes. Please try again.';
		} finally {
			saving = false;
		}
	}

	function handleCancel() {
		goto(`/poems/${$page.params.id}`);
	}

</script>

<svelte:head>
	<title>Edit {poem?.title || 'Poem'} - Victorian Poems</title>
</svelte:head>

<Modal bind:show={showModal} title={modalTitle} type={modalType}>
	<p>{modalMessage}</p>
</Modal>

<LoadingOverlay show={loading || saving} message={saving ? 'Saving changes...' : 'Loading poem...'} />

<div class="container mx-auto px-4 py-8 max-w-5xl">
	{#if loading}
		<!-- Loading handled by overlay -->
	{:else if error}
		<div class="card-victorian text-center py-16">
			<X size={64} class="text-burgundy-500 mx-auto mb-4" />
			<h2 class="text-2xl font-bold mb-3">{error}</h2>
			<div class="flex gap-3 justify-center mt-6">
				<a href="/poems" class="btn-victorian">
					Back to My Poems
				</a>
			</div>
		</div>
	{:else if poem}
		<!-- Page Header -->
		<div class="mb-8">
			<div class="flex items-center gap-3 mb-2">
				<Feather size={36} class="text-gold-600" />
				<h1 class="text-4xl font-bold">Edit Poem</h1>
			</div>
			<p class="text-sepia-700">Make changes to your poem</p>
		</div>

		<div class="grid lg:grid-cols-3 gap-6">
			<!-- Main Content (Left Column) -->
			<div class="lg:col-span-2 space-y-6">
				<!-- Basic Info Card -->
				<div class="card-victorian">
					<h2 class="text-2xl font-bold mb-4 flex items-center gap-2">
						<Type size={24} class="text-gold-600" />
						<span>Content</span>
					</h2>

					<div class="space-y-4">
						<!-- Title -->
						<div>
							<label for="title" class="block text-sm font-semibold mb-2">
								Title
							</label>
							<input
								id="title"
								type="text"
								bind:value={title}
								class="input-victorian"
								placeholder="Enter poem title"
								maxlength="200"
							/>
						</div>

						<!-- Content -->
						<div>
							<label for="content" class="block text-sm font-semibold mb-2">
								Poem Content
							</label>
							<textarea
								id="content"
								bind:value={content}
								class="input-victorian min-h-[400px] font-cormorant text-lg"
								placeholder="Enter your poem..."
								style="font-family: {fontFamily}; line-height: {lineSpacing};"
							></textarea>
							<p class="text-xs text-sepia-600 mt-1">
								{content.length} characters
							</p>
						</div>

						<!-- Public/Private Toggle -->
						<div class="flex items-center gap-3 p-4 bg-parchment-100 border border-sepia-300 rounded">
							<button
								type="button"
								on:click={() => isPublic = !isPublic}
								class="flex items-center gap-2 text-sm font-semibold"
							>
								{#if isPublic}
									<Globe size={20} class="text-green-600" />
									<span class="text-green-700">Public</span>
								{:else}
									<Lock size={20} class="text-sepia-600" />
									<span class="text-sepia-700">Private</span>
								{/if}
							</button>
							<span class="text-sm text-sepia-600">
								{isPublic ? 'Visible in gallery' : 'Only visible to you'}
							</span>
						</div>
					</div>
				</div>

				<!-- Preview Card -->
				<div class="card-victorian">
					<h2 class="text-2xl font-bold mb-4 flex items-center gap-2">
						<Eye size={24} class="text-gold-600" />
						<span>Preview</span>
					</h2>
					<div class="bg-parchment-50 p-6 border-2 border-sepia-400 rounded">
						<h3 class="text-2xl font-bold mb-4" style="color: {color}">
							{title || 'Untitled'}
						</h3>
						<div
							class="whitespace-pre-wrap"
							style="font-family: {fontFamily};
							       font-size: {fontSize}px;
							       color: {color};
							       text-align: {alignment};
							       line-height: {lineSpacing};"
						>
							{content || 'Your poem will appear here...'}
						</div>
					</div>
				</div>
			</div>

			<!-- Sidebar (Right Column) -->
			<div class="space-y-6">
				<!-- Formatting Card -->
				<div class="card-victorian">
					<h2 class="text-xl font-bold mb-4 flex items-center gap-2">
						<Palette size={20} class="text-gold-600" />
						<span>Formatting</span>
					</h2>

					<div class="space-y-4">
						<!-- Font Family -->
						<div>
							<label for="font" class="block text-sm font-semibold mb-2">
								Font
							</label>
							<select
								id="font"
								bind:value={fontFamily}
								class="input-victorian"
							>
								{#each fontOptions as font}
									<option value={font}>{font}</option>
								{/each}
							</select>
						</div>

						<!-- Font Size -->
						<div>
							<label for="fontSize" class="block text-sm font-semibold mb-2">
								Font Size: {fontSize}px
							</label>
							<input
								id="fontSize"
								type="range"
								bind:value={fontSize}
								min="12"
								max="24"
								class="w-full"
							/>
						</div>

						<!-- Color -->
						<div>
							<label class="block text-sm font-semibold mb-2">
								Text Color
							</label>
							<div class="grid grid-cols-2 gap-2">
								{#each colorOptions as colorOption}
									<button
										type="button"
										on:click={() => color = colorOption.value}
										class="p-2 border-2 rounded text-xs transition-all"
										class:border-gold-600={color === colorOption.value}
										class:border-sepia-300={color !== colorOption.value}
									>
										<div
											class="w-full h-6 rounded mb-1"
											style="background-color: {colorOption.value}"
										></div>
										{colorOption.name}
									</button>
								{/each}
							</div>
						</div>

						<!-- Alignment -->
						<div>
							<label class="block text-sm font-semibold mb-2">
								Alignment
							</label>
							<div class="flex gap-2">
								<button
									type="button"
									on:click={() => alignment = 'left'}
									class="flex-1 p-2 border-2 rounded transition-all"
									class:border-gold-600={alignment === 'left'}
									class:bg-gold-50={alignment === 'left'}
									class:border-sepia-300={alignment !== 'left'}
								>
									<AlignLeft size={20} class="mx-auto" />
								</button>
								<button
									type="button"
									on:click={() => alignment = 'center'}
									class="flex-1 p-2 border-2 rounded transition-all"
									class:border-gold-600={alignment === 'center'}
									class:bg-gold-50={alignment === 'center'}
									class:border-sepia-300={alignment !== 'center'}
								>
									<AlignCenter size={20} class="mx-auto" />
								</button>
								<button
									type="button"
									on:click={() => alignment = 'right'}
									class="flex-1 p-2 border-2 rounded transition-all"
									class:border-gold-600={alignment === 'right'}
									class:bg-gold-50={alignment === 'right'}
									class:border-sepia-300={alignment !== 'right'}
								>
									<AlignRight size={20} class="mx-auto" />
								</button>
							</div>
						</div>

						<!-- Line Spacing -->
						<div>
							<label for="lineSpacing" class="block text-sm font-semibold mb-2">
								Line Spacing: {lineSpacing.toFixed(1)}
							</label>
							<input
								id="lineSpacing"
								type="range"
								bind:value={lineSpacing}
								min="1.0"
								max="2.5"
								step="0.1"
								class="w-full"
							/>
						</div>
					</div>
				</div>

				<!-- Action Buttons -->
				<div class="card-victorian">
					<div class="space-y-2">
						<button
							on:click={handleSave}
							disabled={saving}
							class="btn-victorian w-full flex items-center justify-center gap-2"
						>
							<Save size={18} />
							<span>{saving ? 'Saving...' : 'Save Changes'}</span>
						</button>
						<button
							on:click={handleCancel}
							disabled={saving}
							class="btn-victorian-secondary w-full flex items-center justify-center gap-2"
						>
							<X size={18} />
							<span>Cancel</span>
						</button>
					</div>
				</div>
			</div>
		</div>
	{/if}
</div>
