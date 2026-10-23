<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeDeployment;
use App\Models\User;
use App\Support\Employee\EmployeeDocumentCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class EmployeeDocumentVaultTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_follows_the_document_vault_blocks(): void
    {
        $catalog = app(EmployeeDocumentCatalog::class);
        $blocks = collect($catalog->blocks())->keyBy('key');
        $documents = $blocks->flatMap(fn (array $block): array => $block['documents'])->keyBy('key');

        $this->assertSame(['identity', 'academic', 'work_medical', 'company'], $blocks->keys()->all());
        $this->assertSame(['ktp', 'family_card', 'npwp', 'formal_photo', 'bank_book', 'last_diploma', 'transcript'], $catalog->requiredEmployeeDocumentKeys());
        $this->assertSame(5, $documents['mcu']['max_size_mb']);
        $this->assertSame(2, $documents['ktp']['max_size_mb']);
        $this->assertSame(['pdf', 'jpg', 'jpeg', 'png'], $documents['ktp']['accepted_extensions']);
        $this->assertSame(['jpg', 'jpeg', 'png'], $documents['formal_photo']['accepted_extensions']);
        $this->assertTrue($blocks['company']['is_hr_only']);
        $this->assertSame(['hr'], collect($blocks['company']['documents'])->pluck('uploaded_by')->unique()->values()->all());
        $this->assertFalse($documents['certificates']['is_required']);
    }

    public function test_employee_vault_lets_staff_upload_their_documents_and_hides_company_documents(): void
    {
        $html = Blade::render("@include('employee_data.documents.vault', ['documentVaultMode' => 'employee'])");

        $this->assertSame(11, substr_count($html, 'js-document-dropzone'));
        $this->assertSame(11, substr_count($html, 'js-document-card'));
        $this->assertStringContainsString('3. Dokumen Riwayat Pekerjaan &amp; Medis', $html);
        $this->assertStringNotContainsString('Dokumen Perusahaan', $html);
        $this->assertStringNotContainsString('Khusus HR', $html);
        $this->assertStringNotContainsString('Scan Perjanjian Kerja (PKWT/PKWTT)', $html);
        $this->assertStringNotContainsString('Dari HR', $html);
        $this->assertStringContainsString('0/7 Dokumen Wajib Terunggah', $html);
        $this->assertStringContainsString('<span class="badge badge-sm employee-document-required-badge">Wajib</span>', $html);
        $this->assertStringContainsString('employee-document-required-dot', $html);
        $this->assertStringContainsString('employee-document-optional-dot', $html);
        $this->assertStringNotContainsString('badge-danger', $html);
        $this->assertStringContainsString('<span class="badge badge-sm light badge-primary">Opsional</span>', $html);
        $this->assertStringContainsString('data-max-size-mb="5"', $html);
        $this->assertStringContainsString('JPG, PNG · Maks 2 MB', $html);
        $this->assertStringNotContainsString('Belum tersedia', $html);
        $this->assertStringNotContainsString('<i class="fa fa-download me-1"></i>Download', $html);
        $this->assertStringNotContainsString('role="progressbar"', $html);
    }

    public function test_hr_vault_can_upload_every_document_including_company_documents(): void
    {
        $html = Blade::render("@include('employee_data.documents.vault', ['documentVaultMode' => 'hr', 'employeeDocumentFiles' => \$files])", [
            'files' => [
                'ktp' => [['name' => 'KTP Dimas', 'url' => 'https://example.test/ktp.pdf', 'size' => '1.2 MB', 'icon' => 'assets/images/files/pdf.avif', 'is_image' => false]],
            ],
        ]);

        $this->assertSame(14, substr_count($html, 'js-document-dropzone'));
        $this->assertStringContainsString('1/7 Dokumen Wajib Terunggah', $html);
        $this->assertStringContainsString('KTP Dimas', $html);
        $this->assertStringContainsString('href="https://example.test/ktp.pdf"', $html);
        $this->assertStringNotContainsString('Belum tersedia', $html);
    }

    public function test_view_vault_only_shows_documents_for_preview_and_download(): void
    {
        $html = Blade::render("@include('employee_data.documents.vault', ['documentVaultMode' => 'view'])");

        $this->assertSame(0, substr_count($html, 'js-document-dropzone'));
        $this->assertSame(14, substr_count($html, '<i class="fa fa-eye me-1"></i>Preview'));
        $this->assertSame(14, substr_count($html, 'Belum diunggah'));
        $this->assertStringNotContainsString('Belum tersedia', $html);
    }

    public function test_profile_page_has_a_documents_tab_with_the_employee_vault(): void
    {
        $user = User::query()->create([
            'email' => 'staff.documents@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);
        $employee = Employee::query()->create(['user_id' => $user->id, 'employee_code' => 'EMP-DOC', 'status' => 'Active']);
        EmployeeDeployment::query()->create([
            'employee_id' => $employee->id,
            'current_company_id' => Company::query()->create(['name' => 'RNB'])->id,
            'status' => 'Active',
        ]);

        $response = $this->actingAs($user)->get(route('profile'));

        $response->assertOk();
        $response->assertSeeInOrder([
            'id="employee-documents-tab"', 'Dokumen', '<span class="badge profile-tab-alert-badge ms-1">!</span>', '</button>',
            'id="employee-documents-pane"',
            'Dokumen Digital',
            'Dokumen Identitas &amp; Legalitas Pribadi',
            'Dokumen Akademik &amp; Kompetensi',
            'Dokumen Riwayat Pekerjaan &amp; Medis',
        ], false);
        $response->assertDontSee('Dokumen Perusahaan');
        $response->assertDontSee('Khusus HR');
        $response->assertSee('data-upload-url="'.route('profile.employee-documents.store').'"', false);
        $response->assertSee('function renderFiles', false);
        $response->assertSee('.profile-tab-alert-badge {', false);
        $response->assertSee('background-color: #dc3545;', false);
        $response->assertSee('.employee-document-required-badge {', false);
        $response->assertSee('background-color: rgba(220, 53, 69, 0.12);', false);
        $response->assertDontSee('<span class="badge bg-danger ms-1">!</span>', false);
    }
}
