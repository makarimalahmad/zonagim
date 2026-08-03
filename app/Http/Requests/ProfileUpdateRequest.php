<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],

            'phone' => ['nullable', 'string', 'regex:/^8[0-9]{8,13}$/'],
            'address' => ['nullable', 'array'],
            'address.country' => ['nullable', 'string', 'max:100'],
            'address.province_code' => ['nullable', 'string', 'exists:indonesia_provinces,code'],
            'address.province' => ['nullable', 'string', 'max:100'],
            'address.city_code' => ['nullable', 'string', 'exists:indonesia_cities,code'],
            'address.city' => ['nullable', 'string', 'max:100'],
            'address.district_code' => ['nullable', 'string', 'exists:indonesia_districts,code'],
            'address.district' => ['nullable', 'string', 'max:100'],
            'address.village_code' => ['nullable', 'string', 'exists:indonesia_villages,code'],
            'address.village' => ['nullable', 'string', 'max:100'],
            'address.street' => ['nullable', 'string', 'max:255'],
            'address.zip' => ['nullable', 'string', 'regex:/^[0-9]{5}$/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama lengkap maksimal 255 karakter.',
            'phone.regex' => 'Nomor WhatsApp harus diawali angka 8 dan berisi 9–14 digit.',
            'address.array' => 'Format alamat tidak valid.',
            'address.country.max' => 'Nama negara maksimal 100 karakter.',
            'address.province.max' => 'Nama provinsi maksimal 100 karakter.',
            'address.city.max' => 'Nama kota/kabupaten maksimal 100 karakter.',
            'address.street.max' => 'Alamat lengkap maksimal 255 karakter.',
            'address.zip.regex' => 'Kode pos harus terdiri dari 5 angka.',
        ];
    }
}
