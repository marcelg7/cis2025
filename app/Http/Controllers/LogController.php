<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Mail;
use App\Mail\LogReviewRequest;
use App\Models\Setting;


class LogController extends Controller
{
	use AuthorizesRequests;
	
    public function myLogs()
    {
        $logs = Activity::causedBy(auth()->user())->latest()->paginate(20);
        return view('logs.my', compact('logs'));
    }
  
    public function requestReview(Request $request)
    {
        $supervisorEmail = Setting::where('key', 'cellular_supervisor_email')->first()->value ?? 'supervisor@example.com';
        Mail::to($supervisorEmail)->send(new LogReviewRequest(auth()->user()));
        return back()->with('success', 'Review requested.');
    }
  
    public function allLogs()
    {
        $this->authorize('view_all_logs');
        $logs = Activity::latest()->paginate(50);
        return view('logs.all', compact('logs'));
    }
}