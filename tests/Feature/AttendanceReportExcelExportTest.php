<?php

namespace Tests\Feature;

use App\Http\Controllers\ReportController;
use App\Models\Attendance;
use App\Models\AttendanceException;
use Illuminate\Support\Facades\File;
use ReflectionMethod;
use Tests\TestCase;
use ZipArchive;

class AttendanceReportExcelExportTest extends TestCase
{
    public function test_attendance_report_table_uses_note_and_attachment_columns(): void
    {
        $reportView = File::get(resource_path('views/attendance/reports/index.blade.php'));

        $this->assertStringContainsString('Export Excel', $reportView);
        $this->assertStringNotContainsString('<th class="mw-100">Variance</th>', $reportView);
        $this->assertStringContainsString('<th class="mw-150">Note</th>', $reportView);
        $this->assertStringContainsString('<th class="mw-150">Attachment</th>', $reportView);
        $this->assertSame(1, substr_count($reportView, '<th class="mw-150">Note</th>'));
        $this->assertStringNotContainsString("{ data: 'variance', defaultContent: '-' }", $reportView);
        $this->assertStringContainsString("{ data: 'note', defaultContent: '-' }", $reportView);
        $this->assertStringContainsString("{ data: 'attachment', defaultContent: '-' }", $reportView);
        $this->assertStringContainsString('class="attendance-attachment-link">View Attachment</a>', $reportView);
        $this->assertStringContainsString('function escapeHtml(value)', $reportView);
    }

    public function test_attendance_report_export_uses_xlsx_instead_of_pdf_or_html_xls(): void
    {
        $reportController = File::get(app_path('Http/Controllers/ReportController.php'));

        $this->assertStringNotContainsString('Spatie\\LaravelPdf', $reportController);
        $this->assertStringNotContainsString("Pdf::view('attendance.reports.pdf'", $reportController);
        $this->assertStringNotContainsString("response()->view('attendance.reports.excel'", $reportController);
        $this->assertStringNotContainsString('application/vnd.ms-excel; charset=UTF-8', $reportController);
        $this->assertStringNotContainsString(".'.xls';", $reportController);
        $this->assertFileDoesNotExist(resource_path('views/attendance/reports/pdf.blade.php'));
        $this->assertFileDoesNotExist(resource_path('views/attendance/reports/excel.blade.php'));
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $reportController);
        $this->assertStringContainsString(".'.xlsx';", $reportController);
        $this->assertStringContainsString('buildAttendanceReportXlsx($reportRows, $titleLabel)', $reportController);
        $this->assertStringContainsString('$this->buildAttendanceReportSheetXml($reportRows, $titleLabel)', $reportController);
        $this->assertStringContainsString('ZipArchive::OVERWRITE', $reportController);
        $this->assertStringContainsString('Attendance Report', $reportController);
        $this->assertStringContainsString('Working Hours', $reportController);
        $this->assertStringContainsString('View Attachment', $reportController);
        $this->assertStringNotContainsString('SIAP - HRIS', $reportController);
    }

    public function test_attendance_report_export_title_uses_company_and_staff_names(): void
    {
        $reportController = File::get(app_path('Http/Controllers/ReportController.php'));

        $this->assertStringContainsString('private function resolveReportTitleLabel(Collection $reportRows): string', $reportController);
        $this->assertStringContainsString("->pluck('company_name')", $reportController);
        $this->assertStringContainsString("->pluck('staff_name')", $reportController);
        $this->assertStringContainsString("return \$companyLabel.' - '.\$staffLabel;", $reportController);
        $this->assertStringContainsString('$this->xlsxInlineStringCell(\'A1\', $titleLabel, 1)', $reportController);
        $this->assertStringContainsString('$fileNameSlug = Str::slug($titleLabel);', $reportController);
    }

    public function test_attendance_report_xlsx_contains_dynamic_title_and_attachment_hyperlink(): void
    {
        $controller = app(ReportController::class);
        $buildAttendanceReportXlsx = new ReflectionMethod(ReportController::class, 'buildAttendanceReportXlsx');
        $buildAttendanceReportXlsx->setAccessible(true);

        $xlsxContent = $buildAttendanceReportXlsx->invoke($controller, collect([
            [
                'attendance_date' => '01 May 2026',
                'check_in' => '08:00',
                'check_out' => '17:00',
                'note' => 'On Time',
                'work_hours' => '8 hours',
                'attachment' => 'http://localhost/storage/leave-request-attachments/file.pdf',
            ],
        ]), 'PT Andalan - Staff One');
        $temporaryPath = tempnam(sys_get_temp_dir(), 'attendance-report-test-');
        $this->assertIsString($temporaryPath);

        file_put_contents($temporaryPath, $xlsxContent);

        $zipArchive = new ZipArchive;
        $this->assertTrue($zipArchive->open($temporaryPath) === true);
        $this->assertNotFalse($zipArchive->locateName('[Content_Types].xml'));
        $this->assertNotFalse($zipArchive->locateName('xl/workbook.xml'));
        $this->assertNotFalse($zipArchive->locateName('xl/worksheets/sheet1.xml'));
        $this->assertNotFalse($zipArchive->locateName('xl/worksheets/_rels/sheet1.xml.rels'));

        $sheetXml = $zipArchive->getFromName('xl/worksheets/sheet1.xml');
        $relationshipXml = $zipArchive->getFromName('xl/worksheets/_rels/sheet1.xml.rels');
        $zipArchive->close();
        @unlink($temporaryPath);

        $this->assertIsString($sheetXml);
        $this->assertIsString($relationshipXml);
        $this->assertStringContainsString('PT Andalan - Staff One', $sheetXml);
        $this->assertStringContainsString('<mergeCell ref="A1:F1"/>', $sheetXml);
        $this->assertStringContainsString('Working Hours', $sheetXml);
        $this->assertStringContainsString('View Attachment', $sheetXml);
        $this->assertStringContainsString('http://localhost/storage/leave-request-attachments/file.pdf', $relationshipXml);
    }

    public function test_attendance_report_controller_resolves_note_and_attachment_values(): void
    {
        $reportController = File::get(app_path('Http/Controllers/ReportController.php'));

        $this->assertStringContainsString("'note' => \$noteLabel,", $reportController);
        $this->assertStringContainsString("'attachment' => \$attachmentUrl,", $reportController);
        $this->assertStringContainsString("'note' => \$isNationalHoliday ? 'Libur Nasional' : 'Cuti Bersama',", $reportController);
        $this->assertStringContainsString("'note' => 'Weekend / Day Off',", $reportController);
        $this->assertStringNotContainsString("'variance' =>", $reportController);
        $this->assertStringNotContainsString("'notes' =>", $reportController);
        $this->assertStringNotContainsString('formatVariance(', $reportController);
        $this->assertStringContainsString("->get(['attendance_id', 'exception_date', 'type', 'note', 'from_time', 'to_time'])", $reportController);
        $this->assertStringContainsString("'late_arrival' => 'Izin Masuk Terlambat'", $reportController);
        $this->assertStringContainsString("'early_departure' => 'Izin Pulang Lebih Awal'", $reportController);
        $this->assertStringContainsString("return 'Late '.\$lateMinutes.' Minutes';", $reportController);
        $this->assertStringContainsString("return 'On Time';", $reportController);
        $this->assertStringContainsString("return 'Cuti Tahunan';", $reportController);
        $this->assertStringContainsString("return Storage::disk('public')->url(\$attachmentPath);", $reportController);
    }

    public function test_attendance_report_note_labels_cover_late_on_time_and_exceptions(): void
    {
        $controller = app(ReportController::class);
        $resolveAttendanceNoteLabel = new ReflectionMethod(ReportController::class, 'resolveAttendanceNoteLabel');
        $resolveAttendanceNoteLabel->setAccessible(true);

        $this->assertSame(
            'Late 10 Minutes',
            $resolveAttendanceNoteLabel->invoke(
                $controller,
                new Attendance(['late_minutes' => 10]),
                null,
                null
            )
        );

        $this->assertSame(
            'On Time',
            $resolveAttendanceNoteLabel->invoke(
                $controller,
                new Attendance(['clock_in' => '2026-06-10 08:00:00', 'late_minutes' => 0]),
                null,
                null
            )
        );

        $this->assertSame(
            'Izin Masuk Terlambat 3 Jam',
            $resolveAttendanceNoteLabel->invoke(
                $controller,
                new Attendance(['late_minutes' => 0]),
                new AttendanceException([
                    'exception_date' => '2026-06-10',
                    'type' => 'late_arrival',
                    'from_time' => '08:00:00',
                    'to_time' => '11:00:00',
                ]),
                null
            )
        );

        $this->assertSame(
            'Izin Pulang Lebih Awal 2 Jam 30 Menit',
            $resolveAttendanceNoteLabel->invoke(
                $controller,
                new Attendance(['late_minutes' => 0]),
                new AttendanceException([
                    'exception_date' => '2026-06-10',
                    'type' => 'early_departure',
                    'from_time' => '14:30:00',
                    'to_time' => '17:00:00',
                ]),
                null
            )
        );
    }
}
