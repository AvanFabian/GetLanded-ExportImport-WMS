<?php

namespace App\Http\Controllers;

use App\Models\ImportJob;
use App\Services\ImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

        return view('imports.index', compact('jobs'));
    }

    public function create()
    {
        $importTypes = [
            'products' => 'Products',
            'customers' => 'Customers',
            'suppliers' => 'Suppliers',
        ];

        return view('imports.create', compact('importTypes'));
    }

    public function upload(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx|max:10240',
            'type' => 'required|in:products,customers,suppliers,stock',
        ]);

        $path = $request->file('file')->store('imports');
        
        try {
            $parsed = $this->importService->parseFile($path);

            $job = ImportJob::create([
                'company_id' => auth()->user()->company_id,
                'type' => $validated['type'],
                'file_path' => $path,
                'status' => ImportJob::STATUS_MAPPING,
                'total_rows' => $parsed['total_rows'],
                // Store headers/sample in metadata/options if needed, 
                // but for now we'll re-parse in the mapping step or just rely on the file
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('imports.mapping', $job);

        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Error parsing file: ' . $e->getMessage()]);
        }
    }

    public function mapping(ImportJob $job)
    {
        $this->authorize('update', $job);

        if ($job->status !== ImportJob::STATUS_MAPPING) {
            return redirect()->route('imports.show', $job);
        }

        try {
            // content re-parse to get headers
            $parsed = $this->importService->parseFile($job->file_path);
            $suggestions = $this->importService->suggestMappings($parsed['headers'], $job->type);
            $fields = array_keys($this->importService->getAliases($job->type));
            
            return view('imports.mapping', [
                'job' => $job,
                'headers' => $parsed['headers'],
                'sample' => $parsed['sample'],
                'suggestions' => $suggestions,
                'fields' => $fields,
            ]);
        } catch (\Exception $e) {
            return redirect()->route('imports.index')->with('error', 'File not found or invalid.');
        }
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

        // Dispatch to queue as a proper job (not a closure)
        \App\Jobs\ProcessImportJob::dispatch($job->id, auth()->user()->company_id);

        return redirect()->route('imports.show', $job)
            ->with('success', 'Import process started in background.');
    }

    public function show(ImportJob $job)
    {
        $this->authorize('view', $job);
        $job->load('creator');

        return view('imports.show', compact('job'));
    }

    public function progress(ImportJob $job)
    {
        $this->authorize('view', $job);

        return response()->json([
             'status' => $job->status,
             'progress' => ($job->total_rows > 0) ? round(($job->processed_rows / $job->total_rows) * 100) : 0,
             'processed' => $job->processed_rows,
             'failed' => $job->failed_rows,
             'total' => $job->total_rows,
        ]);
    }

    /**
     * Return all active (processing/pending) import jobs for the floating progress bar.
     */
    public function activeJobs()
    {
        $jobs = ImportJob::where('company_id', auth()->user()->company_id)
            ->whereIn('status', [ImportJob::STATUS_PROCESSING, ImportJob::STATUS_PENDING])
            ->select(['id', 'type', 'status', 'total_rows', 'processed_rows', 'failed_rows'])
            ->latest()
            ->get();

        return response()->json($jobs);
    }
}
