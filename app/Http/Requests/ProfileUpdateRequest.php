<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request Pembaruan Profil Pengguna
 *
 * Memvalidasi nama, email unik, dan nomor telepon WhatsApp pelanggan/pengguna,
 * dengan sanitasi otomatis format nomor ponsel (menormalkan awalan 62/+62 menjadi 08).
 */
class ProfileUpdateRequest extends FormRequest
{
    /**
     * Sanitasi data sebelum validasi dijalankan.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('no_hp') && ! empty($this->no_hp)) {
            $noHp = preg_replace('/[^0-9]/', '', (string) $this->no_hp);
            if (str_starts_with($noHp, '62')) {
                $noHp = '0'.substr($noHp, 2);
            }
            $this->merge(['no_hp' => $noHp]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama' => ['sometimes', 'required', 'string', 'max:100'],
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'no_hp' => ['nullable', 'string', 'regex:/^(\+62|62|0)8[1-9][0-9]{6,11}$/'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:100',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
        ];
    }
}
