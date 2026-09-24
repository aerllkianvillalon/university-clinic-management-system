<?php

namespace App\Livewire\Patient;

use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.clinic')]
#[Title('My Documents')]
class Documents extends Component
{
    use WithFileUploads;

    public $file;

    public function upload(): void
    {
        $this->validate(['file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120']);

        $patient = auth()->user()->patient;
        $path = $this->file->store("documents/{$patient->id}", 'local'); // private disk

        Document::create([
            'patient_id' => $patient->id,
            'uploaded_by' => auth()->id(),
            'file_path' => $path,
            'original_name' => $this->file->getClientOriginalName(),
            'file_type' => $this->file->getMimeType(),
        ]);

        $this->reset('file');
        session()->flash('status', 'Document uploaded.');
    }

    public function remove(int $id): void
    {
        $document = Document::findOrFail($id);
        $this->authorize('delete', $document);

        Storage::disk('local')->delete($document->file_path);
        $document->delete();
        session()->flash('status', 'Document removed.');
    }

    public function render()
    {
        return view('livewire.patient.documents', [
            'documents' => auth()->user()->patient->documents()->latest()->get(),
        ]);
    }
}
