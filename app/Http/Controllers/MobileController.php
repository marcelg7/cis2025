<?php
namespace App\Http\Controllers;

use App\Models\Shortcode as LaravelShortcode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MobileController extends Controller
{
    public function testWordpress()
    {
        try {
            // Fetch from WordPress
            $wp_shortcodes = DB::connection('wordpress')
                ->table('SH_CD_SHORTCODES')
                ->get();

            Log::info('Fetched WP shortcodes: ' . $wp_shortcodes->count());
            if ($wp_shortcodes->isNotEmpty()) {
                Log::info('First WP shortcode: ' . json_encode($wp_shortcodes->first()));
            }

            // Sync to Laravel
            foreach ($wp_shortcodes as $wp_shortcode) {
                try {
                    LaravelShortcode::updateOrCreate(
                        ['wp_id' => $wp_shortcode->id],
                        [
                            'slug' => $wp_shortcode->slug,
                            'data' => $wp_shortcode->data,
                            'disabled' => $wp_shortcode->disabled,
                            'previous_slug' => $wp_shortcode->previous_slug,
                            'multisite' => $wp_shortcode->multisite,
                        ]
                    );
                } catch (\Exception $e) {
                    Log::error('Sync Error for WP ID ' . $wp_shortcode->id . ': ' . $e->getMessage());
                }
            }

            // Fetch Laravel shortcodes
            $shortcodes = LaravelShortcode::all();
            Log::info('Laravel shortcodes: ' . $shortcodes->count());

            return view('mobile.test', ['shortcodes' => $shortcodes]);
        } catch (\Exception $e) {
            Log::error('WP Test Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }
}