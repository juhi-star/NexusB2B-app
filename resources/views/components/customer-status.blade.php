@props(['status'])
@php
    $color = match ($status) {
        \App\Enums\CustomerStatus::Active => 'bg-green-100 text-green-700',
        \App\Enums\CustomerStatus::Suspended => 'bg-amber-100 text-amber-700',
        \App\Enums\CustomerStatus::Inactive => 'bg-slate-100 text-slate-600',
    };
@endphp
<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $color }}">
    {{ $status->label() }}
</span>
