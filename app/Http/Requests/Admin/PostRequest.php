<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class PostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Will be handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'excerpt' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,published,archived'],
            'featured_image' => ['nullable', 'image', 'max:2048'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:tags,id'],
            'published_at' => ['nullable', 'date'],
            'meta' => ['nullable', 'array'],
        ];

        // Make slug unique but allow the current post's slug when updating
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['slug'] = ['required', 'string', 'max:255', 'unique:posts,slug,' . $this->post->id];
        } else {
            $rules['slug'] = ['required', 'string', 'max:255', 'unique:posts,slug'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul post wajib diisi',
            'title.max' => 'Judul post maksimal 255 karakter',
            'content.required' => 'Konten post wajib diisi',
            'slug.required' => 'Slug wajib diisi',
            'slug.unique' => 'Slug sudah digunakan',
            'status.required' => 'Status wajib diisi',
            'status.in' => 'Status tidak valid',
            'category_id.exists' => 'Kategori yang dipilih tidak valid',
            'tags.array' => 'Format tags tidak valid',
            'tags.*.exists' => 'Tag yang dipilih tidak valid',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->filled('title') && !$this->filled('slug')) {
            $this->merge([
                'slug' => Str::slug($this->title),
            ]);
        }
    }
}
