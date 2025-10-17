<?php

namespace App\Http\Controllers;

use App\Models\TermsOfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class TermsOfServiceController extends Controller
{
    /**
     * Display the ToS management page
     */
    public function index()
    {
        $versions = TermsOfService::getAllVersions();
        $activeVersion = TermsOfService::getActive();
        
        return view('terms-of-service.index', compact('versions', 'activeVersion'));
    }

    /**
     * Show the upload form
     */
    public function create()
    {
        return view('terms-of-service.upload');
    }

    /**
     * Store a new Terms of Service PDF
     */
    public function store(Request $request)
    {
        $request->validate([
            'pdf' => 'required|file|mimes:pdf|max:10240', // 10MB max
            'version' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $file = $request->file('pdf');
            $originalName = $file->getClientOriginalName();
            
            // Generate a unique filename
            $filename = 'terms_of_service_' . time() . '_' . $originalName;
            
            // Store the file
            $path = $file->storeAs('terms-of-service', $filename, 'public');
            
            // Create the database record
            $tos = TermsOfService::create([
                'filename' => $originalName,
                'path' => $path,
                'version' => $request->version ?? 'v' . (TermsOfService::count() + 1),
                'notes' => $request->notes,
                'uploaded_by' => auth()->id(),
                'is_active' => false, // Not active by default
            ]);

            Log::info('Terms of Service uploaded', [
                'tos_id' => $tos->id,
                'version' => $tos->version,
                'uploaded_by' => auth()->id(),
            ]);

            return redirect()->route('terms-of-service.index')
                ->with('success', 'Terms of Service uploaded successfully. Click "Activate" to make it the active version.');
                
        } catch (\Exception $e) {
            Log::error('Failed to upload Terms of Service', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to upload Terms of Service: ' . $e->getMessage());
        }
    }

    /**
     * Activate a specific version
     */
    public function activate($id)
    {
        $tos = TermsOfService::findOrFail($id);
        
        try {
            $tos->activate();
            
            Log::info('Terms of Service activated', [
                'tos_id' => $tos->id,
                'version' => $tos->version,
                'activated_by' => auth()->id(),
            ]);
            
            return redirect()->route('terms-of-service.index')
                ->with('success', "Version {$tos->version} is now active.");
                
        } catch (\Exception $e) {
            Log::error('Failed to activate Terms of Service', [
                'tos_id' => $id,
                'error' => $e->getMessage(),
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to activate Terms of Service: ' . $e->getMessage());
        }
    }

    /**
     * Download a specific version
     */
    public function download($id)
    {
        $tos = TermsOfService::findOrFail($id);
        
        if (!Storage::disk('public')->exists($tos->path)) {
            return redirect()->back()
                ->with('error', 'Terms of Service file not found.');
        }
        
        return Storage::disk('public')->download($tos->path, $tos->filename);
    }

    /**
     * Delete a version (soft delete - just deactivate)
     */
    public function destroy($id)
    {
        $tos = TermsOfService::findOrFail($id);
        
        if ($tos->is_active) {
            return redirect()->back()
                ->with('error', 'Cannot delete the active version. Please activate another version first.');
        }
        
        try {
            // Delete the file
            if (Storage::disk('public')->exists($tos->path)) {
                Storage::disk('public')->delete($tos->path);
            }
            
            // Delete the record
            $tos->delete();
            
            Log::info('Terms of Service deleted', [
                'tos_id' => $id,
                'version' => $tos->version,
                'deleted_by' => auth()->id(),
            ]);
            
            return redirect()->route('terms-of-service.index')
                ->with('success', 'Terms of Service version deleted successfully.');
                
        } catch (\Exception $e) {
            Log::error('Failed to delete Terms of Service', [
                'tos_id' => $id,
                'error' => $e->getMessage(),
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to delete Terms of Service: ' . $e->getMessage());
        }
    }
}