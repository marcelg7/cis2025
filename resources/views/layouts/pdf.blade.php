<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hay CIS Contract #{{ $contract->id }}</title>
    <style>
        body { font-family: sans-serif; font-size: 9pt; margin: 0mm; padding: 0; color: #333; } /* Zero margins */
        .container { width: 100%; padding: 0; } /* No padding */
        table { width: 100%; border-collapse: collapse; }
        table td { vertical-align: top; }
        hr { margin: 0.1rem 0; border: 1px solid #ccc; }
        h2 { font-size: 12pt; font-weight: bold; margin: 0; }
        h3 { font-size: 10pt; font-weight: bold; margin: 0; }
        h4 { font-size: 9pt; font-weight: bold; margin: 0; }
        p { margin: 0; line-height: 1.0; }
        .text-xs { font-size: 7pt; }
        .text-sm { font-size: 8pt; }
        .font-semibold { font-weight: bold; }
        .font-medium { font-weight: 500; }
        .text-gray-900 { color: #000; }
        .text-gray-600 { color: #333; }
        img { max-height: 25px; width: auto; }
        a { color: #000; text-decoration: none; }
        * { box-sizing: border-box; page-break-inside: avoid; } /* Avoid breaking elements across pages */
        ul { margin: 0; padding-left: 0.5rem; }
        li { margin: 0; }
    </style>
</head>
<body>
    @yield('content')
</body>
</html>