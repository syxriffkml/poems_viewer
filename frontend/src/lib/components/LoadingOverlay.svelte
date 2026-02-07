<script>
	import { onDestroy } from 'svelte';
	import { fade } from 'svelte/transition';

	export let show = false;
	export let message = 'Loading...';

	function initPaths(node) {
		const paths = node.querySelectorAll('.feather-path');
		paths.forEach((path) => {
			const length = path.getTotalLength();
			path.style.strokeDasharray = length;
			path.style.strokeDashoffset = length;
		});
	}

	$: if (typeof document !== 'undefined') {
		document.body.style.overflow = show ? 'hidden' : '';
	}

	onDestroy(() => {
		if (typeof document !== 'undefined') {
			document.body.style.overflow = '';
		}
	});
</script>

{#if show}
	<div class="loading-overlay" transition:fade={{ duration: 300 }}>
		<div class="loading-content">
			<svg
				use:initPaths
				xmlns="http://www.w3.org/2000/svg"
				width="80"
				height="80"
				viewBox="0 0 24 24"
				fill="none"
				stroke="currentColor"
				stroke-width="2"
				stroke-linecap="round"
				stroke-linejoin="round"
				class="feather-svg"
			>
				<path
					class="feather-path feather-path-1"
					d="M12.67 19a2 2 0 0 0 1.416-.588l6.154-6.172a6 6 0 0 0-8.49-8.49L5.586 9.914A2 2 0 0 0 5 11.328V18a1 1 0 0 0 1 1z"
				/>
				<path
					class="feather-path feather-path-2"
					d="M16 8 2 22"
				/>
				<path
					class="feather-path feather-path-3"
					d="M17.5 15H9"
				/>
			</svg>
			<p class="loading-text">{message}</p>
		</div>
	</div>
{/if}

<style>
	.loading-overlay {
		position: fixed;
		inset: 0;
		z-index: 60;
		display: flex;
		align-items: center;
		justify-content: center;
		background-color: rgba(249, 245, 237, 0.95);
	}

	.loading-content {
		text-align: center;
	}

	.feather-svg {
		color: #b8891c;
		margin: 0 auto 1.5rem;
		filter: drop-shadow(0 2px 4px rgba(130, 97, 66, 0.3));
		animation: featherGlow 2s ease-in-out 1.8s infinite;
	}

	.feather-path {
		fill: none;
	}

	.feather-path-1 {
		animation: drawPath1 1.2s ease-in-out forwards;
	}

	.feather-path-2 {
		animation: drawPath2 0.8s ease-in-out 0.6s forwards;
	}

	.feather-path-3 {
		animation: drawPath3 0.5s ease-in-out 1.0s forwards;
	}

	@keyframes drawPath1 {
		to {
			stroke-dashoffset: 0;
		}
	}

	@keyframes drawPath2 {
		to {
			stroke-dashoffset: 0;
		}
	}

	@keyframes drawPath3 {
		to {
			stroke-dashoffset: 0;
		}
	}

	@keyframes featherGlow {
		0%, 100% {
			opacity: 1;
			filter: drop-shadow(0 2px 4px rgba(130, 97, 66, 0.3));
		}
		50% {
			opacity: 0.7;
			filter: drop-shadow(0 2px 8px rgba(184, 137, 28, 0.5));
		}
	}

	.loading-text {
		font-family: 'Cormorant Garamond', serif;
		font-size: 1.25rem;
		color: #b8891c;
		letter-spacing: 0.05em;
		animation: textPulse 2s ease-in-out 1.8s infinite;
	}

	@keyframes textPulse {
		0%, 100% {
			opacity: 1;
		}
		50% {
			opacity: 0.6;
		}
	}
</style>
