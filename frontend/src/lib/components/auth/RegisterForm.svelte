<script>
	import { auth, db } from '$lib/services/firebase';
	import { createUserWithEmailAndPassword, updateProfile, signInWithPopup, GoogleAuthProvider } from 'firebase/auth';
	import { doc, setDoc } from 'firebase/firestore';
	import { goto } from '$app/navigation';

	let email = '';
	let password = '';
	let confirmPassword = '';
	let displayName = '';
	let error = '';
	let loading = false;

	async function createUserDocument(user) {
		await setDoc(doc(db, 'users', user.uid), {
			uid: user.uid,
			email: user.email,
			displayName: user.displayName || displayName,
			username: user.email.split('@')[0],
			createdAt: new Date(),
			updatedAt: new Date()
		});
	}

	async function handleEmailRegister() {
		if (!email || !password || !displayName) {
			error = 'Please fill in all fields';
			return;
		}

		if (password.length < 6) {
			error = 'Password must be at least 6 characters';
			return;
		}

		if (password !== confirmPassword) {
			error = 'Passwords do not match';
			return;
		}

		loading = true;
		error = '';

		try {
			const result = await createUserWithEmailAndPassword(auth, email, password);
			await updateProfile(result.user, { displayName });
			await createUserDocument(result.user);
			goto('/create');
		} catch (err) {
			if (err.code === 'auth/email-already-in-use') {
				error = 'Email already in use';
			} else if (err.code === 'auth/invalid-email') {
				error = 'Invalid email address';
			} else if (err.code === 'auth/weak-password') {
				error = 'Password is too weak';
			} else {
				error = err.message;
			}
		} finally {
			loading = false;
		}
	}

	async function handleGoogleRegister() {
		loading = true;
		error = '';

		try {
			const provider = new GoogleAuthProvider();
			const result = await signInWithPopup(auth, provider);
			await createUserDocument(result.user);
			goto('/create');
		} catch (err) {
			if (err.code !== 'auth/popup-closed-by-user') {
				error = err.message;
			}
		} finally {
			loading = false;
		}
	}
</script>

<div class="card-victorian max-w-md mx-auto">
	<h2 class="text-3xl font-bold text-center mb-6">Create Account</h2>

	{#if error}
		<div class="bg-burgundy-500 text-parchment-50 p-3 rounded mb-4 text-center">
			{error}
		</div>
	{/if}

	<form on:submit|preventDefault={handleEmailRegister} class="space-y-4">
		<div>
			<label for="displayName" class="block text-sm font-semibold mb-2">Display Name</label>
			<input
				type="text"
				id="displayName"
				bind:value={displayName}
				class="input-victorian"
				placeholder="Your Name"
				required
				disabled={loading}
			/>
		</div>

		<div>
			<label for="email" class="block text-sm font-semibold mb-2">Email Address</label>
			<input
				type="email"
				id="email"
				bind:value={email}
				class="input-victorian"
				placeholder="your@email.com"
				required
				disabled={loading}
			/>
		</div>

		<div>
			<label for="password" class="block text-sm font-semibold mb-2">Password</label>
			<input
				type="password"
				id="password"
				bind:value={password}
				class="input-victorian"
				placeholder="••••••••"
				required
				disabled={loading}
			/>
		</div>

		<div>
			<label for="confirmPassword" class="block text-sm font-semibold mb-2">Confirm Password</label>
			<input
				type="password"
				id="confirmPassword"
				bind:value={confirmPassword}
				class="input-victorian"
				placeholder="••••••••"
				required
				disabled={loading}
			/>
		</div>

		<button type="submit" class="btn-victorian w-full" disabled={loading}>
			{loading ? 'Creating account...' : 'Create Account'}
		</button>
	</form>

	<div class="divider-flourish my-6">
		<span>or</span>
	</div>

	<button on:click={handleGoogleRegister} class="btn-victorian-secondary w-full" disabled={loading}>
		<span class="flex items-center justify-center gap-2">
			<svg class="w-5 h-5" viewBox="0 0 24 24">
				<path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
				<path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
				<path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
				<path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
			</svg>
			Sign up with Google
		</span>
	</button>

	<p class="text-center mt-6 text-sm text-ink-700">
		Already have an account?
		<a href="/auth/login" class="text-gold-600 hover:text-gold-700 font-semibold">Sign in here</a>
	</p>
</div>
