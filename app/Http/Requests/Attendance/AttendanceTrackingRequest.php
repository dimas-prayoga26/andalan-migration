<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

abstract class AttendanceTrackingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'client_ip' => ['nullable', 'ip'],
            'latitude' => ['nullable', 'required_with:longitude', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'required_with:latitude', 'numeric', 'between:-180,180'],
        ];
    }

    public function messages(): array
    {
        return [
            'client_ip.ip' => 'Format IP address tidak valid.',
            'latitude.required_with' => 'Latitude wajib diisi bersama longitude.',
            'latitude.numeric' => 'Latitude harus berupa angka.',
            'latitude.between' => 'Latitude harus berada di antara -90 dan 90.',
            'longitude.required_with' => 'Longitude wajib diisi bersama latitude.',
            'longitude.numeric' => 'Longitude harus berupa angka.',
            'longitude.between' => 'Longitude harus berada di antara -180 dan 180.',
        ];
    }
}
