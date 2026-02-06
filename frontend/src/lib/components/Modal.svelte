<script>
	export let show = false;
	export let title = '';
	export let type = 'info'; // 'info', 'success', 'error', 'confirm'
	export let confirmText = 'OK';
	export let cancelText = 'Cancel';
	export let onConfirm = null;
	export let onCancel = null;

	function handleConfirm() {
		if (onConfirm) onConfirm();
		show = false;
	}

	function handleCancel() {
		if (onCancel) onCancel();
		show = false;
	}

	function handleBackdropClick(e) {
		if (e.target === e.currentTarget) {
			handleCancel();
		}
	}
</script>

{#if show}
	<div
		class="fixed inset-0 z-50 flex items-center justify-center bg-ink-900 bg-opacity-50 p-4"
		on:click={handleBackdropClick}
		role="dialog"
		aria-modal="true"
	>
		<div class="card-victorian max-w-md w-full">
			<!-- Header -->
			{#if title}
				<div class="flex items-center justify-between mb-4">
					<h3 class="text-2xl font-bold">
						{#if type === 'success'}
							✓
						{:else if type === 'error'}
							⚠
						{:else if type === 'confirm'}
							❓
						{:else}
							ℹ
						{/if}
						{title}
					</h3>
					<button
						on:click={handleCancel}
						class="text-sepia-600 hover:text-ink-900 text-2xl leading-none"
						aria-label="Close"
					>
						×
					</button>
				</div>
			{/if}

			<!-- Content -->
			<div class="mb-6">
				<slot />
			</div>

			<!-- Actions -->
			<div class="flex gap-3 justify-end">
				{#if type === 'confirm'}
					<button on:click={handleCancel} class="btn-victorian-secondary">
						{cancelText}
					</button>
					<button on:click={handleConfirm} class="btn-victorian">
						{confirmText}
					</button>
				{:else}
					<button on:click={handleConfirm} class="btn-victorian w-full">
						{confirmText}
					</button>
				{/if}
			</div>
		</div>
	</div>
{/if}
