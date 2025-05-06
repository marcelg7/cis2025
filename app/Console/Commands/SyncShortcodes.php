<?php
namespace App\Console\Commands;

use App\Models\Shortcode as LaravelShortcode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncShortcodes extends Command
{
    protected $signature = 'shortcodes:sync';
    protected $description = 'Sync WordPress SH_CD_SHORTCODES to Laravel shortcodes table';

    public function handle()
    {
        try {
            // Fetch from WordPress
            $wp_shortcodes = DB::connection('wordpress')
                ->table('SH_CD_SHORTCODES')
                ->get();

            Log::info('Sync: Fetched WP shortcodes: ' . $wp_shortcodes->count());

            // Sync to Laravel
            $synced = 0;
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
                    $synced++;
                } catch (\Exception $e) {
                    Log::error('Sync Error for WP ID ' . $wp_shortcode->id . ': ' . $e->getMessage());
                }
            }

            Log::info('Sync: Successfully synced ' . $synced . ' shortcodes');
            $this->info('Shortcodes synced successfully! Synced: ' . $synced);
        } catch (\Exception $e) {
            Log::error('Sync Command Error: ' . $e->getMessage());
            $this->error('Failed to sync shortcodes: ' . $e->getMessage());
        }
    }
}