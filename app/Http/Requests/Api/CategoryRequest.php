<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Category;

class CategoryRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')->ignore($this->category)
            ],
            'description' => ['nullable', 'string'],
            'parent_id' => [
                'nullable',
                'exists:categories,id',
                function ($attribute, $value, $fail) {
                    if ($this->category && $value == $this->category->id) {
                        $fail('A category cannot be its own parent.');
                    }
                    
                    if ($value && $this->category) {
                        // Prevent circular references
                        $parent = Category::find($value);
                        while ($parent) {
                            if ($parent->id === $this->category->id) {
                                $fail('Cannot create circular category references.');
                                break;
                            }
                            $parent = $parent->parent;
                        }
                    }
                }
            ],
            'featured_image' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
            
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
            'name.required' => 'The category name is required.',
            'slug.required' => 'The category slug is required.',
            'slug.unique' => 'This slug is already in use.',
            'parent_id.exists' => 'The selected parent category does not exist.',
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
