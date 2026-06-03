@extends('layouts.app')

@section('title', 'Edit Product')

@push('styles')
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
<link rel="stylesheet" href="{{ asset('assets/css/dashboard/product/edit.css') }}">
@endpush

@section('content')
<div class="form-section">

    {{-- HEADER --}}
    <div class="form-section__header">
        <div style="display:flex; align-items:center; gap:10px;">
            <h1>Edit Product</h1>
            <span class="product-code-badge"># {{ $product->code }}</span>
        </div>
        <a href="{{ route('admin.products.index') }}" class="btn-back">← Back to Products</a>
    </div>

    {{-- ERRORS --}}
    @if ($errors->any())
        <div class="alert-error">
            <strong>Please fix the errors below:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.products.update', $product->code) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-grid">

            {{-- LEFT --}}
            <div class="form-column">

                {{-- GENERAL --}}
                <div class="form-card">
                    <h2>General Details</h2>

                    <div class="form-group">
                        <label>Product Name <span class="req">*</span></label>
                        <input type="text" name="name"
                            value="{{ old('name', $product->name) }}"
                            class="form-input @error('name') is-invalid @enderror"
                            placeholder="e.g. Coca-Cola 330ml">
                        @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-row-2">
                        <div class="form-group">
                            <label>Product Code</label>
                            <input type="text" name="code"
                                value="{{ $product->code }}"
                                class="form-input" readonly>
                        </div>
                        <div class="form-group">
                            <label>Barcode</label>
                            <input type="text" name="barcode"
                                value="{{ old('barcode', $product->barcode) }}"
                                class="form-input @error('barcode') is-invalid @enderror"
                                placeholder="e.g. 8850999126838">
                            @error('barcode') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Category <span class="req">*</span></label>
                        <select name="category_code"
                            class="form-select @error('category_code') is-invalid @enderror">
                            <option value="">-- Select Category --</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->code }}"
                                    {{ old('category_code', $product->category_code) == $category->code ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_code') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>

                {{-- PRICING --}}
                <div class="form-card">
                    <h2>Pricing & Stock</h2>

                    <div class="form-row-2">
                        <div class="form-group">
                            <label>Cost Price <span class="req">*</span></label>
                            <input type="number" step="0.01" name="cost_price"
                                value="{{ old('cost_price', $product->cost_price) }}"
                                class="form-input @error('cost_price') is-invalid @enderror">
                            @error('cost_price') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="form-group">
                            <label>Selling Price <span class="req">*</span></label>
                            <input type="number" step="0.01" name="price"
                                value="{{ old('price', $product->price) }}"
                                class="form-input @error('price') is-invalid @enderror">
                            @error('price') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <div class="form-row-2">
                        <div class="form-group">
                            <label>Stock</label>
                            <input type="number" name="stock"
                                value="{{ old('stock', $product->stock) }}"
                                class="form-input @error('stock') is-invalid @enderror">
                            @error('stock') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="form-group">
                            <label>Min Stock</label>
                            <input type="number" name="min_stock"
                                value="{{ old('min_stock', $product->min_stock) }}"
                                class="form-input @error('min_stock') is-invalid @enderror">
                            @error('min_stock') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT --}}
            <div class="form-column">

                {{-- IMAGE --}}
                <div class="form-card">
                    <h2>Product Image</h2>

                    {{-- Current image --}}
                    @if ($product->image)
                        <div class="current-image-wrap">
                            <img src="{{ asset('storage/' . $product->image) }}" alt="">
                            <div class="current-image-info">
                                <span>Current image</span>
                                {{-- <strong>{{ basename($product->image) }}</strong> --}}
                            </div>
                        </div>
                    @endif

                    {{-- Upload new --}}
                    <div class="form-group">
                        <label>{{ $product->image ? 'Replace Image' : 'Upload Image' }}</label>
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
                        <div class="image-preview-box" id="imagePreview">
                            <img id="previewImg" src="" alt="">
                            <div class="image-preview-footer">
                                <span class="image-preview-name" id="previewName"></span>
                                <button type="button" class="image-preview-remove" id="removeImage">Remove</button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- EXTRA --}}
                <div class="form-card">
                    <h2>Extra Information</h2>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="3" class="form-input"
                            placeholder="Optional product description...">{{ old('description', $product->description) }}</textarea>
                    </div>

                    <div class="form-group">
                        <label>Expiry Date</label>
                        <input type="date" name="expiry_date"
                            value="{{ old('expiry_date', $product->expiry_date) }}"
                            class="form-input">
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-select">
                            <option value="active"   {{ old('status', $product->status) == 'active'   ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $product->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>

            </div>
        </div>

        {{-- ACTIONS --}}
        <div class="form-actions">
            <a href="{{ route('admin.products.index') }}" class="btn-cancel">Cancel</a>
            <button type="submit" class="btn-submit">✓ Update Product</button>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
(function () {

    // picture preview
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

    // Toaster
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <span>${message}</span>
            <button class="toast-close" onclick="this.parentElement.remove()">✕</button>
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
