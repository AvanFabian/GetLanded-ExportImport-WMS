<?php

namespace App\Http\Controllers;

use App\Models\HsCode;
use Illuminate\Http\Request;

class HsCodeController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q');
        
        if (!$query) {
            return response()->json([]);
        }

        $codes = HsCode::where('code', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->limit(10)
            ->get();

        return response()->json($codes);
    }
}
