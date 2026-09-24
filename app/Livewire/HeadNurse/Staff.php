<?php

namespace App\Livewire\HeadNurse;

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.clinic')]
#[Title('Manage Staff')]
class Staff extends Component
{
    public string $type = 'nurses';
    public string $name = '';
    public string $email = '';
    public string $password = '';

    public function mount(string $type): void
    {
        abort_unless(in_array($type, ['nurses', 'doctors'], true), 404);
        $this->type = $type;
    }

    private function role(): string
    {
        return $this->type === 'nurses' ? User::NURSE : User::DOCTOR;
    }

    public function create(): void
    {
        $data = $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        User::create($data + ['role' => $this->role(), 'email_verified_at' => now()]);

        $this->reset(['name', 'email', 'password']);
        session()->flash('status', 'Account created.');
    }

    public function toggle(int $id): void
    {
        // Scoped by role: the head nurse can only toggle nurses/doctors from this screen.
        $user = User::where('role', $this->role())->findOrFail($id);
        $user->update(['is_active' => ! $user->is_active]);
    }

    public function render()
    {
        return view('livewire.headnurse.staff', [
            'staff' => User::where('role', $this->role())->orderBy('name')->get(),
            'label' => $this->type === 'nurses' ? 'nurse' : 'doctor',
        ]);
    }
}
