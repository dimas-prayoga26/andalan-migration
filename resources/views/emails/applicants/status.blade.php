@php
    $statusValue = (int) ($applicant->applicantStatus?->value ?? 0);
    $statusName = $applicant->statusLabel();
    $applicantName = $applicant->full_name ?? 'Kandidat';
    $positionName = $applicant->jobVacancy?->name ?? 'Posisi yang dilamar';
    $submittedAt = $applicant->created_at;
    $contactEmail = $brand['email'];
    $logoSource = $brand['logo_url'];
    $primaryColor = $brand['primary_color'] ?? '#304767';
    $accentColor = $brand['accent_color'] ?? '#2563eb';
    $headerBackgroundColor = $brand['header_background_color'] ?? $primaryColor;
    $headerTextColor = $brand['header_text_color'] ?? '#ffffff';

    $content = match ($statusValue) {
        1 => [
            'title' => 'Update Status Lamaran',
            'intro' => 'Lamaran Anda saat ini berada di tahap '.$statusName.'.',
            'message' => 'Tim rekrutmen sedang meninjau kesesuaian awal profil Anda dengan kebutuhan posisi.',
            'badgeColor' => $accentColor,
        ],
        2 => [
            'title' => 'Update Status Lamaran',
            'intro' => 'Lamaran Anda saat ini berada di tahap '.$statusName.'.',
            'message' => 'Mohon mempersiapkan diri untuk proses penilaian teknis sesuai kebutuhan posisi yang Anda lamar.',
            'badgeColor' => $accentColor,
        ],
        3 => [
            'title' => 'Update Status Lamaran',
            'intro' => 'Lamaran Anda saat ini berada di tahap '.$statusName.'.',
            'message' => 'Anda sedang berada pada proses interview dengan user terkait posisi yang dilamar.',
            'badgeColor' => $accentColor,
        ],
        4 => [
            'title' => 'Update Status Lamaran',
            'intro' => 'Lamaran Anda saat ini berada di tahap '.$statusName.'.',
            'message' => 'Tim kami akan menghubungi Anda untuk informasi berikutnya terkait offering.',
            'badgeColor' => '#16a34a',
        ],
        5 => [
            'title' => 'Update Status Lamaran',
            'intro' => 'Status lamaran Anda saat ini adalah '.$statusName.'.',
            'message' => 'Terima kasih atas waktu dan ketertarikan Anda untuk bergabung bersama '.$brand['name'].'.',
            'badgeColor' => '#dc2626',
        ],
        default => [
            'title' => 'Update Status Lamaran',
            'intro' => 'Lamaran Anda saat ini berada di tahap '.$statusName.'.',
            'message' => 'Mohon menunggu proses review dari tim rekrutmen kami. Jika profil Anda sesuai dengan kebutuhan posisi, kami akan menghubungi Anda untuk tahap berikutnya.',
            'badgeColor' => $accentColor,
        ],
    };
@endphp

<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $content['title'] }}</title>
</head>

<body style="margin: 0; padding: 0; background: #eef2f7; font-family: Arial, Helvetica, sans-serif; color: #172033;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background: #eef2f7; padding: 28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 640px; background: #ffffff; border-collapse: collapse;">
                    <tr>
                        <td style="background: {{ $headerBackgroundColor }}; padding: 28px 32px; border-bottom: 4px solid {{ $primaryColor }};">
                            <img src="{{ $logoSource }}" alt="{{ $brand['name'] }}" width="132" style="display: block; max-width: 132px; height: auto;">
                            <p style="margin: 22px 0 6px; color: {{ $accentColor }}; font-size: 13px; line-height: 1.5; font-weight: 700;">Status Lamaran</p>
                            <h1 style="margin: 0; color: {{ $headerTextColor }}; font-size: 26px; line-height: 1.25; font-weight: 700;">{{ $content['title'] }}</h1>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 30px 32px 20px;">
                            <p style="margin: 0; font-size: 16px; line-height: 1.7;">Halo {{ $applicantName }},</p>
                            <p style="margin: 12px 0 0; font-size: 16px; line-height: 1.7;">{{ $content['intro'] }}</p>
                            <p style="margin: 12px 0 0; font-size: 15px; line-height: 1.7; color: #48566d;">{{ $content['message'] }}</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 0 32px 24px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border: 1px solid #dbe3ee; border-collapse: collapse;">
                                <tr>
                                    <td style="padding: 18px; border-bottom: 1px solid #dbe3ee; background: #f8fafc;">
                                        <p style="margin: 0; color: #64748b; font-size: 12px;">Status Saat Ini</p>
                                        <p style="margin: 8px 0 0; color: {{ $content['badgeColor'] }}; font-size: 20px; font-weight: 700;">{{ $statusName }}</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 16px 18px; border-bottom: 1px solid #dbe3ee;">
                                        <p style="margin: 0; color: #64748b; font-size: 12px;">Posisi Dilamar</p>
                                        <p style="margin: 6px 0 0; color: #172033; font-size: 15px; font-weight: 700;">{{ $positionName }}</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 16px 18px; border-bottom: 1px solid #dbe3ee;">
                                        <p style="margin: 0; color: #64748b; font-size: 12px;">Tanggal Submit</p>
                                        <p style="margin: 6px 0 0; color: #172033; font-size: 15px; font-weight: 700;">
                                            {{ $submittedAt ? \Illuminate\Support\Carbon::parse($submittedAt)->translatedFormat('d F Y, H:i') : '-' }}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 22px 32px; background: #f8fafc; border-top: 1px solid #dbe3ee;">
                            <p style="margin: 0; color: #48566d; font-size: 13px; line-height: 1.7;">
                                Jika ada pertanyaan, silakan hubungi kami melalui
                                <a href="mailto:{{ $contactEmail }}" style="color: {{ $accentColor }}; text-decoration: none;">{{ $contactEmail }}</a>.
                            </p>
                            <p style="margin: 16px 0 0; color: #94a3b8; font-size: 12px; line-height: 1.6;">
                                Email ini dikirim otomatis oleh sistem rekrutmen {{ $brand['name'] }}. Mohon tidak membalas langsung email ini.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
