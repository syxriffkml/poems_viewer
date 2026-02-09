<script>
	import { authStore } from '$lib/stores/auth';
	import { auth } from '$lib/services/firebase';
	import { signOut } from 'firebase/auth';
	import { goto } from '$app/navigation';
	import { Sparkles, BookMarked, Image, Feather, Heart } from 'lucide-svelte';

	async function handleLogout() {
		await signOut(auth);
		goto('/');
	}
</script>

<header class="border-b-2 border-sepia-400 bg-parchment-50 sticky top-0 z-50">
	<div class="container mx-auto px-4 py-4 flex justify-between items-center">
		<!-- Logo -->
		<a href="/" class="flex items-center gap-2">
			<Feather size={32} class="text-gold-600" />
			<span class="text-2xl font-playfair font-bold text-ink-900">Victorian Poems</span>
		</a>

		<!-- Navigation Links -->
		<nav class="hidden md:flex gap-6 items-center">
			<a href="/create" class="text-ink-800 hover:text-gold-600 font-semibold transition-colors flex items-center gap-2">
				<Sparkles size={18} />
				<span>Create</span>
			</a>
			{#if $authStore.user}
				<a href="/poems" class="text-ink-800 hover:text-gold-600 font-semibold transition-colors flex items-center gap-2">
					<BookMarked size={18} />
					<span>My Poems</span>
				</a>
				<a href="/favorites" class="text-ink-800 hover:text-gold-600 font-semibold transition-colors flex items-center gap-2">
					<Heart size={18} />
					<span>Favorites</span>
				</a>
			{/if}
			<a href="/gallery" class="text-ink-800 hover:text-gold-600 font-semibold transition-colors flex items-center gap-2">
				<Image size={18} />
				<span>Gallery</span>
			</a>
		</nav>

		<!-- Auth Section -->
		<div>
			{#if $authStore.loading}
				<div class="text-sm text-sepia-600">Loading...</div>
			{:else if $authStore.user}
				<!-- User Menu -->
				<div class="flex items-center gap-3">
					<span class="text-sm text-ink-700 hidden sm:inline">
						{#if $authStore.user.displayName}
							{$authStore.user.displayName}
						{:else}
							<span class="inline-block w-20 h-4 bg-parchment-300 rounded animate-pulse"></span>
						{/if}
					</span>
					<button on:click={handleLogout} class="btn-victorian-secondary py-2 px-4 text-sm">
						Sign Out
					</button>
				</div>
			{:else}
				<!-- Login/Register Buttons -->
				<div class="flex gap-2">
					<a href="/auth/login" class="btn-victorian-secondary py-2 px-4 text-sm">
						Sign In
					</a>
					<a href="/auth/register" class="btn-victorian py-2 px-4 text-sm">
						Register
					</a>
				</div>
			{/if}
		</div>
	</div>
</header>
