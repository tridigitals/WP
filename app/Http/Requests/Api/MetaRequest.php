<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MetaRequest extends FormRequest
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
            'metable_type' => ['required', 'string', Rule::in(['App\Models\Post', 'App\Models\Category', 'App\Models\Tag'])],
            'metable_id' => ['required', 'integer'],

            // Basic meta tags
            'meta_title' => ['nullable', 'string', 'max:60'],
            'meta_description' => ['nullable', 'string', 'max:160'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'canonical_url' => ['nullable', 'url'],
            'robots' => ['nullable', 'string', Rule::in([
                'index,follow', 'noindex,follow', 'index,nofollow', 'noindex,nofollow'
            ])],

            // Open Graph
            'og_title' => ['nullable', 'string', 'max:60'],
            'og_description' => ['nullable', 'string', 'max:160'],
            'og_image' => ['nullable', 'url'],
            'og_type' => ['nullable', 'string', Rule::in([
                'website', 'article', 'product', 'profile', 'book'
            ])],

            // Twitter Card
            'twitter_card' => ['nullable', 'string', Rule::in([
                'summary', 'summary_large_image', 'app', 'player'
            ])],
            'twitter_title' => ['nullable', 'string', 'max:60'],
            'twitter_description' => ['nullable', 'string', 'max:160'],
            'twitter_image' => ['nullable', 'url'],

            // Schema.org
            'schema_markup' => ['nullable', 'array'],
            'schema_markup.@context' => ['required_with:schema_markup', 'string', 'in:https://schema.org'],
            'schema_markup.@type' => ['required_with:schema_markup', 'string'],

            // Custom meta data
            'custom_meta' => ['nullable', 'array'],
            'custom_meta.*' => ['string']
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
            'meta_title.max' => 'Meta title should not exceed 60 characters for optimal SEO.',
            'meta_description.max' => 'Meta description should not exceed 160 characters for optimal SEO.',
            'og_title.max' => 'Open Graph title should not exceed 60 characters.',
            'og_description.max' => 'Open Graph description should not exceed 160 characters.',
            'twitter_title.max' => 'Twitter Card title should not exceed 60 characters.',
            'twitter_description.max' => 'Twitter Card description should not exceed 160 characters.',
            'schema_markup.@context.required_with' => 'Schema.org markup must include the @context field.',
            'schema_markup.@type.required_with' => 'Schema.org markup must include the @type field.'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert empty strings to null
        $this->merge(collect($this->all())->map(function ($value) {
            return $value === '' ? null : $value;
        })->all());

        // Ensure Schema.org context is set if schema markup is provided
        if ($this->has('schema_markup') && !isset($this->schema_markup['@context'])) {
            $schema = $this->input('schema_markup', []);
            $schema['@context'] = 'https://schema.org';
            $this->merge(['schema_markup' => $schema]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'meta_title' => 'Meta Title',
            'meta_description' => 'Meta Description',
            'meta_keywords' => 'Meta Keywords',
            'canonical_url' => 'Canonical URL',
            'og_title' => 'Open Graph Title',
            'og_description' => 'Open Graph Description',
            'og_image' => 'Open Graph Image',
            'twitter_title' => 'Twitter Card Title',
            'twitter_description' => 'Twitter Card Description',
            'twitter_image' => 'Twitter Card Image'
        ];
    }
}
