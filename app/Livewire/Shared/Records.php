<?php

namespace App\Livewire\Shared;

use App\Models\ClinicNotification;
use App\Models\MedicalRecord;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.clinic')]
#[Title('Medical Records')]
class Records extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showVoided = false;
    public $viewingId = null;
    public $editingId = null;
    public string $diagnosis = '';
    public string $treatment = '';
    public string $notes = '';

    public function updated($name): void
    {
        if (in_array($name, ['search', 'showVoided'])) {
            $this->resetPage();
        }
    }

    public function view(int $id): void
    {
        $record = MedicalRecord::withTrashed()->findOrFail($id);
        $this->authorize('view', $record);

        activity('medical_record')->performedOn($record)->causedBy(auth()->user())->log('viewed medical record');

        $this->editingId = null;
        $this->viewingId = $id;
    }

    public function edit(int $id): void
    {
        $record = MedicalRecord::findOrFail($id);
        $this->authorize('update', $record);

        $this->fill(['diagnosis' => $record->diagnosis, 'treatment' => $record->treatment, 'notes' => (string) $record->notes]);
        $this->viewingId = null;
        $this->editingId = $id;
    }

    public function save(): void
    {
        $record = MedicalRecord::with('patient')->findOrFail($this->editingId);
        $this->authorize('update', $record);

        $data = $this->validate([
            'diagnosis' => 'required|string|max:255',
            'treatment' => 'required|string|max:2000',
            'notes' => 'nullable|string|max:2000',
        ]);
        $record->update($data);

        ClinicNotification::send($record->patient->user_id, 'record_updated', 'Medical record updated',
            'Dr. '.auth()->user()->name.' updated your medical record dated '.$record->created_at->format('M j, Y').'.');

        $this->editingId = null;
        session()->flash('status', 'Record updated.');
    }

    public function void(int $id): void
    {
        $record = MedicalRecord::findOrFail($id);
        $this->authorize('delete', $record);
        $record->delete(); // soft delete — the record is kept for the audit trail
        session()->flash('status', 'Record voided. It is preserved in the audit history.');
    }

    public function restore(int $id): void
    {
        $record = MedicalRecord::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $record);
        $record->restore();
        session()->flash('status', 'Record restored.');
    }

    public function close(): void
    {
        $this->viewingId = $this->editingId = null;
    }

    public function render()
    {
        $u = auth()->user();

        $records = MedicalRecord::with(['patient.user', 'doctor', 'visit'])
            ->when($u->hasRole(User::HEAD_NURSE) && $this->showVoided, fn ($q) => $q->withTrashed())
            ->when($u->hasRole(User::PATIENT), fn ($q) => $q->where('patient_id', $u->patient->id))
            ->when($u->hasRole(User::NURSE), fn ($q) => $q->whereHas('visit', fn ($v) => $v->where('nurse_id', $u->id)))
            ->when($u->hasRole(User::DOCTOR), fn ($q) => $q->where(function ($w) use ($u) {
                $w->where('doctor_id', $u->id)
                  ->orWhereHas('patient.appointments', fn ($a) => $a->where('doctor_id', $u->id));
            }))
            ->when($this->search, fn ($q) => $q->where(function ($w) {
                $w->where('diagnosis', 'like', "%{$this->search}%")
                  ->orWhereHas('patient.user', fn ($p) => $p->where('name', 'like', "%{$this->search}%"));
            }))
            ->latest()->paginate(10);

        $viewing = null;
        if ($this->viewingId) {
            $viewing = MedicalRecord::withTrashed()->with(['patient.user', 'doctor', 'visit.vitalSigns'])->find($this->viewingId);
            $viewing && $this->authorize('view', $viewing); // guards against a tampered $viewingId
        }

        return view('livewire.shared.records', compact('records', 'viewing'));
    }
}
