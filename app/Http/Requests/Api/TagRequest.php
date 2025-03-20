<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TagRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tags')->ignore($this->tag)
            ],
            'description' => ['nullable', 'string'],
            'count' => ['sometimes', 'integer', 'min:0'],
            
            // Meta information
            'meta.meta_title' => ['nullable', 'string', 'max:60'],
            'meta.meta_description' => ['nullable', 'string', 'max:160'],
            'meta.meta_keywords' => ['nullable', 'string', 'max:255'],
            'meta.canonical_url' => ['nullable', 'url'],
            'meta.robots' => ['nullable', 'string'],
            'meta.og_title' => ['nullable', 'string', 'max:60'],
            'meta.og_description' => ['nullable', 'string', 'max:160'],
            'meta.og_image' => ['nullable', 'url'],
            'meta.og_type' => ['nullable', 'string'],
            'meta.twitter_card' => ['nullable', 'string'],
            'meta.twitter_title' => ['nullable', 'string', 'max:60'],
            'meta.twitter_description' => ['nullable', 'string', 'max:160'],
            'meta.twitter_image' => ['nullable', 'url']
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The tag name is required.',
            'slug.required' => 'The tag slug is required.',
            'slug.unique' => 'This slug is already in use.',
            'meta.meta_title.max' => 'The meta title should not exceed 60 characters.',
            'meta.meta_description.max' => 'The meta description should not exceed 160 characters.'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('name') && !$this->has('slug')) {
            $this->merge([
                'slug' => \Str::slug($this->name)
            ]);
        }
    }
}
