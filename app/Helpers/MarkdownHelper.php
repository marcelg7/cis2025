<?php

namespace App\Helpers;

use HTMLPurifier;
use HTMLPurifier_Config;

class MarkdownHelper
{
    private static ?HTMLPurifier $purifier = null;

    /**
     * Get configured HTMLPurifier instance
     */
    private static function getPurifier(): HTMLPurifier
    {
        if (self::$purifier === null) {
            $config = HTMLPurifier_Config::createDefault();

            // Set cache directory
            $config->set('Cache.SerializerPath', storage_path('app/htmlpurifier'));

            // Allow safe HTML tags
            $config->set('HTML.Allowed', 'p,b,strong,i,em,u,a[href|title],ul,ol,li,br,span,div,h1,h2,h3,h4,h5,h6,blockquote,pre,code,hr,table,thead,tbody,tr,th,td');

            // Allow target="_blank" for links
            $config->set('Attr.AllowedFrameTargets', ['_blank']);

            // Convert relative URLs to absolute if needed
            $config->set('URI.Base', config('app.url'));
            $config->set('URI.MakeAbsolute', false);

            // Disable external resources by default (prevent SSRF)
            $config->set('URI.DisableExternal', false);
            $config->set('URI.DisableExternalResources', true);

            // Add rel="noopener noreferrer" to external links
            $config->set('HTML.TargetBlank', true);
            $config->set('HTML.Nofollow', false);

            self::$purifier = new HTMLPurifier($config);
        }

        return self::$purifier;
    }

    /**
     * Sanitize HTML content to prevent XSS attacks
     *
     * @param string|null $html The HTML content to sanitize
     * @return string The sanitized HTML content
     */
    public static function sanitize(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        // Create cache directory if it doesn't exist
        $cacheDir = storage_path('app/htmlpurifier');
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        return self::getPurifier()->purify($html);
    }

    /**
     * Sanitize and render Markdown content
     * Currently just sanitizes HTML - can be extended with markdown parser if needed
     *
     * @param string|null $content The content to sanitize and render
     * @return string The sanitized and rendered content
     */
    public static function render(?string $content): string
    {
        if (empty($content)) {
            return '';
        }

        // For now, just sanitize the content as HTML
        // In the future, this could be extended to parse markdown to HTML first
        return self::sanitize($content);
    }

    /**
     * Sanitize content and convert newlines to <br> tags
     *
     * @param string|null $content The content to sanitize
     * @return string The sanitized content with line breaks
     */
    public static function nl2br(?string $content): string
    {
        if (empty($content)) {
            return '';
        }

        $sanitized = self::sanitize($content);
        return nl2br($sanitized, false);
    }

    /**
     * Strip all HTML tags and return plain text
     * Useful for displaying in attributes or when HTML is not allowed
     *
     * @param string|null $content The content to strip
     * @return string Plain text content
     */
    public static function stripTags(?string $content): string
    {
        if (empty($content)) {
            return '';
        }

        // First sanitize to ensure no malicious content
        $sanitized = self::sanitize($content);

        // Then strip all HTML tags
        return strip_tags($sanitized);
    }

    /**
     * Truncate HTML content safely
     *
     * @param string|null $content The content to truncate
     * @param int $length Maximum length
     * @param string $ending Ending string (e.g., '...')
     * @return string Truncated and sanitized content
     */
    public static function truncate(?string $content, int $length = 100, string $ending = '...'): string
    {
        if (empty($content)) {
            return '';
        }

        // First sanitize the content
        $sanitized = self::sanitize($content);

        // Strip tags for truncation
        $plain = strip_tags($sanitized);

        if (mb_strlen($plain) <= $length) {
            return $sanitized;
        }

        // Truncate and add ending
        $truncated = mb_substr($plain, 0, $length);
        return htmlspecialchars($truncated . $ending, ENT_QUOTES, 'UTF-8');
    }
}
