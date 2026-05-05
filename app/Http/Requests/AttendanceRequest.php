<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'user_id' => 'sometimes|exists:users,id',
                'date' => 'required|date|date_format:Y-m-d',
                'check_out' => 'required|date_format:H:i',
            ];
        }

        return [
            'user_id' => 'sometimes|exists:users,id',
            'date' => 'required|date|date_format:Y-m-d',
            'check_in' => 'required|date_format:H:i|before_or_equal:check_out',
            'check_out' => 'nullable|date_format:H:i|after_or_equal:check_in',
            'status' => 'required|in:present,late,remote,business_trip',
        ];
    }

    public function messages(): array
    {
        return [
            'date.required' => 'Tanggal harus diisi.',
            'check_in.required' => 'Jam Masuk harus diisi.',
            'check_out.required' => 'Jam Keluar harus diisi.',
            'status.required' => 'Status harus diisi.',

            'user_id.exists' => 'ID User tidak ditemukan.',
            'date.date' => 'Tanggal harus berupa tanggal.',
            'date.date_format' => 'Format tanggal harus YYYY-MM-DD.',
            'check_in.date_format' => 'Format jam harus HH:MM.',
            'check_out.date_format' => 'Format jam harus HH:MM.',

            'check_in.before_or_equal' => 'Jam Masuk harus sebelum Jam Keluar.',
            'check_out.after_or_equal' => 'Jam Keluar harus setelah Jam Masuk.',

            'status.in' => 'Status harus present, late, remote, atau business_trip.',
        ];
    }
}
