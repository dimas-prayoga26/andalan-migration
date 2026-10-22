<?php

namespace App\Support\Employee;

use App\Models\CompanyAsset;
use App\Models\Employee;
use App\Models\EmployeeAddress;
use App\Models\EmployeeBankAccount;
use App\Models\EmployeeCertification;
use App\Models\EmployeeDeployment;
use App\Models\EmployeeEducation;
use App\Models\EmployeeFamily;
use App\Models\EmployeeIdentity;
use App\Models\EmployeeLicense;
use App\Models\EmployeePicAssignment;
use App\Models\EmployeeProfile;
use App\Models\EmployeeReference;
use App\Models\EmployeeWorkExperience;
use App\Models\User;
use App\Services\Employee\EmployeeDocumentVaultService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Lays out an employee's data for the Employee Details page, following the HRIS employee database details template.
 * Required items mirror EmployeeDataCompletionCalculator so empty required values can be highlighted.
 *
 * @phpstan-type DetailField array{label: string, value: ?string, is_required: bool}
 * @phpstan-type DetailTable array{title: string, is_required: bool, columns: list<string>, rows: list<list<string>>}
 * @phpstan-type DetailGroup array{title: string, fields: list<DetailField>, tables: list<DetailTable>}
 * @phpstan-type DetailDocument array{id: ?string, name: string, url: ?string, download_url: ?string, delete_url: ?string, size: ?string, icon: string, is_image: bool, uploaded_at: ?string}
 * @phpstan-type DetailSection array{key: string, title: string, groups: list<DetailGroup>, document_files?: array<string, list<DetailDocument>>}
 */
class EmployeeDataDetailPresenter
{
    public function __construct(private readonly EmployeeDocumentVaultService $employeeDocumentVaultService) {}

    /**
     * @return list<DetailSection>
     */
    public function forEmployee(Employee $employee): array
    {
        $employeeId = (string) $employee->id;
        $profile = EmployeeProfile::query()->where('employee_id', $employeeId)->first();
        $identity = EmployeeIdentity::query()->where('employee_id', $employeeId)->first();
        $user = $employee->user_id !== null
            ? User::query()->whereKey($employee->user_id)->first(['id', 'email', 'phone'])
            : null;
        $addressesByType = EmployeeAddress::query()
            ->where('employee_id', $employeeId)
            ->latest('created_at')
            ->get()
            ->groupBy(fn (EmployeeAddress $address): string => strtolower(trim((string) $address->type)) ?: EmployeeDataCompletionCalculator::DOMICILE_ADDRESS_TYPE)
            ->map(fn (Collection $addresses): ?EmployeeAddress => $addresses->first());
        $bankAccount = EmployeeBankAccount::query()
            ->where('employee_id', $employeeId)
            ->orderByDesc('is_primary')
            ->orderByDesc('created_at')
            ->first();
        $families = EmployeeFamily::query()
            ->where('employee_id', $employeeId)
            ->orderBy('created_at')
            ->get();
        $emergencyContacts = $families->filter(fn (EmployeeFamily $family): bool => $family->is_emergency_contact)->values();

        return [
            [
                'key' => 'personal',
                'title' => 'Personal',
                'groups' => [
                    $this->group('Personal Information', [
                        $this->field('Full Name', $profile?->name, true),
                        $this->field('Nickname', $profile?->nickname),
                        $this->field('Place & Date of Birth', $this->joinFilled([$profile?->place_of_birth, $this->dateWithAge($profile?->date_of_birth)], ', '), true),
                        $this->field('Gender', $profile?->gender, true),
                        $this->field('NIK KTP', $identity?->nik, true),
                        $this->field('Family Card Number (KK)', $identity?->kk, true),
                        $this->field('Mother\'s Maiden Name', $identity?->mother_name, true),
                        $this->field('Religion', $profile?->religion, true),
                        $this->field('Nationality', $profile?->nationality),
                        $this->field('Blood Type', $profile?->blood_type),
                        $this->field('Marital Status', $profile?->marital_status, true),
                        $this->field('Permanent Address', $this->addressLabel($addressesByType->get(EmployeeDataCompletionCalculator::KTP_ADDRESS_TYPE)), true),
                        $this->field('Current Residential Address', $this->addressLabel($addressesByType->get(EmployeeDataCompletionCalculator::DOMICILE_ADDRESS_TYPE)), true),
                        $this->field('Phone Number', $user?->phone, true),
                        $this->field('Email Address', $profile?->personal_email),
                        $this->field('LinkedIn', $profile?->linkedin_url),
                    ]),
                    $this->group('Physical & Medical Information', [
                        $this->field('Height', $this->withUnit($profile?->height, 'cm')),
                        $this->field('Weight', $this->withUnit($profile?->weight, 'kg')),
                        $this->field('Shirt Size', $profile?->shirt_size),
                        $this->field('Pants Size', $profile?->pants_size),
                        $this->field('Shoe Size', $profile?->shoe_size),
                        $this->field('Medical History', $profile?->medical_history),
                        $this->field('Allergies', $profile?->allergy_notes),
                    ]),
                ],
            ],
            [
                'key' => 'employment',
                'title' => 'Employment',
                'groups' => [
                    $this->group('Employment & Organizational Data', $this->employmentFields($employee, $user)),
                ],
            ],
            [
                'key' => 'financial',
                'title' => 'Financial',
                'groups' => [
                    $this->group('Financial & Tax Information (Payroll)', [
                        $this->field('Tax ID (NPWP)', $identity?->npwp),
                        $this->field('Tax Relief (PTKP Status)', $identity?->ptkp_status),
                        $this->field('Number of Dependents', (string) $families->filter(fn (object $family): bool => (bool) $family->is_dependents)->count()),
                        $this->field('Employment BPJS', $identity?->bpjs_ketenagakerjaan),
                        $this->field('Healthcare BPJS', $identity?->bpjs_kesehatan),
                    ]),
                    $this->group('Bank Account', [
                        $this->field('Bank Name', $bankAccount?->bank_name),
                        $this->field('Branch', $bankAccount?->branch),
                        $this->field('Account Number', $bankAccount?->account_number, true),
                        $this->field('Account Holder Name', $bankAccount?->account_holder_name),
                    ]),
                ],
            ],
            [
                'key' => 'family',
                'title' => 'Family',
                'groups' => [
                    $this->group('Family & Emergency Contacts', $emergencyContacts->isEmpty()
                        ? [$this->field('Emergency Contact', null, true)]
                        : $emergencyContacts
                            ->map(fn (EmployeeFamily $contact, int $index): array => $this->field(
                                'Emergency Contact '.($index + 1),
                                $this->joinFilled([$this->joinFilled([$contact->name, $this->wrap($contact->relationship)], ' '), $contact->phone_number], ' - '),
                                true,
                            ))
                            ->all(), [
                                $this->table('Family Members', ['Name', 'Date of Birth', 'Gender', 'Relationship', 'Occupation', 'Phone'], $families
                                    ->reject(fn (EmployeeFamily $family): bool => $family->is_emergency_contact)
                                    ->map(fn (EmployeeFamily $family): array => [
                                        $this->text($family->name),
                                        $this->dateWithAge($family->date_of_birth) ?? '-',
                                        $this->text($family->gender),
                                        $this->text($family->relationship),
                                        $this->text($family->occupation),
                                        $this->text($family->phone_number),
                                    ])),
                            ]),
                ],
            ],
            [
                'key' => 'experience',
                'title' => 'Education & Experience',
                'groups' => [
                    $this->group('Education & Professional Experience', [], [
                        $this->table('Educational Background', ['Degree', 'Institution Name', 'Field of Study', 'City', 'Graduation Year', 'GPA'], EmployeeEducation::query()
                            ->with('educationLevel:id,name')
                            ->where('employee_id', $employeeId)
                            ->orderBy('sequence')
                            ->get()
                            ->map(fn (EmployeeEducation $education): array => [
                                $this->text($education->educationLevel?->name),
                                $this->text($education->institution_name),
                                $this->text($education->major),
                                $this->text($education->city),
                                $this->joinFilled([$education->start_year, $education->graduation_year], ' – ') ?? '-',
                                $this->text($education->gpa),
                            ]), true),
                        $this->table('Certifications', ['Certification Name', 'Issuing Institution', 'Effective Date', 'Expiration Date'], EmployeeCertification::query()
                            ->where('employee_id', $employeeId)
                            ->orderBy('sequence')
                            ->get()
                            ->map(fn (EmployeeCertification $certification): array => [
                                $this->text($certification->certificate_name),
                                $this->text($certification->issuer),
                                $this->dateLabel($certification->effective_date) ?? '-',
                                $this->dateLabel($certification->expired_at) ?? '-',
                            ])),
                        $this->table('Licenses', ['License Type', 'License Number', 'Expiration Date'], EmployeeLicense::query()
                            ->where('employee_id', $employeeId)
                            ->orderBy('created_at')
                            ->get()
                            ->map(fn (EmployeeLicense $license): array => [
                                $this->text($license->license_type),
                                $this->text($license->license_number),
                                $this->dateLabel($license->expired_at) ?? '-',
                            ])),
                        $this->table('Previous Employment', ['Previous Company Name', 'Last Job Title', 'Tenure', 'Reason for Leaving'], EmployeeWorkExperience::query()
                            ->where('employee_id', $employeeId)
                            ->orderBy('sequence')
                            ->get()
                            ->map(fn (EmployeeWorkExperience $workExperience): array => [
                                $this->text($workExperience->company_name),
                                $this->text($workExperience->last_position),
                                $this->text($workExperience->duration_text
                                    ?: $this->joinFilled([$this->dateLabel($workExperience->start_date), $this->dateLabel($workExperience->end_date)], ' – ')),
                                $this->text($workExperience->resignation_reason),
                            ])),
                        $this->table('References', ['Name', 'Job Title', 'Company', 'Phone'], EmployeeReference::query()
                            ->where('employee_id', $employeeId)
                            ->orderBy('sequence')
                            ->get()
                            ->map(fn (EmployeeReference $reference): array => [
                                $this->text($reference->name),
                                $this->text($reference->position),
                                $this->text($reference->company_name),
                                $this->text($reference->phone_number),
                            ])),
                    ]),
                    $this->group('Skills', [
                        $this->field('Languages', $this->listLabel($profile?->languages)),
                        $this->field('Hard Skills', $this->listLabel($profile?->hard_skills)),
                        $this->field('Soft Skills', $this->listLabel($profile?->soft_skills)),
                    ]),
                ],
            ],
            [
                'key' => 'assets',
                'title' => 'Company Assets',
                'groups' => [
                    $this->group('Company Assets & Facilities', [], [
                        $this->table('Assets Held', ['Asset Name', 'Specification', 'Serial Number', 'Handover Date'], CompanyAsset::query()
                            ->where('employee_id', $employeeId)
                            ->orderBy('handover_date')
                            ->orderBy('name')
                            ->get()
                            ->map(fn (CompanyAsset $companyAsset): array => [
                                $this->text($companyAsset->name),
                                $this->text($companyAsset->specification),
                                $this->text($companyAsset->serial_number),
                                $this->dateLabel($companyAsset->handover_date) ?? '-',
                            ])),
                    ]),
                ],
            ],
            [
                'key' => 'attachments',
                'title' => 'Attachments',
                'groups' => [],
                'document_files' => $this->documentFilesFor($employee),
            ],
        ];
    }

    /**
     * Uploaded files per document vault key (see EmployeeDocumentCatalog).
     *
     * @return array<string, list<DetailDocument>>
     */
    public function documentFilesFor(Employee $employee): array
    {
        return $this->employeeDocumentVaultService->filesFor($employee, isSelfService: false);
    }

    /**
     * @return list<DetailField>
     */
    private function employmentFields(Employee $employee, ?User $user): array
    {
        /** @var EmployeeDeployment|null $deployment */
        $deployment = $employee->deployment()
            ->with([
                'company:id,name',
                'department:id,name',
                'officeLocation:id,name',
                'position:id,name',
                'positions:id,name',
            ])
            ->first();
        /** @var EmployeePicAssignment|null $picAssignment */
        $picAssignment = $employee->picAssignment()->with('supervisor.profile:id,employee_id,name,nickname')->first();
        $positionNames = collect([$deployment?->position?->name])
            ->merge($deployment?->positions?->pluck('name') ?? [])
            ->map(fn (mixed $positionName): string => trim((string) $positionName))
            ->filter()
            ->unique()
            ->implode(', ');
        $supervisorName = $picAssignment?->supervisor?->profile?->name;

        return [
            $this->field('Employee ID', $employee->employee_code),
            $this->field('Company', $deployment?->company?->name),
            $this->field('Job Title', $positionNames),
            $this->field('Department', $deployment?->department?->name),
            $this->field('Reporting Structure', filled($supervisorName) ? $supervisorName.' (PIC)' : null),
            $this->field('Employment Type', $deployment === null ? null : ($deployment->is_contract ? 'PKWT (Contract)' : 'PKWTT (Permanent)')),
            $this->field('Join Date', $this->joinFilled([
                $this->dateLabel($deployment?->join_date),
                $this->wrap($this->tenureLabel($deployment?->join_date, $deployment?->resignation_date)),
            ], ' ')),
            $this->field('Contract End Date', $deployment === null ? null : ($deployment->is_contract ? $this->dateLabel($deployment->contract_end_date) : 'No End Date')),
            $this->field('Probation End Date', $deployment?->is_contract ? null : $this->dateLabel($deployment?->probation_end_date)),
            $this->field('Resignation Date', $this->dateLabel($deployment?->resignation_date)),
            $this->field('Work Location', $deployment?->officeLocation?->name ?? $deployment?->workplace),
            $this->field('Work Email', $user?->email),
            $this->field('Status', filled($employee->status) ? Str::title((string) $employee->status) : null),
            $this->field('Core Staff', $employee->is_core_staff ? 'Yes' : 'No'),
        ];
    }

    /**
     * @param  list<DetailField>  $fields
     * @param  list<DetailTable>  $tables
     * @return DetailGroup
     */
    private function group(string $title, array $fields, array $tables = []): array
    {
        return [
            'title' => $title,
            'fields' => $fields,
            'tables' => $tables,
        ];
    }

    private function addressLabel(?EmployeeAddress $address): ?string
    {
        if ($address === null) {
            return null;
        }

        return $this->joinFilled([
            $this->joinFilled([
                $address->address_line,
                $address->village,
                $address->subdistrict,
                $address->regency,
                $address->province,
            ], ', '),
            $address->postal_code,
        ], ' ');
    }

    /**
     * @return DetailField
     */
    private function field(string $label, mixed $value, bool $isRequired = false): array
    {
        $text = trim((string) $value);

        return [
            'label' => $label,
            'value' => $text !== '' ? $text : null,
            'is_required' => $isRequired,
        ];
    }

    /**
     * @param  list<string>  $columns
     * @param  Collection<int, list<string>>  $rows
     * @return DetailTable
     */
    private function table(string $title, array $columns, Collection $rows, bool $isRequired = false): array
    {
        return [
            'title' => $title,
            'is_required' => $isRequired,
            'columns' => $columns,
            'rows' => $rows->values()->all(),
        ];
    }

    private function text(mixed $value): string
    {
        $text = trim((string) $value);

        return $text !== '' ? $text : '-';
    }

    private function wrap(?string $value): ?string
    {
        return filled($value) ? '('.trim($value).')' : null;
    }

    private function dateLabel(mixed $date): ?string
    {
        if (blank($date)) {
            return null;
        }

        return Carbon::parse($date, 'Asia/Jakarta')->format('d M Y');
    }

    private function dateWithAge(mixed $date): ?string
    {
        if (blank($date)) {
            return null;
        }

        return $this->dateLabel($date).' ('.Carbon::parse($date, 'Asia/Jakarta')->age.' y.o)';
    }

    private function tenureLabel(mixed $joinDate, mixed $endDate = null): ?string
    {
        if (blank($joinDate)) {
            return null;
        }

        $tenure = Carbon::parse($joinDate, 'Asia/Jakarta')->diff(filled($endDate) ? Carbon::parse($endDate, 'Asia/Jakarta') : now('Asia/Jakarta'));

        if ($tenure->invert === 1) {
            return null;
        }

        $tenureParts = array_filter([
            $tenure->y > 0 ? $tenure->y.' '.Str::plural('year', $tenure->y) : null,
            $tenure->m > 0 ? $tenure->m.' '.Str::plural('month', $tenure->m) : null,
        ]);

        return $tenureParts !== [] ? implode(', ', $tenureParts) : 'less than a month';
    }

    private function withUnit(mixed $value, string $unit): ?string
    {
        if (blank($value)) {
            return null;
        }

        $number = trim((string) $value);

        if (is_numeric($number) && str_contains($number, '.')) {
            $number = rtrim(rtrim($number, '0'), '.');
        }

        return $number.' '.$unit;
    }

    private function listLabel(mixed $values): ?string
    {
        return is_array($values) ? $this->joinFilled($values, ', ') : null;
    }

    /**
     * @param  array<int, mixed>  $values
     */
    private function joinFilled(array $values, string $separator): ?string
    {
        $text = collect($values)
            ->map(fn (mixed $value): string => match (true) {
                is_scalar($value) => trim((string) $value),
                is_array($value) => trim(implode(' ', array_filter($value, 'is_scalar'))),
                default => '',
            })
            ->filter()
            ->implode($separator);

        return $text !== '' ? $text : null;
    }
}
