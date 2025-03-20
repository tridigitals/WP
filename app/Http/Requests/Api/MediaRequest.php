<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class MediaRequest extends FormRequest
{
    /**
     * Allowed file types and their maximum sizes in MB
     */
    protected const ALLOWED_TYPES = [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
        'document' => ['pdf', 'doc', 'docx', 'txt', 'rtf'],
        'audio' => ['mp3', 'wav', 'ogg'],
        'video' => ['mp4', 'webm', 'avi']
    ];

    protected const MAX_SIZES = [
        'image' => 5120, // 5MB
        'document' => 10240, // 10MB
        'audio' => 20480, // 20MB
        'video' => 51200 // 50MB
    ];

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
            'file' => ['required_without:url', 'file'],
            'url' => ['required_without:file', 'url'],
            'title' => ['nullable', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'disk' => ['sometimes', 'string', 'in:public,s3'],
            'meta_data' => ['nullable', 'array'],
        ];

        if ($this->hasFile('file')) {
            $mimes = $this->getAllowedMimeTypes();
            
            $rules['file'][] = 'mimes:' . implode(',', $mimes);
            $rules['file'][] = File::types($mimes)
                ->max(51200); // 50MB maximum as a global limit

            // Image-specific validations
            if ($this->isImage()) {
                $rules['file'][] = 'dimensions:min_width=100,min_height=100,max_width=5000,max_height=5000';
                $rules['meta_data.width'] = ['nullable', 'integer', 'min:100', 'max:5000'];
                $rules['meta_data.height'] = ['nullable', 'integer', 'min:100', 'max:5000'];
                $rules['meta_data.crop'] = ['nullable', 'array'];
                $rules['meta_data.crop.x'] = ['required_with:meta_data.crop', 'integer', 'min:0'];
                $rules['meta_data.crop.y'] = ['required_with:meta_data.crop', 'integer', 'min:0'];
                $rules['meta_data.crop.width'] = ['required_with:meta_data.crop', 'integer', 'min:100'];
                $rules['meta_data.crop.height'] = ['required_with:meta_data.crop', 'integer', 'min:100'];
            }
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
            'file.required_without' => 'Please provide either a file or a URL.',
            'url.required_without' => 'Please provide either a file or a URL.',
            'file.mimes' => 'The file must be one of the following types: ' . implode(', ', $this->getAllowedMimeTypes()),
            'file.max' => 'The file may not be greater than :max kilobytes.',
            'file.dimensions' => 'The image dimensions are invalid.',
            'meta_data.width.min' => 'The image width must be at least :min pixels.',
            'meta_data.height.min' => 'The image height must be at least :min pixels.',
            'meta_data.width.max' => 'The image width may not be greater than :max pixels.',
            'meta_data.height.max' => 'The image height may not be greater than :max pixels.',
        ];
    }

    /**
     * Get all allowed mime types.
     *
     * @return array
     */
    protected function getAllowedMimeTypes(): array
    {
        return array_merge(...array_values(self::ALLOWED_TYPES));
    }

    /**
     * Check if the uploaded file is an image.
     *
     * @return bool
     */
    protected function isImage(): bool
    {
        if (!$this->hasFile('file')) {
            return false;
        }

        $extension = strtolower($this->file('file')->getClientOriginalExtension());
        return in_array($extension, self::ALLOWED_TYPES['image']);
    }

    /**
     * Get the max file size for a given type.
     *
     * @param string $type
     * @return int
     */
    protected function getMaxSize(string $type): int
    {
        return self::MAX_SIZES[$type] ?? self::MAX_SIZES['document'];
    }
}
