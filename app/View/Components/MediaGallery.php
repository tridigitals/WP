<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\Media;
use Illuminate\Support\Collection;

class MediaGallery extends Component
{
    /**
     * The media items to display.
     */
    public Collection $items;

    /**
     * Whether multiple selection is allowed.
     */
    public bool $multiple;

    /**
     * The selected media items.
     */
    public array $selected;

    /**
     * The allowed file types.
     */
    public array $allowedTypes;

    /**
     * Create a new component instance.
     */
    public function __construct(
        $items = null,
        bool $multiple = false,
        array $selected = [],
        array $allowedTypes = null
    ) {
        $this->items = $items instanceof Collection ? $items : Media::latest()->get();
        $this->multiple = $multiple;
        $this->selected = $selected;
        $this->allowedTypes = $allowedTypes ?? config('cms.media.allowed_file_types', []);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.media-gallery', [
            'items' => $this->items,
            'multiple' => $this->multiple,
            'selected' => $this->selected,
            'allowedTypes' => $this->allowedTypes,
            'fileTypeIcons' => $this->getFileTypeIcons(),
            'maxFileSize' => config('cms.media.max_file_size', 5120), // 5MB default
        ]);
    }

    /**
     * Get the file type icons mapping.
     */
    protected function getFileTypeIcons(): array
    {
        return [
            'image' => 'fa-image',
            'video' => 'fa-video',
            'audio' => 'fa-music',
            'document' => 'fa-file-alt',
            'pdf' => 'fa-file-pdf',
            'archive' => 'fa-file-archive',
            'default' => 'fa-file',
        ];
    }
}