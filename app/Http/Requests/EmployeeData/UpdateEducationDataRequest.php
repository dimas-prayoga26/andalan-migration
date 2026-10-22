<?php

namespace App\Http\Requests\EmployeeData;

/**
 * Step 3 – Education, Experience & Skills.
 */
class UpdateEducationDataRequest extends EmployeeDataStepRequest
{
    /**
     * @return array<string, list<string>>
     */
    protected function repeatableRowFields(): array
    {
        return [
            'educations' => ['education_level_id', 'institution_name', 'major', 'city', 'start_year', 'graduation_year', 'gpa'],
            'certifications' => ['certificate_name', 'issuer', 'effective_date', 'expired_at', 'city'],
            'work_experiences' => ['company_name', 'last_position', 'duration_text', 'resignation_reason'],
            'references' => ['name', 'position', 'company_name', 'phone_number'],
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $latestYear = (int) now()->addYears(10)->format('Y');

        return [
            'educations' => ['array'],
            'educations.*.id' => ['nullable', 'string'],
            'educations.*.education_level_id' => ['nullable', 'string', 'exists:education_levels,id'],
            'educations.*.institution_name' => ['required', 'string', 'max:255'],
            'educations.*.major' => ['nullable', 'string', 'max:255'],
            'educations.*.city' => ['nullable', 'string', 'max:255'],
            'educations.*.start_year' => ['nullable', 'integer', 'between:1950,'.$latestYear],
            'educations.*.graduation_year' => ['nullable', 'integer', 'between:1950,'.$latestYear],
            'educations.*.gpa' => ['nullable', 'numeric', 'between:0,4'],
            'certifications' => ['array'],
            'certifications.*.id' => ['nullable', 'string'],
            'certifications.*.certificate_name' => ['required', 'string', 'max:255'],
            'certifications.*.issuer' => ['nullable', 'string', 'max:255'],
            'certifications.*.effective_date' => ['nullable', 'date_format:Y-m-d'],
            'certifications.*.expired_at' => ['nullable', 'date_format:Y-m-d'],
            'certifications.*.city' => ['nullable', 'string', 'max:255'],
            'work_experiences' => ['array'],
            'work_experiences.*.id' => ['nullable', 'string'],
            'work_experiences.*.company_name' => ['required', 'string', 'max:255'],
            'work_experiences.*.last_position' => ['nullable', 'string', 'max:255'],
            'work_experiences.*.duration_text' => ['nullable', 'string', 'max:100'],
            'work_experiences.*.resignation_reason' => ['nullable', 'string', 'max:1000'],
            'references' => ['array'],
            'references.*.id' => ['nullable', 'string'],
            'references.*.name' => ['required', 'string', 'max:255'],
            'references.*.position' => ['nullable', 'string', 'max:255'],
            'references.*.company_name' => ['nullable', 'string', 'max:255'],
            'references.*.phone_number' => ['nullable', 'string', 'regex:'.self::PHONE_PATTERN],
            'languages' => ['nullable', 'string', 'max:500'],
            'hard_skills' => ['nullable', 'string', 'max:500'],
            'soft_skills' => ['nullable', 'array'],
            'soft_skills.*' => ['string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'educations.*.education_level_id' => 'Tingkat Pendidikan',
            'educations.*.institution_name' => 'Nama Institusi',
            'educations.*.major' => 'Jurusan',
            'educations.*.city' => 'Kota',
            'educations.*.start_year' => 'Tahun Masuk',
            'educations.*.graduation_year' => 'Tahun Lulus',
            'educations.*.gpa' => 'IPK',
            'certifications.*.certificate_name' => 'Nama Sertifikat',
            'certifications.*.issuer' => 'Lembaga Penerbit',
            'certifications.*.effective_date' => 'Tanggal Berlaku',
            'certifications.*.expired_at' => 'Tanggal Berakhir',
            'certifications.*.city' => 'Kota',
            'work_experiences.*.company_name' => 'Nama Perusahaan',
            'work_experiences.*.last_position' => 'Jabatan Terakhir',
            'work_experiences.*.duration_text' => 'Durasi Bekerja',
            'work_experiences.*.resignation_reason' => 'Alasan Resign',
            'references.*.name' => 'Nama Atasan Lama',
            'references.*.position' => 'Jabatan Atasan Lama',
            'references.*.company_name' => 'Nama Perusahaan',
            'references.*.phone_number' => 'Nomor Handphone',
            'languages' => 'Bahasa Yang Dikuasai',
            'hard_skills' => 'Hard Skills',
            'soft_skills' => 'Soft Skills',
        ];
    }
}
