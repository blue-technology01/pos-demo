@extends('layouts.app')

@section('title', 'Edit Product')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/product/create.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/product/edit.css') }}">
@endpush

@section('content')

<div class="form-section">

    {{-- ── Header ── --}}
    <div class="form-section__header">
        <div>
            <h1>Edit product</h1>
            <span class="product-code-badge"># {{ $product->code }}</span>
        </div>
        <a href="{{ route('admin.products.index') }}" class="btn-back">
            <i class="ti ti-arrow-left" aria-hidden="true"></i> Back to products
        </a>
    </div>

    {{-- ── Validation errors ── --}}
    @if($errors->any())
        <div class="alert-error">
            <strong>Please fix the errors below:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.products.update', $product->code) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

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
                            value="{{ old('name', $product->name) }}"
                            class="form-input {{ $errors->has('name') ? 'is-invalid' : '' }}"
                            placeholder="e.g. Coca-Cola 330ml"
                            autocomplete="off"
                        >
                        @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-row-2">
                        <div class="form-group">
                            <label for="code">Product code</label>
                            <input
                                type="text"
                                id="code"
                                name="code"
                                value="{{ $product->code }}"
                                class="form-input"
                                readonly
                            >
                        </div>
                        <div class="form-group">
                            <label for="barcode">Barcode</label>
                            <input
                                type="text"
                                id="barcode"
                                name="barcode"
                                value="{{ old('barcode', $product->barcode) }}"
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
                                    {{ old('category_code', $product->category_code) == $category->code ? 'selected' : '' }}
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
                                value="{{ old('cost_price', $product->cost_price) }}"
                                class="form-input {{ $errors->has('cost_price') ? 'is-invalid' : '' }}"
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
                                value="{{ old('price', $product->price) }}"
                                class="form-input {{ $errors->has('price') ? 'is-invalid' : '' }}"
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
                                value="{{ old('stock', $product->stock) }}"
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
                                value="{{ old('min_stock', $product->min_stock) }}"
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
                    {{-- Current image --}}
                    @if($product->image)
                        <div class="current-image-wrap">
                            <img style="width:100%" src="{{ asset('storage/' . $product->image) }}" >
                            <div class="current-image-info">
                                <span>Current image</span>
                            </div>
                        </div>
                    @endif
                    <div class="form-group">
                        <label>{{ $product->image ? 'Replace image' : 'Upload image' }}</label>
                        <div class="image-upload-wrap" id="uploadWrap">
                            <input type="file" name="image" accept="image/*" id="imageInput">
                            <div class="image-upload-trigger" id="uploadTrigger">
                                <div class="upload-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                                    </svg>
                                </div>
                                <p class="upload-text"><span>Click to upload</span> or drag & drop</p>
                                <p class="upload-hint">PNG, JPG, WEBP — max 2MB</p>
                            </div>
                        </div>
                        <div class="image-preview-box" style="display: flex; flex-direction:row" id="imagePreview" >
                            <img id="previewImg" src="" style="width: 100%" >
                            <div class="image-preview-footer">
                                <span class="image-preview-name" id="previewName" style="width: 100%"></span>
                                <button type="button" class="image-preview-remove" style="border: border: 0.5px solid var(--color-border-secondary, #d1d5db); padding: 7px 16px; border-radius: 50px;" id="removeImage">
                                    <i class="ti ti-x" aria-hidden="true"></i> Remove
                                </button>
                            </div>
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
                            rows="3"
                            class="form-input"
                            placeholder="Optional product description..."
                        >{{ old('description', $product->description) }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="expiry_date">Expiry date</label>
                        <input
                            type="date"
                            id="expiry_date"
                            name="expiry_date"
                            value="{{ old('expiry_date', $product->expiry_date) }}"
                            class="form-input"
                        >
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" class="form-select">
                            <option value="active"   {{ old('status', $product->status) == 'active'   ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $product->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Actions ── --}}
        <div class="form-actions">
            <a href="{{ route('admin.products.index') }}" class="btn-cancel">
                <i class="ti ti-x" aria-hidden="true"></i> Cancel
            </a>
            <button type="submit" class="btn-submit">
                <i class="ti ti-device-floppy" aria-hidden="true"></i> Update product
            </button>
        </div>

    </form>
</div>

@endsection

@push('scripts')
<script>
(function () {

    // ── Image preview ──────────────────────────────────────────────
    const imageInput    = document.getElementById('imageInput');
    const uploadTrigger = document.getElementById('uploadTrigger');
    const imagePreview  = document.getElementById('imagePreview');
    const previewImg    = document.getElementById('previewImg');
    const previewName   = document.getElementById('previewName');
    const removeBtn     = document.getElementById('removeImage');

    if (imageInput) {
        imageInput.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = e => {
                previewImg.src              = e.target.result;
                previewName.textContent     = file.name;
                uploadTrigger.style.display = 'none';
                imagePreview.style.display  = 'flex';
            };
            reader.readAsDataURL(file);
        });
    }

    if (removeBtn) {
        removeBtn.addEventListener('click', function () {
            imageInput.value            = '';
            previewImg.src              = '';
            imagePreview.style.display  = 'none';
            uploadTrigger.style.display = 'flex';
        });
    }

    // ── Toast ──────────────────────────────────────────────────────
    function showToast(message, type = 'success') {
        const iconMap = { success: 'ti-circle-check', error: 'ti-circle-x' };
        const toast   = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <i class="ti ${iconMap[type] ?? 'ti-circle-check'} toast-icon" aria-hidden="true"></i>
            <span>${message}</span>
            <button class="toast-close" onclick="this.parentElement.remove()" aria-label="Close">
                <i class="ti ti-x" aria-hidden="true"></i>
            </button>
        `;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.animation = 'slideOut .3s ease-out forwards';
            toast.addEventListener('animationend', () => toast.remove());
        }, 4000);
    }

    @if(session('success'))
        showToast(@json(session('success')), 'success');
    @endif
    @if(session('error'))
        showToast(@json(session('error')), 'error');
    @endif

})();
</script>
@endpush
