<div @class([
    'max-w-xs sm:max-w-sm rounded-xl overflow-hidden border',
    'border-white/20' => $belongsToAuth ?? false,
    'border-[var(--wc-light-secondary)] dark:border-[var(--wc-dark-secondary)]' => ! ($belongsToAuth ?? false),
])>
    <audio
        controls
        preload="metadata"
        class="w-full min-w-[220px] max-w-full"
        src="{{ $attachment?->url }}"
    >
        {{ $attachment->original_name }}
    </audio>
</div>
