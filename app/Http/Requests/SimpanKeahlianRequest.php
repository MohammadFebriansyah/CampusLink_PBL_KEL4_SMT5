<?php

namespace App\Http\Requests;

use App\Models\Keahlian;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class SimpanKeahlianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // akses dibatasi middleware EnsureRole:admin di route
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['nama' => trim((string) $this->input('nama'))]);
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'nama' => [
                'required', 'string', 'max:100',
                // Tidak membedakan huruf besar/kecil: "web development" dianggap sama dengan "Web Development".
                function (string $atribut, mixed $nilai, \Closure $gagal) use ($id) {
                    $sudahAda = Keahlian::whereRaw('lower(nama) = ?', [Str::lower($nilai)])
                        ->when($id, fn ($q) => $q->where('id', '!=', $id))
                        ->exists();

                    if ($sudahAda) {
                        $gagal('Keahlian ini sudah ada.');
                    }
                },
            ],
        ];
    }

    public function attributes(): array
    {
        return ['nama' => 'nama keahlian'];
    }
}
