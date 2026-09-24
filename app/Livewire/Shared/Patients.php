<?php

namespace App\Livewire\Shared;

use App\Models\Patient;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.clinic')]
#[Title('Patients')]
class Patients extends Component
{
    use WithPagination;

    public string $search = '';
    public $viewingId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function view(int $id): void
    {
        $patient = Patient::findOrFail($id);
        $this->authorize('view', $patient);

        // Access logging (Data Privacy Act): who opened which patient profile.
        activity('patient')->performedOn($patient)->causedBy(auth()->user())->log('viewed patient profile');

        $this->viewingId = $id;
    }

    public function close(): void
    {
        $this->viewingId = null;
    }

    public function render()
    {
        $this->authorize('viewAny', Patient::class);

        $patients = Patient::with('user')
            ->when($this->search, fn ($q) => $q->where(function ($w) {
                $w->where('student_id', 'like', "%{$this->search}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$this->search}%"));
            }))
            ->latest()->paginate(10);

        $viewing = $this->viewingId ? Patient::with('user')->find($this->viewingId) : null;
        if ($viewing) {
            $this->authorize('view', $viewing);
        }

        return view('livewire.shared.patients', compact('patients', 'viewing'));
    }
}
