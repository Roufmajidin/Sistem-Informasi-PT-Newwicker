@php
    $raw = trim((string) ($value ?? ''));
    // Normalize bullets only when they are list markers, not hyphens within words.
    $normalized = preg_replace('/[•●]\s*/u', "\n", $raw);
    $normalized = preg_replace('/(^|\n)\s*[-*]\s+/u', '$1', $normalized);
    $points = collect(preg_split('/\r\n|\r|\n/u', $normalized))
        ->map(fn($point) => trim($point))
        ->filter(fn($point) => $point !== '')
        ->values();
    $count = $points->count();
@endphp
<div class="sm-points" data-sm-points>
    @forelse ($points as $index => $point)
        <div class="sm-point {{ $index >= 2 ? 'is-hidden' : '' }}">
            <span class="sm-point-bullet">•</span>
            <span class="sm-point-text">{{ $point }}</span>
        </div>
    @empty
        <span class="text-muted">—</span>
    @endforelse
    @if ($count > 2)
        <button type="button" class="sm-points-toggle" data-sm-points-toggle data-more="+ More ({{ $count - 2 }})"
            data-less="Show less" aria-expanded="false">+ More ({{ $count - 2 }})</button>
    @endif
</div>
