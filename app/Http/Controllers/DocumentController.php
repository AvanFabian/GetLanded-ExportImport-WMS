<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display documents for a batch.
     */
    public function index(Request $request)
    {
        $query = Document::with(['batch', 'uploader']);

        if ($request->batch_id) {
            $query->where('batch_id', $request->batch_id);
        }

        if ($request->type) {
            $query->where('document_type', $request->type);
        }

        $documents = $query->latest()->paginate(20);
        $types = Document::getTypes();

        return view('documents.index', compact('documents', 'types'));
    }

    /**
     * Show upload form.
     */
    public function create(Request $request)
    {
        $batch = $request->batch_id ? Batch::findOrFail($request->batch_id) : null;
        $types = Document::getTypes();

        return view('documents.create', compact('batch', 'types'));
    }

    /**
     * Store a new document.
     * 
     * Uses configurable disk (local for dev, s3 for production).
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:20480', // 20MB max
            'document_type' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'batch_id' => 'nullable|exists:batches,id',
            'sales_order_id' => 'nullable|exists:sales_orders,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:issue_date',
            'document_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $file = $request->file('file');
        $disk = config('filesystems.default', 'local');
        
        // Generate unique file path
        $companyId = auth()->user()->company_id;
        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $filePath = "documents/{$companyId}/" . now()->format('Y/m') . "/{$fileName}";

        // Store file
        Storage::disk($disk)->put($filePath, file_get_contents($file));

        // Create document record
        $document = Document::create([
            'company_id' => $companyId,
            'batch_id' => $request->batch_id,
            'sales_order_id' => $request->sales_order_id,
            'purchase_order_id' => $request->purchase_order_id,
            'document_type' => $request->document_type,
            'title' => $request->title,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_disk' => $disk,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'issue_date' => $request->issue_date,
            'expiry_date' => $request->expiry_date,
            'document_number' => $request->document_number,
            'notes' => $request->notes,
            'uploaded_by' => auth()->id(),
        ]);

        return redirect()
            ->route('documents.show', $document)
            ->with('success', 'Document uploaded successfully');
    }

    /**
     * Display a document.
     */
    public function show(Document $document)
    {
        $this->authorize('view', $document);
        
        return view('documents.show', compact('document'));
    }

    /**
     * Download a document.
     */
    public function download(Document $document)
    {
        $this->authorize('view', $document);

        $disk = Storage::disk($document->file_disk);

        if (!$disk->exists($document->file_path)) {
            abort(404, 'File not found');
        }

        return $disk->download($document->file_path, $document->file_name);
    }

    /**
     * Delete a document.
     */
    public function destroy(Document $document)
    {
        $this->authorize('delete', $document);

        // Delete file from storage
        Storage::disk($document->file_disk)->delete($document->file_path);

        $document->delete();

        return redirect()
            ->route('documents.index')
            ->with('success', 'Document deleted successfully');
    }
}
