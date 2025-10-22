<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use League\CommonMark\CommonMarkConverter;

class ReadmeController extends Controller
{
	public function index()
	{
		$readmePath = base_path('README.md');

		if (!File::exists($readmePath)) {
			return view('readme', [
				'content' => '<p class="text-red-600">README.md file not found.</p>',
			]);
		}

		$markdown = File::get($readmePath);

		// Convert markdown to HTML
		$converter = new CommonMarkConverter([
			'html_input' => 'strip',
			'allow_unsafe_links' => false,
		]);

		$html = $converter->convert($markdown);

		return view('readme', [
			'content' => $html,
		]);
	}
}
