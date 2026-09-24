<?php

namespace App\Livewire\Doctor;

use App\Models\Appointment;
use App\Models\ClinicNotification;
use App\Models\MedicalRecord;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.clinic')]
#[Title('Consultations')]
class Consultation extends Component
{
    public $appointmentId = null;
    public string $diagnosis = '';
    public string $treatment = '';
    public string $notes = '';

    /** Checked-in appointments that belong to the logged-in doctor. */
    private function mine()
    {
        return Appointment::where('doctor_id', auth()->id())->where('status', Appointment::CHECKED_IN);
    }

    public function select(int $id): void
    {
        $this->appointmentId = $this->mine()->findOrFail($id)->id;
        $this->reset(['diagnosis', 'treatment', 'notes']);
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->authorize('create', MedicalRecord::class);
        $appointment = $this->mine()->with(['visit', 'patient'])->findOrFail($this->appointmentId);

        $data = $this->validate([
            'diagnosis' => 'required|string|max:255',
            'treatment' => 'required|string|max:2000',
            'notes' => 'nullable|string|max:2000',
        ]);

        DB::transaction(function () use ($appointment, $data) {
            MedicalRecord::create($data + [
                'patient_id' => $appointment->patient_id,
                'visit_id' => $appointment->visit->id,
                'doctor_id' => auth()->id(),
            ]);
            $appointment->update(['status' => Appointment::COMPLETED]);
        });

        ClinicNotification::send($appointment->patient->user_id, 'record_updated', 'New medical record',
            'Dr. '.auth()->user()->name.' added a record from your visit on '.$appointment->appointment_date->format('M j, Y').'.');

        $this->reset(['appointmentId', 'diagnosis', 'treatment', 'notes']);
        session()->flash('status', 'Consultation saved and appointment completed.');
    }

    public function render()
    {
        $queue = $this->mine()->with(['patient.user'])->orderBy('appointment_date')->orderBy('appointment_time')->get();

        $selected = $this->appointmentId
            ? $this->mine()->with(['patient.user', 'visit.vitalSigns'])->find($this->appointmentId)
            : null;

        $history = $selected
            ? MedicalRecord::with('doctor')->where('patient_id', $selected->patient_id)->latest()->limit(5)->get()
            : collect();

        return view('livewire.doctor.consultation', compact('queue', 'selected', 'history'));
    }
}
