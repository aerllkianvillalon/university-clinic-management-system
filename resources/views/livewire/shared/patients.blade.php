<div>
    <h1 class="mb-6 text-2xl font-semibold">Patients</h1>

    <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search by name or student ID" class="input mb-4 max-w-sm">

    <div class="card overflow-x-auto">
        <table class="tbl">
            <thead><tr><th>Name</th><th>Student ID</th><th>Sex</th><th>Contact</th><th>Consent</th><th></th></tr></thead>
            <tbody>
            @forelse ($patients as $p)
                <tr>
                    <td>{{ $p->user->name }}</td>
                    <td>{{ $p->student_id ?? '—' }}</td>
                    <td>{{ ucfirst($p->sex ?? '—') }}</td>
                    <td>{{ $p->contact_number ?? '—' }}</td>
                    <td>{{ $p->consent_at ? 'Yes' : 'Not yet' }}</td>
                    <td class="text-right"><button wire:click="view({{ $p->id }})" class="btn btn-ghost">Open profile</button></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-slate-500">No patients match your search.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $patients->links() }}</div>

    @if ($viewing)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 p-4">
            <div class="card w-full max-w-lg">
                <h2 class="text-lg font-semibold">{{ $viewing->user->name }}</h2>
                <dl class="mt-3 grid grid-cols-3 gap-y-2 text-sm">
                    <dt class="text-slate-500">Student ID</dt><dd class="col-span-2">{{ $viewing->student_id ?? '—' }}</dd>
                    <dt class="text-slate-500">Email</dt><dd class="col-span-2">{{ $viewing->user->email }}</dd>
                    <dt class="text-slate-500">Date of birth</dt><dd class="col-span-2">{{ $viewing->date_of_birth?->format('M j, Y') ?? '—' }}</dd>
                    <dt class="text-slate-500">Sex</dt><dd class="col-span-2">{{ ucfirst($viewing->sex ?? '—') }}</dd>
                    <dt class="text-slate-500">Contact</dt><dd class="col-span-2">{{ $viewing->contact_number ?? '—' }}</dd>
                    <dt class="text-slate-500">Address</dt><dd class="col-span-2">{{ $viewing->address ?? '—' }}</dd>
                    <dt class="text-slate-500">Medical history</dt><dd class="col-span-2 whitespace-pre-line">{{ $viewing->medical_history ?: 'None provided' }}</dd>
                </dl>
                <p class="mt-4 text-xs text-slate-400">This view was recorded in the audit log.</p>
                <div class="mt-4 text-right"><button wire:click="close" class="btn btn-ghost">Close</button></div>
            </div>
        </div>
    @endif
</div>
