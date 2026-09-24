@props(['label', 'error'])
<div>
    <label class="mb-1 block text-sm font-medium text-slate-700">{{ $label }}</label>
    {{ $slot }}
    @error($error) <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
</div>
