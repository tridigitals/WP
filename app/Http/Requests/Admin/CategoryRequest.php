<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:categories,id'],
        ];

        // Make slug unique but allow the current category's slug when updating
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['slug'] = ['required', 'string', 'max:255', 'unique:categories,slug,' . $this->category->id];
        } else {
            $rules['slug'] = ['required', 'string', 'max:255', 'unique:categories,slug'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama kategori wajib diisi',
            'name.max' => 'Nama kategori maksimal 255 karakter',
            'slug.required' => 'Slug wajib diisi',
            'slug.unique' => 'Slug sudah digunakan',
            'parent_id.exists' => 'Kategori induk tidak ditemukan',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->filled('name') && !$this->filled('slug')) {
            $this->merge([
                'slug' => Str::slug($this->name),
            ]);
        }
    }
}
