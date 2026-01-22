<?php

namespace App\Http\Controllers;

use App\Models\Webhook;
use App\Services\WebhookService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WebhookController extends Controller
{
    public function __construct(
        protected WebhookService $webhookService
    ) {}

    public function index()
    {
        $webhooks = Webhook::where('company_id', auth()->user()->company_id)
            ->withCount('logs')
            ->latest()
            ->paginate(20);

        return Inertia::render('Settings/Webhooks/Index', [
            'webhooks' => $webhooks,
            'availableEvents' => Webhook::EVENTS,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'url' => 'required|url',
            'events' => 'required|array|min:1',
            'events.*' => 'string|in:' . implode(',', array_keys(Webhook::EVENTS)),
            'secret' => 'nullable|string|max:255',
        ]);

        Webhook::create([
            'company_id' => auth()->user()->company_id,
            'url' => $validated['url'],
            'events' => $validated['events'],
            'secret' => $validated['secret'] ?? null,
            'is_active' => true,
        ]);

        return back()->with('success', 'Webhook created successfully.');
    }

    public function update(Request $request, Webhook $webhook)
    {
        $this->authorize('update', $webhook);

        $validated = $request->validate([
            'url' => 'required|url',
            'events' => 'required|array|min:1',
            'secret' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $webhook->update($validated);

        return back()->with('success', 'Webhook updated successfully.');
    }

    public function destroy(Webhook $webhook)
    {
        $this->authorize('delete', $webhook);

        $webhook->delete();

        return back()->with('success', 'Webhook deleted.');
    }

    public function test(Webhook $webhook)
    {
        $this->authorize('update', $webhook);

        $result = $this->webhookService->test($webhook);

        return response()->json($result);
    }

    public function logs(Webhook $webhook)
    {
        $this->authorize('view', $webhook);

        $logs = $webhook->logs()->latest()->paginate(50);

        return Inertia::render('Settings/Webhooks/Logs', [
            'webhook' => $webhook,
            'logs' => $logs,
        ]);
    }
}
