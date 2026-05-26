<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Attendance Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #0f172a;
            margin: 24px;
        }

        h1 {
            margin: 0 0 8px;
            font-size: 18px;
        }

        .meta {
            margin: 0 0 16px;
            color: #475569;
        }

        .meta div {
            margin: 2px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            vertical-align: top;
            word-wrap: break-word;
        }

        th {
            background: #f1f5f9;
            text-align: left;
        }
    </style>
</head>
<body>
    <h1>Attendance Report</h1>
    <div class="meta">
        <div><strong>Periode:</strong> {{ $periodLabel ?? '-' }}</div>
        <div><strong>Staff :</strong> {{ $userLabel ?? '-' }}</div>
        <div><strong>Generated at:</strong> {{ $generatedAt ?? '-' }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 18%;">Date</th>
                <th style="width: 18%;">Clock In</th>
                <th style="width: 12%;">Clock Out</th>
                <th style="width: 20%;">Variance</th>
                <th style="width: 12%;">Work Hours</th>
                <th style="width: 20%;">Notes</th>
            </tr>
        </thead>
        <tbody>
            @forelse (($rows ?? collect()) as $row)
                <tr>
                    <td>{{ $row['attendance_date'] ?? '-' }}</td>
                    <td>{{ $row['check_in'] ?? '-' }}</td>
                    <td>{{ $row['check_out'] ?? '-' }}</td>
                    <td>{{ $row['variance'] ?? '-' }}</td>
                    <td>{{ $row['work_hours'] ?? '-' }}</td>
                    <td>{{ (isset($row['notes']) && trim((string) $row['notes']) !== '') ? $row['notes'] : 'No Notes' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Tidak ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
