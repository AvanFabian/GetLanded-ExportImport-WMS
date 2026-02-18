<?php

namespace App\Http\Controllers;

use App\Models\FtaScheme;
use App\Models\FtaRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FtaSchemeController extends Controller
{
    public function index()
    {
        $schemes = FtaScheme::where('company_id', auth()->user()->company_id)->withCount('rates')->get();
        return view('fta-schemes.index', compact('schemes'));
    }

    public function create()
    {
        return view('fta-schemes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
            'member_countries' => 'nullable|string', // Comma-separated or similar input
        ]);

        $validated['company_id'] = auth()->user()->company_id;
        if ($validated['member_countries']) {
            $validated['member_countries'] = array_map('trim', explode(',', $validated['member_countries']));
        } else {
            $validated['member_countries'] = [];
        }

        FtaScheme::create($validated);

        return redirect()->route('fta-schemes.index')->with('success', 'FTA Scheme created.');
    }

    public function show(FtaScheme $ftaScheme)
    {
        $ftaScheme->load('rates');
        return view('fta-schemes.show', compact('ftaScheme'));
    }

    public function storeRate(Request $request, FtaScheme $ftaScheme)
    {
        $validated = $request->validate([
            'hs_code' => 'required|string|max:20',
            'rate' => 'required|numeric|min:0|max:100',
        ]);

        $ftaScheme->rates()->updateOrCreate(
            ['hs_code' => $validated['hs_code']],
            ['rate' => $validated['rate']]
        );

        return back()->with('success', 'Rate saved.');
    }

    public function destroyRate(FtaScheme $ftaScheme, FtaRate $rate)
    {
        $rate->delete();
        return back()->with('success', 'Rate removed.');
    }
}
