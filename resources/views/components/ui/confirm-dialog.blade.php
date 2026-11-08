@props(['id' => 'confirm-dialog'])

<div
    id="{{ $id }}"
    data-confirm-dialog
    class="fixed inset-0 z-[70] hidden"
    role="dialog"
    aria-modal="true"
    aria-labelledby="{{ $id }}-title"
    aria-describedby="{{ $id }}-message"
>
    <div class="absolute inset-0 bg-ink/40 backdrop-blur-sm" data-confirm-cancel></div>

    <div
        data-confirm-panel
        tabindex="-1"
        class="absolute inset-x-0 bottom-0 rounded-t-2xl bg-surface p-5 shadow-float outline-none sm:inset-x-auto sm:bottom-auto sm:left-1/2 sm:top-1/2 sm:w-full sm:max-w-sm sm:-translate-x-1/2 sm:-translate-y-1/2 sm:rounded-2xl sm:p-6"
    >
        <div class="mx-auto mb-4 h-1 w-10 rounded-full bg-line sm:hidden"></div>

        <div class="flex items-start gap-3">
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-danger-soft text-danger" aria-hidden="true">
                <i class="bi bi-trash3"></i>
            </span>

            <div class="min-w-0">
                <h2 id="{{ $id }}-title" class="text-lg font-semibold">Delete transaction?</h2>
                <p id="{{ $id }}-message" data-confirm-message class="mt-1 text-sm text-muted">
                    This transaction will be deleted. You can undo it for a few seconds afterwards.
                </p>
            </div>
        </div>

        <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <button type="button" class="btn btn-secondary" data-confirm-cancel>Cancel</button>
            <button type="button" class="btn btn-danger" data-confirm-accept>Delete</button>
        </div>
    </div>
</div>
