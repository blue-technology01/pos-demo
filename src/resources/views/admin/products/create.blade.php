@extends('layouts.app')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/product/create.css') }}">
@endpush

@section('title', 'Create Product')

@section('content')

<div class="form-section">

    {{-- ── Header ── --}}
    <div class="form-section__header">
        <h1>Create new product</h1>
        <a href="{{ route('admin.products.index') }}" class="btn-back">
            <i class="ti ti-arrow-left" aria-hidden="true"></i> Back to products
        </a>
    </div>

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="form-grid">

            {{-- ── Left column ── --}}
            <div class="form-column">

                {{-- General details --}}
                <div class="form-card">
                    <h2>General details</h2>

                    <div class="form-group">
                        <label for="name">Product name <span class="req">*</span></label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            class="form-input {{ $errors->has('name') ? 'is-invalid' : '' }}"
                            placeholder="e.g. Coca-Cola 330ml"
                            autocomplete="off"
                        >
                        @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-row-2">
                        <div class="form-group">
                            <label for="code">Product code <span class="req">*</span></label>
                            <input
                                type="text"
                                id="code"
                                name="code"
                                value="{{ old('code') }}"
                                class="form-input {{ $errors->has('code') ? 'is-invalid' : '' }}"
                                placeholder="e.g. PRD-001"
                                autocomplete="off"
                            >
                            @error('code') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="form-group">
                            <label for="barcode">Barcode</label>
                            <input
                                type="text"
                                id="barcode"
                                name="barcode"
                                value="{{ old('barcode') }}"
                                class="form-input {{ $errors->has('barcode') ? 'is-invalid' : '' }}"
                                placeholder="e.g. 8850999126838"
                                autocomplete="off"
                            >
                            @error('barcode') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="category_code">Category <span class="req">*</span></label>
                        <select
                            id="category_code"
                            name="category_code"
                            class="form-select {{ $errors->has('category_code') ? 'is-invalid' : '' }}"
                        >
                            <option value="">— Select category —</option>
                            @foreach($categories as $category)
                                <option
                                    value="{{ $category->code }}"
                                    {{ old('category_code') == $category->code ? 'selected' : '' }}
                                >
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_code') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>

                {{-- Pricing & stock --}}
                <div class="form-card">
                    <h2>Pricing & stock</h2>

                    <div class="form-row-2">
                        <div class="form-group">
                            <label for="cost_price">Cost price <span class="req">*</span></label>
                            <input
                                type="number"
                                step="0.01"
                                id="cost_price"
                                name="cost_price"
                                value="{{ old('cost_price') }}"
                                class="form-input {{ $errors->has('cost_price') ? 'is-invalid' : '' }}"
                                placeholder="0.00"
                            >
                            @error('cost_price') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="form-group">
                            <label for="price">Selling price <span class="req">*</span></label>
                            <input
                                type="number"
                                step="0.01"
                                id="price"
                                name="price"
                                value="{{ old('price') }}"
                                class="form-input {{ $errors->has('price') ? 'is-invalid' : '' }}"
                                placeholder="0.00"
                            >
                            @error('price') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <div class="form-row-2">
                        <div class="form-group">
                            <label for="stock">Stock</label>
                            <input
                                type="number"
                                id="stock"
                                name="stock"
                                value="{{ old('stock', 0) }}"
                                class="form-input {{ $errors->has('stock') ? 'is-invalid' : '' }}"
                            >
                            @error('stock') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="form-group">
                            <label for="min_stock">Min stock</label>
                            <input
                                type="number"
                                id="min_stock"
                                name="min_stock"
                                value="{{ old('min_stock', 0) }}"
                                class="form-input {{ $errors->has('min_stock') ? 'is-invalid' : '' }}"
                            >
                            @error('min_stock') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                </div>

            </div>

            {{-- ── Right column ── --}}
            <div class="form-column">

                {{-- Product image --}}
                <div class="form-card">
                    <h2>Product image</h2>
                    <div class="form-group">
                        <div class="image-upload-wrap" id="uploadWrap">
                            <input type="file" name="image" accept="image/*" id="imageInput">
                            <i class="ti ti-photo-up image-upload-icon" aria-hidden="true"></i>
                            <span class="image-upload-label">
                                <span>Click to upload</span> or drag and drop
                            </span>
                            <small class="image-upload-label" style="margin-top:2px;font-size:11px">
                                PNG, JPG, WEBP — max 2MB
                            </small>
                        </div>
                        <div class="image-preview" id="imagePreview">
                            <img id="previewImg" src="" alt="Preview">
                            <div class="image-preview-name" id="previewName"></div>
                        </div>
                    </div>
                </div>

                {{-- Extra information --}}
                <div class="form-card">
                    <h2>Extra information</h2>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea
                            id="description"
                            name="description"
                            rows="4"
                            class="form-input"
                            placeholder="Optional product description..."
                        >{{ old('description') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="expiry_date">Expiry date</label>
                        <input
                            type="date"
                            id="expiry_date"
                            name="expiry_date"
                            value="{{ old('expiry_date') }}"
                            class="form-input"
                        >
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" class="form-select">
                            <option value="active"   {{ old('status','active') == 'active'   ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>

            </div>
        </div>

        {{-- ── Actions ── --}}
        <div class="form-actions">
            <a href="{{ route('admin.products.index') }}" onclick="showLoader()" class="btn-cancel">
                <i class="ti ti-x" aria-hidden="true"></i> Cancel
            </a>
            <button type="submit" class="btn-submit" onclick="showLoader()">
                <i class="ti ti-device-floppy" aria-hidden="true"></i> Save product
            </button>
        </div>

    </form>
</div>

@endsection

@push('scripts')
<script>
(function () {
    const imageInput   = document.getElementById('imageInput');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg   = document.getElementById('previewImg');
    const previewName  = document.getElementById('previewName');

    if (imageInput) {
        imageInput.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = e => {
                previewImg.src              = e.target.result;
                previewName.textContent     = file.name;
                imagePreview.style.display  = 'block';
            };
            reader.readAsDataURL(file);
        });
    }
})();
</script>
@endpush
