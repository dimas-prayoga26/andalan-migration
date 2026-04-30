<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AbsenRequest extends FormRequest
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
        return [
            'id_user' => 'required|exists:users,id',
            'domisili' => 'required|string',
            'tanggal' => 'required|date|date_format:Y-m-d',
            'jam_masuk' => 'required|date_format:H:i|before_or_equal:jam_keluar',
            'jam_keluar' => 'nullable|date_format:H:i|after_or_equal:jam_masuk',
            'status' => 'required|in:Terlambat,Hadir',
        ];
    }

    public function messages(){
        return [
            'id_user.required' => 'ID User harus diisi.',
            'domisili.required' => 'Domisili harus diisi.',
            'tanggal.required' => 'Tanggal harus diisi.',
            'jam_masuk.required' => 'Jam Masuk harus diisi.',
            'status.required' => 'Status harus diisi.',

            'id_user.exists' => 'ID User tidak ditemukan.',
            'domisili.string' => 'Domisili harus berupa string.',
            'tanggal.date' => 'Tanggal harus berupa tanggal.',
            'tanggal.date_format' => 'Format tanggal harus YYYY-MM-DD.',
            'jam_masuk.date_format' => 'Format jam harus HH:MM.',
            'jam_keluar.date_format' => 'Format jam harus HH:MM.',

            'jam_masuk.before_or_equal' => 'Jam Masuk harus sebelum Jam Keluar.',
            'jam_keluar.after_or_equal' => 'Jam Keluar harus setelah Jam Masuk.',

            'status.in' => 'Status harus Hadir atau Terlambat.',
        ];
    }
}
