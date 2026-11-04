<?php

namespace App\Support\Employee;

/**
 * Choice lists of the "Data Karyawan" wizard. Keys are the stored values, values are the labels shown to the user.
 */
class EmployeeDataOptions
{
    public const GENDERS = [
        'Male' => 'Laki-laki',
        'Female' => 'Perempuan',
    ];

    public const RELIGIONS = [
        'Islam' => 'Islam',
        'Kristen Protestan' => 'Kristen Protestan',
        'Kristen Katolik' => 'Kristen Katolik',
        'Hindu' => 'Hindu',
        'Buddha' => 'Buddha',
        'Khonghucu' => 'Khonghucu',
    ];

    public const MARITAL_STATUSES = [
        'Lajang' => 'Lajang',
        'Menikah' => 'Menikah',
        'Duda' => 'Duda',
        'Janda' => 'Janda',
    ];

    public const NATIONALITIES = [
        'WNI' => 'Warga Negara Indonesia',
        'WNA' => 'Warga Negara Asing',
    ];

    public const BLOOD_TYPES = [
        'A' => 'A',
        'B' => 'B',
        'AB' => 'AB',
        'O' => 'O',
    ];

    public const BANKS = [
        'BCA' => 'BCA',
        'BNI' => 'BNI',
        'BRI' => 'BRI',
        'MANDIRI' => 'MANDIRI',
        'BSI' => 'BSI',
        'BTN' => 'BTN',
        'CIMB NIAGA' => 'CIMB NIAGA',
        'PERMATA' => 'PERMATA',
        'DANAMON' => 'DANAMON',
        'OCBC' => 'OCBC',
        'MAYBANK' => 'MAYBANK',
        'BANK JAGO' => 'BANK JAGO',
        'SEABANK' => 'SEABANK',
    ];

    public const LICENSE_TYPES = [
        'SIM A' => 'SIM A',
        'SIM B1' => 'SIM B1',
        'SIM B2' => 'SIM B2',
        'SIM C' => 'SIM C',
        'SIO' => 'SIO',
    ];

    public const FAMILY_RELATIONSHIPS = [
        'Suami' => 'Suami',
        'Istri' => 'Istri',
        'Anak' => 'Anak',
        'Ayah' => 'Ayah',
        'Ibu' => 'Ibu',
        'Saudara Kandung' => 'Saudara Kandung',
    ];

    public const EMERGENCY_CONTACT_RELATIONSHIPS = [
        'Ayah' => 'Ayah',
        'Ibu' => 'Ibu',
        'Suami' => 'Suami',
        'Istri' => 'Istri',
        'Kakak' => 'Kakak',
        'Adik' => 'Adik',
        'Keluarga Lain' => 'Keluarga Lain',
    ];

    public const SOFT_SKILLS = [
        'Komunikasi Efektif' => 'Komunikasi Efektif',
        'Teamwork' => 'Teamwork',
        'Problem Solving' => 'Problem Solving',
        'Leadership' => 'Leadership',
        'Time Management' => 'Time Management',
        'Adaptability' => 'Adaptability',
        'Emotional Intelligence' => 'Emotional Intelligence',
        'Critical Thinking' => 'Critical Thinking',
        'Negosiasi' => 'Negosiasi',
        'Work Ethic' => 'Work Ethic',
    ];

    /**
     * Family relationships whose gender is implied, so the family table can show it without asking again.
     */
    public const RELATIONSHIP_GENDERS = [
        'Suami' => 'Male',
        'Ayah' => 'Male',
        'Istri' => 'Female',
        'Ibu' => 'Female',
    ];

    /**
     * Family relationships counted as dependents (tanggungan) for payroll.
     */
    public const DEPENDENT_RELATIONSHIPS = ['Anak'];

    /**
     * Older records store marital status in English; map them to the wizard values.
     */
    public static function normalizeMaritalStatus(?string $maritalStatus): ?string
    {
        $maritalStatus = trim((string) $maritalStatus);

        return match (strtolower($maritalStatus)) {
            '' => null,
            'single', 'lajang' => 'Lajang',
            'married', 'menikah' => 'Menikah',
            'duda', 'widower' => 'Duda',
            'janda', 'widow' => 'Janda',
            default => $maritalStatus,
        };
    }
}
