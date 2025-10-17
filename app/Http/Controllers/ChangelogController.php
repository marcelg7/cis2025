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
		$owner = env('GITHUB_OWNER');
		$repo = env('GITHUB_REPO');
		$token = env('GITHUB_TOKEN'); // From your .env
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
				$commits[] = [
					'hash' => substr($commitData['sha'], 0, 7),
					'author' => $commitData['commit']['author']['name'],
					'date' => $commitData['commit']['author']['date'],
					'message' => $commitData['commit']['message'],
				];
			}
		} else {
			Log::error('Failed to fetch GitHub commits: ' . $response->body());
			$commits[] = ['hash' => '', 'author' => '', 'date' => '', 'message' => 'No changelog available. GitHub API call failed.'];
		}

		return view('changelog', compact('commits'));
	}
}