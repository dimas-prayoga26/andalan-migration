<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmployeeDeployment;
use App\Models\EmployeeDocument;
use App\Models\EmployeeProfile;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use App\Services\Employee\EmployeeDocumentVaultService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EmployeeDocumentVaultUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(EmployeeDocumentVaultService::DISK);
    }

    public function test_staff_uploads_a_document_to_the_private_disk_and_a_reupload_replaces_it(): void
    {
        [$staffUser, $staffEmployee] = $this->createStaff('vault.staff');

        $firstResponse = $this->actingAs($staffUser)->postJson(route('profile.employee-documents.store'), [
            'document_type' => 'ktp',
            'file' => UploadedFile::fake()->create('ktp-lama.pdf', 300, 'application/pdf'),
        ]);

        $firstResponse->assertOk()
            ->assertJsonPath('message', 'Dokumen berhasil diunggah.')
            ->assertJsonPath('document_type', 'ktp')
            ->assertJsonPath('files.0.name', 'ktp-lama.pdf')
            ->assertJsonPath('required_uploaded_count', 1)
            ->assertJsonPath('required_total', 7)
            ->assertJsonPath('has_missing_required_documents', true);

        $firstDocument = EmployeeDocument::query()->where('employee_id', $staffEmployee->id)->firstOrFail();

        Storage::disk(EmployeeDocumentVaultService::DISK)->assertExists($firstDocument->file_path);
        $this->assertStringStartsWith('employee-documents/'.$staffEmployee->id.'/ktp/', $firstDocument->file_path);
        $this->assertSame($staffUser->id, $firstDocument->uploaded_by);
        $this->assertSame(
            route('profile.employee-documents.show', ['employeeDocument' => $firstDocument]),
            $firstResponse->json('files.0.url')
        );

        $this->actingAs($staffUser)->postJson(route('profile.employee-documents.store'), [
            'document_type' => 'ktp',
            'file' => UploadedFile::fake()->image('ktp-baru.jpg'),
        ])->assertOk()->assertJsonCount(1, 'files')->assertJsonPath('files.0.name', 'ktp-baru.jpg');

        $this->assertSoftDeleted($firstDocument);
        $this->assertSame(1, EmployeeDocument::query()->where('employee_id', $staffEmployee->id)->count());
    }

    public function test_certificates_keep_every_uploaded_file(): void
    {
        [$staffUser] = $this->createStaff('certificate.staff');

        foreach (['k3-umum.pdf', 'pop.pdf'] as $fileName) {
            $this->actingAs($staffUser)->postJson(route('profile.employee-documents.store'), [
                'document_type' => 'certificates',
                'file' => UploadedFile::fake()->create($fileName, 100, 'application/pdf'),
            ])->assertOk();
        }

        $this->assertSame(2, EmployeeDocument::query()->where('document_type', 'certificates')->count());
    }

    public function test_upload_rejects_hr_documents_wrong_formats_and_large_files_for_staff(): void
    {
        [$staffUser] = $this->createStaff('rejected.staff');

        $this->actingAs($staffUser)
            ->postJson(route('profile.employee-documents.store'), [
                'document_type' => 'employment_agreement',
                'file' => UploadedFile::fake()->create('pkwt.pdf', 100, 'application/pdf'),
            ])
            ->assertUnprocessable()
            ->assertJsonPath('errors.document_type.0', 'Dokumen ini hanya dapat diunggah oleh HR.');
        $this->actingAs($staffUser)
            ->postJson(route('profile.employee-documents.store'), [
                'document_type' => 'formal_photo',
                'file' => UploadedFile::fake()->create('foto.pdf', 100, 'application/pdf'),
            ])
            ->assertUnprocessable()
            ->assertJsonPath('errors.file.0', 'Format file tidak didukung. Gunakan JPG, JPEG, PNG.');
        $this->actingAs($staffUser)
            ->postJson(route('profile.employee-documents.store'), [
                'document_type' => 'npwp',
                'file' => UploadedFile::fake()->create('npwp.pdf', 3000, 'application/pdf'),
            ])
            ->assertUnprocessable()
            ->assertJsonPath('errors.file.0', 'Ukuran file maksimal 2 MB.');

        $this->assertSame(0, EmployeeDocument::query()->count());
    }

    public function test_staff_previews_downloads_and_deletes_only_their_own_documents(): void
    {
        [$staffUser, $staffEmployee] = $this->createStaff('owner.staff');
        [$otherUser, $otherEmployee] = $this->createStaff('other.staff');
        $ownDocument = $this->storeDocument($staffEmployee, 'npwp', 'npwp.pdf');
        $hrDocument = $this->storeDocument($staffEmployee, 'nda', 'nda.pdf');
        $otherDocument = $this->storeDocument($otherEmployee, 'ktp', 'ktp-orang-lain.pdf');

        $this->actingAs($staffUser)
            ->get(route('profile.employee-documents.show', ['employeeDocument' => $ownDocument]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString(
            'attachment',
            (string) $this->actingAs($staffUser)->get(route('profile.employee-documents.show', ['employeeDocument' => $ownDocument, 'download' => 1]))->headers->get('content-disposition')
        );
        $this->actingAs($staffUser)
            ->get(route('profile.employee-documents.show', ['employeeDocument' => $otherDocument]))
            ->assertNotFound();
        $this->actingAs($staffUser)
            ->deleteJson(route('profile.employee-documents.destroy', ['employeeDocument' => $hrDocument]))
            ->assertForbidden();
        $this->actingAs($otherUser)
            ->deleteJson(route('profile.employee-documents.destroy', ['employeeDocument' => $ownDocument]))
            ->assertNotFound();

        $this->actingAs($staffUser)
            ->deleteJson(route('profile.employee-documents.destroy', ['employeeDocument' => $ownDocument]))
            ->assertOk()
            ->assertJsonPath('message', 'Dokumen berhasil dihapus.')
            ->assertJsonPath('files', []);

        $this->assertSoftDeleted($ownDocument);
        $this->assertNotSoftDeleted($hrDocument);
    }

    public function test_hr_uploads_company_documents_that_are_hidden_from_the_staff_profile(): void
    {
        $manager = $this->createAuthorizationManager();
        [$staffUser, $staffEmployee] = $this->createStaff('hr.vault.staff');

        $uploadResponse = $this->actingAs($manager)->postJson(route('authorization.employee-documents.store', ['employee' => $staffEmployee]), [
            'document_type' => 'employment_agreement',
            'file' => UploadedFile::fake()->create('pkwt-2026.pdf', 200, 'application/pdf'),
        ]);

        $uploadResponse->assertOk()->assertJsonPath('files.0.name', 'pkwt-2026.pdf');

        $hrDocument = EmployeeDocument::query()->where('employee_id', $staffEmployee->id)->firstOrFail();

        $this->assertSame(
            route('authorization.employee-documents.destroy', ['employee' => $staffEmployee, 'employeeDocument' => $hrDocument]),
            $uploadResponse->json('files.0.delete_url')
        );
        $this->actingAs($manager)
            ->get(route('authorization.employee-documents.show', ['employee' => $staffEmployee, 'employeeDocument' => $hrDocument]))
            ->assertOk();

        $this->actingAs($staffUser)->get(route('profile'))
            ->assertOk()
            ->assertDontSee('Dokumen Perusahaan')
            ->assertDontSee('pkwt-2026.pdf')
            ->assertDontSee(route('profile.employee-documents.show', ['employeeDocument' => $hrDocument]), false)
            ->assertDontSee('data-delete-url="'.route('profile.employee-documents.destroy', ['employeeDocument' => $hrDocument]).'"', false);

        $this->actingAs($manager)->get(route('authorization.edit', ['employee' => $staffEmployee]))
            ->assertOk()
            ->assertSee('Dokumen Perusahaan')
            ->assertSee('pkwt-2026.pdf');

        $this->actingAs($manager)
            ->deleteJson(route('authorization.employee-documents.destroy', ['employee' => $staffEmployee, 'employeeDocument' => $hrDocument]))
            ->assertOk();

        $this->assertSoftDeleted($hrDocument);
    }

    public function test_documents_of_another_employee_cannot_be_reached_through_an_employee_url(): void
    {
        $manager = $this->createAuthorizationManager();
        [, $staffEmployee] = $this->createStaff('first.staff');
        [, $otherEmployee] = $this->createStaff('second.staff');
        $otherDocument = $this->storeDocument($otherEmployee, 'ktp', 'ktp.pdf');

        $this->actingAs($manager)
            ->get(route('authorization.employee-documents.show', ['employee' => $staffEmployee, 'employeeDocument' => $otherDocument]))
            ->assertNotFound();
        $this->actingAs($manager)
            ->deleteJson(route('authorization.employee-documents.destroy', ['employee' => $staffEmployee, 'employeeDocument' => $otherDocument]))
            ->assertNotFound();
    }

    public function test_profile_badge_disappears_when_all_required_documents_are_uploaded(): void
    {
        [$staffUser, $staffEmployee] = $this->createStaff('complete.vault.staff');

        foreach (['ktp', 'family_card', 'npwp', 'bank_book', 'last_diploma', 'transcript'] as $documentType) {
            $this->storeDocument($staffEmployee, $documentType, $documentType.'.pdf');
        }

        $this->actingAs($staffUser)->postJson(route('profile.employee-documents.store'), [
            'document_type' => 'formal_photo',
            'file' => UploadedFile::fake()->image('foto.png'),
        ])->assertOk()->assertJsonPath('has_missing_required_documents', false);

        $this->actingAs($staffUser)->get(route('profile'))
            ->assertOk()
            ->assertSee('7/7 Dokumen Wajib Terunggah')
            ->assertDontSee('Dokumen <span class="badge profile-tab-alert-badge ms-1">!</span>', false);
    }

    private function storeDocument(Employee $employee, string $documentType, string $fileName): EmployeeDocument
    {
        $filePath = 'employee-documents/'.$employee->id.'/'.$documentType.'/'.$fileName;

        Storage::disk(EmployeeDocumentVaultService::DISK)->put($filePath, '%PDF-1.4 test');

        return EmployeeDocument::factory()->for($employee)->create([
            'document_type' => $documentType,
            'file_path' => $filePath,
            'original_name' => $fileName,
            'mime_type' => 'application/pdf',
        ]);
    }

    /**
     * @return array{0: User, 1: Employee}
     */
    private function createStaff(string $emailName): array
    {
        $staffUser = User::query()->create([
            'email' => $emailName.'@example.test',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $staffEmployee = Employee::query()->create([
            'user_id' => $staffUser->id,
            'employee_code' => strtoupper(str_replace('.', '-', $emailName)),
            'status' => 'Active',
        ]);
        EmployeeDeployment::query()->create(['employee_id' => $staffEmployee->id, 'status' => 'Active']);

        return [$staffUser, $staffEmployee];
    }

    private function createAuthorizationManager(): User
    {
        $manager = User::query()->create([
            'email' => 'superadmin@example.test',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $manager->assignRole(Role::query()->firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']));
        $managerEmployee = Employee::query()->create([
            'user_id' => $manager->id,
            'employee_code' => 'EMP-ADMIN',
            'status' => 'Active',
        ]);
        EmployeeProfile::query()->create(['employee_id' => $managerEmployee->id, 'name' => 'Super Administrator']);
        EmployeeDeployment::query()->create([
            'employee_id' => $managerEmployee->id,
            'current_position_id' => Position::query()->create(['name' => 'Super Administrator'])->id,
            'status' => 'Active',
        ]);

        return $manager;
    }
}
