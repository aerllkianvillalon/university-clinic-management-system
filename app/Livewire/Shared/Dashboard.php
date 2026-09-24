<?php

namespace App\Livewire\Shared;

use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.clinic')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function render()
    {
        $u = auth()->user();

        $data = match ($u->role) {
            User::PATIENT => $this->forPatient($u),
            User::NURSE => $this->forNurse(),
            User::DOCTOR => $this->forDoctor($u),
            default => $this->forHeadNurse(),
        };

        return view('livewire.shared.dashboard', ['data' => $data]);
    }

    private function forPatient(User $u): array
    {
        $pid = $u->patient->id;
        $upcoming = Appointment::where('patient_id', $pid)->where('status', 'scheduled')
            ->whereDate('appointment_date', '>=', today());

        return [
            'stats' => [
                'Upcoming appointments' => (clone $upcoming)->count(),
                'Medical records' => MedicalRecord::where('patient_id', $pid)->count(),
                'Unread notifications' => $u->clinicNotifications()->whereNull('read_at')->count(),
            ],
            'listTitle' => 'Your next appointments',
            'list' => (clone $upcoming)->with(['doctor', 'patient.user'])
                ->orderBy('appointment_date')->orderBy('appointment_time')->limit(5)->get(),
            'chart' => null,
        ];
    }

    private function forNurse(): array
    {
        $today = Appointment::whereDate('appointment_date', today());

        return [
            'stats' => [
                "Today's appointments" => (clone $today)->where('status', '!=', 'cancelled')->count(),
                'Waiting for check-in' => (clone $today)->where('status', 'scheduled')->count(),
                'Visits today' => Visit::whereDate('visit_date', today())->count(),
            ],
            'listTitle' => "Today's appointments",
            'list' => (clone $today)->where('status', '!=', 'cancelled')->with(['doctor', 'patient.user'])
                ->orderBy('appointment_time')->get(),
            'chart' => null,
        ];
    }

    private function forDoctor(User $u): array
    {
        $today = Appointment::where('doctor_id', $u->id)->whereDate('appointment_date', today());

        return [
            'stats' => [
                "Today's patients" => (clone $today)->where('status', '!=', 'cancelled')->count(),
                'Awaiting consultation' => (clone $today)->where('status', 'checked_in')->count(),
                'Completed today' => (clone $today)->where('status', 'completed')->count(),
            ],
            'listTitle' => "Today's patients",
            'list' => (clone $today)->where('status', '!=', 'cancelled')->with(['doctor', 'patient.user'])
                ->orderBy('appointment_time')->get(),
            'chart' => null,
        ];
    }

    private function forHeadNurse(): array
    {
        $days = collect(range(6, 0))->map(fn ($i) => now()->subDays($i)->toDateString());
        $counts = Appointment::whereBetween('appointment_date', [$days->first(), $days->last()])
            ->selectRaw('appointment_date as d, count(*) as c')->groupBy('d')->pluck('c', 'd');
        $statuses = Appointment::selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');

        return [
            'stats' => [
                'Patients' => Patient::count(),
                'Nurses' => User::where('role', User::NURSE)->where('is_active', true)->count(),
                'Doctors' => User::where('role', User::DOCTOR)->where('is_active', true)->count(),
                "Today's appointments" => Appointment::whereDate('appointment_date', today())->count(),
            ],
            'listTitle' => null,
            'list' => null,
            'chart' => [
                'labels' => $days->map(fn ($d) => date('M j', strtotime($d)))->values(),
                'values' => $days->map(fn ($d) => (int) ($counts[$d] ?? 0))->values(),
                'statusLabels' => $statuses->keys()->map(fn ($s) => ucfirst(str_replace('_', ' ', $s)))->values(),
                'statusValues' => $statuses->values(),
            ],
        ];
    }
}
