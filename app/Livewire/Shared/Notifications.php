<?php

namespace App\Livewire\Shared;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.clinic')]
#[Title('Notifications')]
class Notifications extends Component
{
    use WithPagination;

    public function markRead(int $id): void
    {
        auth()->user()->clinicNotifications()->whereKey($id)->update(['read_at' => now()]);
    }

    public function markAllRead(): void
    {
        auth()->user()->clinicNotifications()->whereNull('read_at')->update(['read_at' => now()]);
    }

    public function render()
    {
        return view('livewire.shared.notifications', [
            'items' => auth()->user()->clinicNotifications()->latest()->paginate(10),
        ]);
    }
}
