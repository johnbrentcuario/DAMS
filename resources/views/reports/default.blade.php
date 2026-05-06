<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; margin: 20px; }
        h1 { font-size: 16px; margin-bottom: 2px; color: #1e3a5f; }
        p.meta { font-size: 10px; color: #888; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1e40af; color: white; padding: 7px 8px; text-align: left; font-size: 10px; }
        td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
        tr:nth-child(even) td { background: #f9fafb; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p class="meta">Generated on {{ now()->format('F d, Y h:i A') }}</p>
    <table>
        <thead>
            <tr>
                @foreach($headings as $heading)
                    <th>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    @foreach($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headings) }}" style="text-align:center; color:#999; padding:20px;">
                        No data found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
