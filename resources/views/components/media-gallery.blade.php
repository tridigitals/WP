<div
    x-data="{
        selectedItems: @json($selected),
        uploadProgress: 0,
        isUploading: false,
        previewUrl: null,
        previewItem: null,
        
        toggleSelection(id) {
            if (this.multiple) {
                const index = this.selectedItems.indexOf(id);
                if (index === -1) {
                    this.selectedItems.push(id);
                } else {
                    this.selectedItems.splice(index, 1);
                }
            } else {
                this.selectedItems = [id];
            }
            this.$dispatch('media-selected', this.selectedItems);
        },
        
        isSelected(id) {
            return this.selectedItems.includes(id);
        },
        
        showPreview(item) {
            this.previewItem = item;
            this.previewUrl = item.type.startsWith('image/') ? item.url : null;
        },
        
        async handleUpload(event) {
            const files = event.target.files;
            if (!files.length) return;
            
            this.isUploading = true;
            this.uploadProgress = 0;
            
            try {
                for (let file of files) {
                    if (file.size > {{ $maxFileSize * 1024 }}) {
                        throw new Error(`File ${file.name} exceeds maximum size of ${$maxFileSize}KB`);
                    }
                    
                    const formData = new FormData();
                    formData.append('file', file);
                    
                    const response = await fetch('/api/v1/media', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        onUploadProgress: (e) => {
                            this.uploadProgress = Math.round((e.loaded * 100) / e.total);
                        }
                    });
                    
                    if (!response.ok) throw new Error('Upload failed');
                    
                    const result = await response.json();
                    this.$dispatch('media-uploaded', result.media);
                }
            } catch (error) {
                alert(error.message);
            } finally {
                this.isUploading = false;
                this.uploadProgress = 0;
            }
        }
    }"
    class="media-gallery"
>
    {{-- Upload Area --}}
    <div class="upload-area p-6 mb-6 border-2 border-dashed rounded-lg text-center">
        <input
            type="file"
            @change="handleUpload"
            class="hidden"
            id="media-upload"
            {{ $multiple ? 'multiple' : '' }}
            accept="{{ collect($allowedTypes)->flatten()->map(fn($type) => '.'.$type)->implode(',') }}"
        >
        <label
            for="media-upload"
            class="cursor-pointer inline-block"
        >
            <div class="text-gray-500">
                <i class="fas fa-cloud-upload-alt text-3xl mb-2"></i>
                <p>Drop files here or click to upload</p>
                <p class="text-sm">Maximum file size: {{ $maxFileSize }}KB</p>
            </div>
        </label>
        
        {{-- Upload Progress --}}
        <div x-show="isUploading" class="mt-4">
            <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div
                    class="bg-blue-600 h-2.5 rounded-full"
                    x-bind:style="'width: ' + uploadProgress + '%'"
                ></div>
            </div>
            <p class="text-sm mt-2" x-text="`Uploading: ${uploadProgress}%`"></p>
        </div>
    </div>

    {{-- Media Grid --}}
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
        @foreach($items as $item)
            <div
                class="media-item relative rounded-lg overflow-hidden border"
                :class="{ 'ring-2 ring-blue-500': isSelected({{ $item->id }}) }"
                @click="toggleSelection({{ $item->id }})"
            >
                {{-- Preview --}}
                <div class="aspect-square bg-gray-100">
                    @if(Str::startsWith($item->mime_type, 'image/'))
                        <img
                            src="{{ $item->url() }}"
                            alt="{{ $item->title ?? $item->original_name }}"
                            class="w-full h-full object-cover"
                        >
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                            <i class="fas {{ $fileTypeIcons[explode('/', $item->mime_type)[0]] ?? $fileTypeIcons['default'] }} text-3xl"></i>
                        </div>
                    @endif
                </div>

                {{-- Info --}}
                <div class="p-2 text-sm">
                    <p class="truncate" title="{{ $item->original_name }}">
                        {{ $item->original_name }}
                    </p>
                    <p class="text-gray-500 text-xs">
                        {{ number_format($item->size / 1024, 1) }}KB
                    </p>
                </div>

                {{-- Selection Indicator --}}
                <div
                    x-show="isSelected({{ $item->id }})"
                    class="absolute top-2 right-2 w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center text-white"
                >
                    <i class="fas fa-check text-sm"></i>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Preview Modal --}}
    <div
        x-show="previewItem"
        x-cloak
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        @click.self="previewItem = null"
    >
        <div class="bg-white rounded-lg max-w-3xl w-full mx-4">
            <div class="p-4 border-b flex justify-between items-center">
                <h3 class="text-lg font-semibold" x-text="previewItem?.original_name"></h3>
                <button @click="previewItem = null" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="p-4">
                <template x-if="previewUrl">
                    <img :src="previewUrl" class="max-h-[70vh] mx-auto">
                </template>
                <template x-if="!previewUrl">
                    <div class="text-center py-12">
                        <i class="fas fa-file text-6xl text-gray-400 mb-4"></i>
                        <p>Preview not available</p>
                    </div>
                </template>
                
                <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">File name</p>
                        <p x-text="previewItem?.original_name"></p>
                    </div>
                    <div>
                        <p class="text-gray-500">File type</p>
                        <p x-text="previewItem?.mime_type"></p>
                    </div>
                    <div>
                        <p class="text-gray-500">File size</p>
                        <p x-text="Math.round(previewItem?.size / 1024) + 'KB'"></p>
                    </div>
                    <div>
                        <p class="text-gray-500">Uploaded</p>
                        <p x-text="new Date(previewItem?.created_at).toLocaleDateString()"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.media-gallery {
    @apply p-4;
}

.media-item {
    @apply cursor-pointer transition-all duration-200 hover:shadow-md;
}

[x-cloak] {
    display: none !important;
}
</style>
@endpush