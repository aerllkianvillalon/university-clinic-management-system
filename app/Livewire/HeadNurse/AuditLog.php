<?php

namespace App\Livewire\HeadNurse;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

#[Layout('layouts.clinic')]
#[Title('Audit Log')]
class AuditLog extends Component
{
    use WithPagination;

    public string $log = '';
    public string $search = '';

    public function updated($name): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $activities = Activity::with('causer')
            ->when($this->log, fn ($q) => $q->where('log_name', $this->log))
            ->when($this->search, fn ($q) => $q->where('description', 'like', "%{$this->search}%"))
            ->latest()->paginate(15);

        $logNames = Activity::query()->distinct()->orderBy('log_name')->pluck('log_name');

        return view('livewire.headnurse.audit-log', compact('activities', 'logNames'));
    }
}
