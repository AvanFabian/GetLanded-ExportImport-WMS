<?php

namespace App\Http\Controllers;

use App\Models\ImportJob;
use App\Services\ImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ImportController extends Controller
{
    public function __construct(
        protected ImportService $importService
    ) {}

    public function index()
    {
        $jobs = ImportJob::where('company_id', auth()->user()->company_id)
            ->with('creator')
            ->latest()
            ->paginate(20);

        return Inertia::render('Import/Index', [
            'jobs' => $jobs,
        ]);
    }

    public function create()
    {
        return Inertia::render('Import/Create', [
            'importTypes' => [
                ImportJob::TYPE_PRODUCTS => 'Products',
                ImportJob::TYPE_CUSTOMERS => 'Customers',
                ImportJob::TYPE_SUPPLIERS => 'Suppliers',
            ],
        ]);
    }

    public function upload(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
            'type' => 'required|in:products,customers,suppliers,stock',
        ]);

        $path = $request->file('file')->store('imports');
        $parsed = $this->importService->parseFile($path);

        $job = ImportJob::create([
            'company_id' => auth()->user()->company_id,
            'type' => $validated['type'],
            'file_path' => $path,
            'status' => ImportJob::STATUS_MAPPING,
            'total_rows' => $parsed['total_rows'],
            'created_by' => auth()->id(),
        ]);

        $suggestions = $this->importService->suggestMappings($parsed['headers'], $validated['type']);

        return Inertia::render('Import/Mapping', [
            'job' => $job,
            'headers' => $parsed['headers'],
            'sample' => $parsed['sample'],
            'suggestions' => $suggestions,
        ]);
    }

    public function confirmMapping(Request $request, ImportJob $job)
    {
        $this->authorize('update', $job);

        $validated = $request->validate([
            'mapping' => 'required|array',
        ]);

        $job->update([
            'column_mapping' => $validated['mapping'],
        ]);

        // Start processing in background
        dispatch(function () use ($job) {
            $this->importService->process($job);
        })->afterResponse();

        return redirect()->route('imports.show', $job)
            ->with('success', 'Import started. You will be notified when complete.');
    }

    public function show(ImportJob $job)
    {
        $this->authorize('view', $job);

        return Inertia::render('Import/Show', [
            'job' => $job->load('creator'),
        ]);
    }

    public function progress(ImportJob $job)
    {
        $this->authorize('view', $job);

        return response()->json([
            'status' => $job->status,
            'progress' => $job->progress_percentage,
            'processed' => $job->processed_rows,
            'failed' => $job->failed_rows,
            'total' => $job->total_rows,
        ]);
    }

    public function errors(ImportJob $job)
    {
        $this->authorize('view', $job);

        return response()->json([
            'errors' => $job->error_log ?? [],
        ]);
    }
}
