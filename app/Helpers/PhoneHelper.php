<?php

namespace App\Helpers;

class PhoneHelper
{
    /**
     * Format a phone number to xxx-xxx-xxxx format
     * Handles numbers with or without names appended
     *
     * @param string $contactInfo Raw contact info (e.g., "5196306098Henry" or "5196306098")
     * @return array ['formatted' => 'xxx-xxx-xxxx', 'number' => 'xxxxxxxxxx', 'name' => 'Name']
     */
    public static function format($contactInfo)
    {
        if (empty($contactInfo)) {
            return [
                'formatted' => '',
                'number' => '',
                'name' => '',
                'display' => ''
            ];
        }

        // Extract all digits from the string (strips dashes, spaces, parentheses, etc.)
        $allDigits = preg_replace('/\D/', '', $contactInfo);

        if (empty($allDigits)) {
            // If no digits found, return as-is
            return [
                'formatted' => $contactInfo,
                'number' => $contactInfo,
                'name' => '',
                'display' => $contactInfo
            ];
        }

        // Extract any text that comes AFTER the phone number
        // Pattern: phone number chars (digits, dashes, spaces, parens) followed by text
        $name = '';
        if (preg_match('/^[\d\s\-\(\)\.]+(.*)$/', $contactInfo, $matches)) {
            $name = trim($matches[1]);
        }

        // For 10-digit numbers, use the full number
        // For longer numbers (like "15196306098"), use the last 10 digits
        // This handles cases where country code is included
        $number = $allDigits;
        if (strlen($allDigits) > 10) {
            $number = substr($allDigits, -10);
        }

        // Format 10-digit North American numbers as xxx-xxx-xxxx
        if (strlen($number) === 10) {
            $formatted = sprintf(
                '%s-%s-%s',
                substr($number, 0, 3),
                substr($number, 3, 3),
                substr($number, 6, 4)
            );
        } else {
            // For non-10-digit numbers, just return the number
            $formatted = $number;
        }

        // Create display string (formatted number + name if present)
        $display = $name ? $formatted . ' ' . $name : $formatted;

        return [
            'formatted' => $formatted,
            'number' => $number,
            'name' => $name,
            'display' => $display
        ];
    }

    /**
     * Format contact info and return just the display string
     *
     * @param string $contactInfo Raw contact info
     * @return string Formatted display string
     */
    public static function formatDisplay($contactInfo)
    {
        return self::format($contactInfo)['display'];
    }
}
