<div>
    <x-flash />
    <h1 class="mb-6 text-2xl font-semibold">Welcome, {{ auth()->user()->name }}</h1>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($data['stats'] as $label => $value)
            <x-stat :label="$label" :value="$value" />
        @endforeach
    </div>

    @if ($data['list'] !== null)
        <div class="card mt-6">
            <h2 class="mb-3 font-semibold">{{ $data['listTitle'] }}</h2>
            <table class="tbl">
                <thead><tr><th>Date</th><th>Time</th><th>Patient</th><th>Doctor</th><th>Status</th></tr></thead>
                <tbody>
                @forelse ($data['list'] as $a)
                    <tr>
                        <td>{{ $a->appointment_date->format('M j') }}</td>
                        <td>{{ $a->time_label }}</td>
                        <td>{{ $a->patient->user->name }}</td>
                        <td>Dr. {{ $a->doctor->name }}</td>
                        <td><x-badge :status="$a->status" /></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-slate-500">Nothing scheduled.
                        @if (auth()->user()->role === 'patient') <a class="text-teal-700 underline" href="{{ route('patient.book') }}">Book an appointment</a>@endif
                    </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    @endif

    @if ($data['chart'])
        <div class="mt-6 grid gap-4 lg:grid-cols-3">
            <div class="card lg:col-span-2" wire:ignore>
                <h2 class="mb-3 font-semibold">Appointments, last 7 days</h2>
                <canvas id="weekChart" height="120"></canvas>
            </div>
            <div class="card" wire:ignore>
                <h2 class="mb-3 font-semibold">By status</h2>
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        @script
        <script>
            const c = @js($data['chart']);
            new Chart(document.getElementById('weekChart'), {
                type: 'bar',
                data: { labels: c.labels, datasets: [{ label: 'Appointments', data: c.values, backgroundColor: '#0f766e' }] },
                options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
            });
            new Chart(document.getElementById('statusChart'), {
                type: 'doughnut',
                data: { labels: c.statusLabels, datasets: [{ data: c.statusValues, backgroundColor: ['#3b82f6', '#f59e0b', '#10b981', '#94a3b8'] }] }
            });
        </script>
        @endscript
    @endif
</div>
