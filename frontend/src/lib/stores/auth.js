import { writable } from 'svelte/store';
import { auth } from '$lib/services/firebase';
import { onAuthStateChanged } from 'firebase/auth';

function createAuthStore() {
	const { subscribe, set, update } = writable({
		user: null,
		loading: true,
		error: null
	});

	// Listen to auth state changes
	onAuthStateChanged(auth, (user) => {
		set({
			user: user,
			loading: false,
			error: null
		});
	});

	return {
		subscribe,
		setUser: (user) => update(state => ({ ...state, user, loading: false })),
		setError: (error) => update(state => ({ ...state, error })),
		clearError: () => update(state => ({ ...state, error: null })),
		reset: () => set({ user: null, loading: false, error: null })
	};
}

export const authStore = createAuthStore();
