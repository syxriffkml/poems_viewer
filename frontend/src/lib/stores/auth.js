import { writable } from 'svelte/store';
import { auth } from '$lib/services/firebase';
import { onAuthStateChanged } from 'firebase/auth';

/**
 * @typedef {import('firebase/auth').User} FirebaseUser
 */

/**
 * @typedef {Object} AuthState
 * @property {FirebaseUser | null} user
 * @property {boolean} loading
 * @property {string | null} error
 */

function createAuthStore() {
	/** @type {import('svelte/store').Writable<AuthState>} */
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
		/**
		 * @param {FirebaseUser | null} user
		 */
		setUser: (user) => update(state => ({ ...state, user, loading: false })),
		/**
		 * @param {string} error
		 */
		setError: (error) => update(state => ({ ...state, error })),
		clearError: () => update(state => ({ ...state, error: null })),
		reset: () => set({ user: null, loading: false, error: null })
	};
}

export const authStore = createAuthStore();
