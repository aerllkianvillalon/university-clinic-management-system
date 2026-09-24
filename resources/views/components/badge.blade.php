@props(['status'])
@php
    $c = match ($status) {
        'scheduled' => 'bg-blue-100 text-blue-700',
        'checked_in' => 'bg-amber-100 text-amber-700',
        'completed' => 'bg-emerald-100 text-emerald-700',
        'cancelled', 'voided' => 'bg-slate-200 text-slate-600',
        default => 'bg-slate-100 text-slate-600',
    };
@endphp
<span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $c }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
