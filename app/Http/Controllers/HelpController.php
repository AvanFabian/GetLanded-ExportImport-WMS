<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HelpController extends Controller
{
    /**
     * Display the Knowledge Base Home
     */
    public function index()
    {
        return view('help.index');
    }

    /**
     * Display the FAQ Page
     */
    public function faq()
    {
        return view('help.faq');
    }

    /**
     * Display specific help articles
     */
    public function article($slug)
    {
        // For now, we simple return views based on slug to keep it static but organized
        // In future this could query a database
        
        $validArticles = ['smart-importer-guide'];
        
        if (!in_array($slug, $validArticles)) {
            abort(404);
        }

        return view("help.articles.{$slug}");
    }
}
