<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use League\CommonMark\CommonMarkConverter;

class CheatSheetController extends Controller
{
    public function index()
    {
        $cheatSheetPath = base_path('CSR_CHEAT_SHEET.md');

        if (!File::exists($cheatSheetPath)) {
            return view('cheat-sheet', [
                'content' => '<p class="text-red-600">CSR Cheat Sheet file not found.</p>',
            ]);
        }

        $markdown = File::get($cheatSheetPath);

        // Convert markdown to HTML
        $converter = new CommonMarkConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        $html = $converter->convert($markdown);

        return view('cheat-sheet', [
            'content' => $html,
        ]);
    }
}
