<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $form->name }} · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-brand-light text-text-primary">
    <div class="max-w-2xl mx-auto p-6">
        <div class="mb-6">
            <div class="text-2xl font-semibold">{{ $form->name }}</div>
            @if($form->description)
                <div class="text-sm text-text-muted mt-2">{{ $form->description }}</div>
            @endif
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-card border border-success bg-success-soft px-4 py-3 text-sm text-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-card border border-gray-100 p-6">
            <form method="POST" action="{{ route('public.forms.submit', ['slug' => $form->slug]) }}" class="space-y-5">
                @csrf
                <input type="hidden" name="k" value="{{ $key }}">

                @foreach(($form->fields ?? []) as $field)
                    @php
                        $name = (string) ($field['name'] ?? '');
                        $label = (string) ($field['label'] ?? $name);
                        $type = (string) ($field['type'] ?? 'text');
                        $placeholder = (string) ($field['placeholder'] ?? '');
                        $required = (bool) ($field['required'] ?? false);
                    @endphp

                    @if($name !== '')
                        <div>
                            <label class="block text-xs font-semibold text-text-muted" for="{{ $name }}">
                                {{ $label }}@if($required)<span class="text-danger">*</span>@endif
                            </label>
                            <input
                                id="{{ $name }}"
                                name="{{ $name }}"
                                type="{{ $type === 'email' ? 'email' : 'text' }}"
                                value="{{ old($name) }}"
                                @if($required) required @endif
                                class="mt-2 w-full rounded-input border border-gray-200 bg-white px-3 py-2 text-sm text-text-primary placeholder:text-text-muted focus:border-primary focus:ring-primary"
                                placeholder="{{ $placeholder }}"
                            />
                            @error($name)
                                <div class="mt-2 text-xs text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif
                @endforeach

                <button type="submit" class="w-full rounded-button bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-700">
                    Submit
                </button>
            </form>
        </div>

        <div class="mt-6 text-xs text-text-muted">
            Powered by {{ config('app.name') }}.
        </div>
    </div>
</body>
</html>

