<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class TagRequest extends FormRequest
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
        ];

        // Make slug unique but allow the current tag's slug when updating
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['slug'] = ['required', 'string', 'max:255', 'unique:tags,slug,' . $this->tag->id];
        } else {
            $rules['slug'] = ['required', 'string', 'max:255', 'unique:tags,slug'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama tag wajib diisi',
            'name.max' => 'Nama tag maksimal 255 karakter',
            'slug.required' => 'Slug wajib diisi',
            'slug.unique' => 'Slug sudah digunakan',
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