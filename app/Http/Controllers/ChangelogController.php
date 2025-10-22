<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChangelogController extends Controller
{
	public function index()
	{
		$commits = [];
		$owner = config('services.github.owner');
		$repo = config('services.github.repo');
		$token = config('services.github.token');
		$url = "https://api.github.com/repos/{$owner}/{$repo}/commits";

		$response = Http::withToken($token)
			->withHeaders([
				'Accept' => 'application/vnd.github.v3+json',
				'User-Agent' => 'Hay-CIS-App',
			])
			->get($url, [
				'per_page' => 50,
			]);

		if ($response->successful()) {
			$data = $response->json();
			foreach ($data as $commitData) {
				$message = $commitData['commit']['message'];

				// Split message into title and body
				$lines = explode("\n", $message);
				$title = array_shift($lines);

				// Keep remaining lines as body, preserving blank lines for markdown
				$body = implode("\n", $lines);
				$body = trim($body);

				$commits[] = [
					'hash' => substr($commitData['sha'], 0, 7),
					'author' => $commitData['commit']['author']['name'],
					'date' => $commitData['commit']['author']['date'],
					'title' => $title,
					'body' => $body,
				];
			}
		} else {
			Log::error('Failed to fetch GitHub commits: ' . $response->body());
			$commits[] = ['hash' => '', 'author' => '', 'date' => '', 'title' => 'Error', 'body' => 'No changelog available. GitHub API call failed.'];
		}

		return view('changelog', compact('commits'));
	}
}