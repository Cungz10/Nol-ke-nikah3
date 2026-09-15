<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentRequest;
use App\Models\DocumentChecklist;
use App\Models\Team;
use App\Models\WeddingDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function index(Team $current_team): Response|RedirectResponse
    {
        $project = $current_team->weddingProject;
        if (! $project) {
            return redirect()->route('onboarding.show', $current_team);
        }

        $documents = $project->documents()
            ->with('vendor')
            ->latest()
            ->get();

        $vendors = $project->vendors()->select('id', 'name')->get();

        $checklist = DocumentChecklist::forProject($project);

        return Inertia::render('Documents/Index', [
            'weddingProject' => $project,
            'documents' => $documents,
            'vendors' => $vendors,
            'checklist' => $checklist,
        ]);
    }

    public function store(StoreDocumentRequest $request, Team $current_team): RedirectResponse
    {
        $project = $current_team->weddingProject;
        if (! $project) {
            return redirect()->route('onboarding.show', $current_team);
        }

        $file = $request->file('file');
        $path = $file->store('wedding_documents/'.$project->id, 'local');

        $document = WeddingDocument::create([
            'wedding_project_id' => $project->id,
            'vendor_id' => $request->input('vendor_id'),
            'title' => $request->input('title'),
            'category' => $request->input('category'),
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size_bytes' => $file->getSize(),
            'notes' => $request->input('notes'),
        ]);
        $this->logCreated($document, $project->id, "Dokumen \"{$document->title}\" diunggah");

        return back()->with('success', 'Dokumen berhasil diunggah');
    }

    public function download(Team $current_team, WeddingDocument $document): StreamedResponse
    {
        $project = $current_team->weddingProject;
        if (! $project || $document->wedding_project_id !== $project->id) {
            abort(404);
        }

        $this->authorize('view', $document);

        if (! Storage::disk('local')->exists($document->file_path)) {
            abort(404, 'File tidak ditemukan di server');
        }

        return Storage::disk('local')->download($document->file_path, $document->file_name);
    }

    public function destroy(Team $current_team, WeddingDocument $document): RedirectResponse
    {
        $project = $current_team->weddingProject;
        if (! $project || $document->wedding_project_id !== $project->id) {
            abort(404);
        }

        $this->authorize('delete', $document);

        $docTitle = $document->title;
        $this->logDeleted($document, $project->id, "Dokumen \"{$docTitle}\" dihapus");

        if (Storage::disk('local')->exists($document->file_path)) {
            Storage::disk('local')->delete($document->file_path);
        }

        $document->delete();

        return back()->with('success', 'Dokumen berhasil dihapus');
    }
}
