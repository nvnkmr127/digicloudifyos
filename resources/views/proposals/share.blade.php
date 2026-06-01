<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $proposal->title }} · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-brand-light text-text-primary">
    <div class="max-w-3xl mx-auto p-6">
        <div class="mb-6">
            <div class="text-xs font-semibold text-text-muted">Proposal</div>
            <div class="text-2xl font-semibold mt-1">{{ $proposal->title }}</div>
            <div class="text-sm text-text-muted mt-1">{{ $proposal->proposal_number }} · {{ $proposal->client?->name ?? '—' }}</div>
        </div>

        <div class="bg-white rounded-card border border-gray-100 p-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div>
                    <div class="text-xs font-semibold text-text-muted">Status</div>
                    <div class="text-sm font-semibold mt-1">{{ $proposal->status }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold text-text-muted">Valid Until</div>
                    <div class="text-sm font-semibold mt-1">{{ $proposal->valid_until ? $proposal->valid_until->format('M d, Y') : '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold text-text-muted">Total</div>
                    <div class="text-sm font-semibold mt-1">${{ number_format((float) $proposal->total_amount, 2) }}</div>
                </div>
            </div>

            @if($proposal->description)
                <div class="mt-6 pt-6 border-t border-gray-100">
                    <div class="text-xs font-semibold text-text-muted">Description</div>
                    <div class="text-sm mt-2 whitespace-pre-line">{{ $proposal->description }}</div>
                </div>
            @endif
        </div>

        <div class="mt-6 text-xs text-text-muted">
            Shared via a signed link from {{ config('app.name') }}.
        </div>
    </div>
</body>
</html>

