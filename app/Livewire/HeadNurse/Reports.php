<?php

namespace App\Livewire\HeadNurse;

use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Visit;
use App\Models\VitalSign;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.clinic')]
#[Title('Reports')]
class Reports extends Component
{
    public string $from = '';
    public string $to = '';

    public function mount(): void
    {
        $this->from = now()->startOfMonth()->toDateString();
        $this->to = today()->toDateString();
    }

    public function render()
    {
        $from = $this->from ?: now()->startOfMonth()->toDateString();
        $to = $this->to ?: today()->toDateString();
        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        $byStatus = Appointment::whereBetween('appointment_date', [$from, $to])
            ->select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status');

        $byDoctor = Appointment::with('doctor')->whereBetween('appointment_date', [$from, $to])
            ->select('doctor_id', DB::raw('count(*) as total'))->groupBy('doctor_id')->orderByDesc('total')->get();

        $topDiagnoses = MedicalRecord::whereBetween('created_at', ["$from 00:00:00", "$to 23:59:59"])
            ->selectRaw('LOWER(TRIM(diagnosis)) as diagnosis, COUNT(*) as total')
            ->groupBy(DB::raw('LOWER(TRIM(diagnosis))'))->orderByDesc('total')->limit(5)->get();

        $visits = Visit::whereBetween('visit_date', [$from, $to])->count();
        $abnormal = VitalSign::whereBetween('created_at', ["$from 00:00:00", "$to 23:59:59"])
            ->get()->filter(fn ($v) => count($v->abnormalFlags()) > 0)->count();

        return view('livewire.headnurse.reports', compact('byStatus', 'byDoctor', 'topDiagnoses', 'visits', 'abnormal'));
    }
}
