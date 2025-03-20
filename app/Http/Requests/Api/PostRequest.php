<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostRequest extends FormRequest
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
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('posts')->ignore($this->post)
            ],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'content' => ['required', 'string'],
            'post_type' => ['required', 'string', Rule::in(['post', 'page', 'custom'])],
            'status' => ['required', 'string', Rule::in(['draft', 'published', 'scheduled'])],
            'visibility' => ['required', 'string', Rule::in(['public', 'private', 'password_protected'])],
            'password' => ['nullable', 'string', 'min:6', 'required_if:visibility,password_protected'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:tags,id'],
            'featured_image' => ['nullable', 'string'],
            'published_at' => ['nullable', 'date', 'required_if:status,scheduled'],
            
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
            'meta.twitter_image' => ['nullable', 'url'],
            
            // Layout data for page builder
            'layout_data' => ['nullable', 'array'],
        ];

        // Add custom validation for scheduled posts
        if ($this->input('status') === 'scheduled') {
            $rules['published_at'][] = 'after:now';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The post title is required.',
            'slug.required' => 'The post slug is required.',
            'slug.unique' => 'This slug is already in use.',
            'content.required' => 'The post content is required.',
            'published_at.after' => 'The scheduled date must be in the future.',
            'password.required_if' => 'A password is required for password protected posts.',
            'published_at.required_if' => 'A publish date is required for scheduled posts.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('title') && !$this->has('slug')) {
            $this->merge([
                'slug' => \Str::slug($this->title)
            ]);
        }
    }
}
