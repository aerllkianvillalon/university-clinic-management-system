<div>
    <h1 class="mb-6 text-2xl font-semibold">Audit Log</h1>

    <div class="mb-4 flex flex-wrap gap-3">
        <select wire:model.live="log" class="input w-52">
            <option value="">All areas</option>
            @foreach ($logNames as $n) <option value="{{ $n }}">{{ ucfirst(str_replace('_', ' ', $n)) }}</option> @endforeach
        </select>
        <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search description (e.g. viewed, updated)" class="input max-w-sm">
    </div>

    <div class="card overflow-x-auto">
        <table class="tbl">
            <thead><tr><th>When</th><th>Who</th><th>Action</th><th>Record</th><th>Changes</th></tr></thead>
            <tbody>
            @forelse ($activities as $a)
                <tr wire:key="act-{{ $a->id }}">
                    <td class="whitespace-nowrap">{{ $a->created_at->format('M j, Y g:i A') }}</td>
                    <td>{{ $a->causer?->name ?? 'System' }}</td>
                    <td>{{ ucfirst($a->description) }}</td>
                    <td>{{ $a->subject_type ? class_basename($a->subject_type).' #'.$a->subject_id : '—' }}</td>
                    <td>
                        @php $changes = collect($a->attribute_changes)->merge(collect($a->properties)); @endphp
                        @if ($changes->isNotEmpty())
                            <details><summary class="cursor-pointer text-teal-700">Show</summary>
                                <pre class="mt-1 max-w-md overflow-x-auto whitespace-pre-wrap text-xs">{{ $changes->toJson(JSON_PRETTY_PRINT) }}</pre>
                            </details>
                        @else — @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-slate-500">No activity recorded yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $activities->links() }}</div>
</div>
