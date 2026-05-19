<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        /* ───────────────── GENERAL RESET ───────────────── */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 10px;
            color: #1f2937;
            background: #ffffff;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td, th {
            padding: 0;
            vertical-align: middle;
        }

        /* ───────────────── HEADER ───────────────── */
        .header {
            margin-bottom: 20px;
            border-bottom: 3px solid #1d4ed8;
            padding-bottom: 12px;
        }
        .logo-col {
            width: 55px;
        }
        .logo {
            width: 48px;
            height: 48px;
            display: block;
        }
        .agency-name {
            font-size: 15px;
            font-weight: bold;
            color: #0f172a;
            line-height: 1.2;
        }
        .agency-sub {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
            letter-spacing: 0.5px;
        }
        .right-section {
            text-align: right;
        }
        .report-title {
            font-size: 16px;
            font-weight: bold;
            color: #1d4ed8;
        }
        .report-date {
            font-size: 9px;
            color: #94a3b8;
            margin-top: 4px;
        }

        /* ───────────────── SUMMARY ───────────────── */
        .summary-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-left: 4px solid #2563eb;
            border-radius: 4px;
            padding: 10px 14px;
            margin-bottom: 20px;
        }
        .summary-label {
            font-weight: bold;
            color: #1e40af;
        }
        .record-badge {
            background: #1d4ed8;
            color: #ffffff;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
        }

        /* ───────────────── DATA TABLE ───────────────── */
        .table-container {
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
        }
        .data-table th {
            background: #1d4ed8;
            color: #ffffff;
            padding: 8px 10px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .data-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 9px;
            color: #374151;
            vertical-align: top;
            line-height: 1.4;
        }
        .data-table tr:nth-child(even) td {
            background: #f8fafc;
        }
        .data-table tr:last-child td {
            border-bottom: none;
        }
        .empty-row td {
            text-align: center;
            padding: 30px;
            color: #94a3b8;
            font-style: italic;
        }

        /* ───────────────── FOOTER ───────────────── */
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            font-size: 8.5px;
            color: #94a3b8;
        }
        .confidential {
            background: #fef3c7;
            border: 1px solid #fcd34d;
            border-radius: 3px;
            color: #92400e;
            padding: 2px 6px;
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        /* ───────────────── WATERMARK ───────────────── */
        .watermark {
            position: fixed;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            opacity: 0.03;
            font-size: 55px;
            font-weight: bold;
            color: #1e3a8a;
            z-index: -1;
            text-align: center;
            width: 100%;
        }

        @page {
            margin: 20px;
        }
    </style>
</head>
<body>

    <!-- Watermark Centered Perfectly -->
    <div class="watermark">
        Pangasinan Irrigation Management Office
    </div>

    <!-- Header -->
    <div class="header">
        <table>
            <tr>
                <td class="logo-col">
                    <img src="{{ public_path('favicon.ico') }}" class="logo" alt="Logo">
                </td>
                <td>
                    <div class="agency-name">Pangasinan Irrigation Management Office</div>
                    <div class="agency-sub">Digital Archives & Mapping System</div>
                </td>
                <td class="right-section">
                    <div class="report-title">{{ $title }}</div>
                    <div class="report-date">Generated on {{ now()->format('F d, Y h:i A') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Summary Box -->
    <div class="summary-box">
        <table>
            <tr>
                <td class="summary-label">
                    {{ $title }} — Export Summary
                </td>
                <td class="right-section">
                    <span class="record-badge">
                        {{ count($rows) }} {{ count($rows) === 1 ? 'Record' : 'Records' }}
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <!-- Data Table -->
    <div class="table-container">
        <table class="data-table">
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
                    <tr class="empty-row">
                        <td colspan="{{ count($headings) }}">
                            No data available for this report.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <table>
            <tr>
                <td>
                    Pangasinan Irrigation Management Office • Digital Archives & Mapping System
                </td>
                <td class="right-section">
                    <span class="confidential">CONFIDENTIAL</span>
                    &nbsp;&nbsp;|&nbsp;&nbsp;
                    {{ now()->format('Y') }} © All Rights Reserved
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
