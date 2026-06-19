@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
<link rel="stylesheet" href="{{ asset('assets/css/dashboard/product/create.css') }}">
@endpush

@section('title', 'Create Product')

@section('content')
<div class="form-section">

    {{-- HEADER --}}
    <div class="form-section__header">
        <h1>Create New Product</h1>
        <a href="{{ route('admin.products.index') }}" class="btn-back">
            ← Back to Products
        </a>
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

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="form-grid">

            {{-- LEFT COLUMN --}}
            <div class="form-column">

                {{-- GENERAL --}}
                <div class="form-card">
                    <h2>General Details</h2>

                    <div class="form-group">
                        <label>Product Name <span class="req">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}"
                            class="form-input @error('name') is-invalid @enderror"
                            placeholder="e.g. Coca-Cola 330ml">
                        @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-row-2">
                        <div class="form-group">
                            <label>Product Code <span class="req">*</span></label>
                            <input type="text" name="code" value="{{ old('code') }}"
                                class="form-input @error('code') is-invalid @enderror"
                                placeholder="e.g. PRD-001">
                            @error('code') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="form-group">
                            <label>Barcode</label>
                            <input type="text" name="barcode" value="{{ old('barcode') }}"
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
                            @foreach($categories as $category)
                                <option value="{{ $category->code }}"
                                    {{ old('category_code') == $category->code ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_code') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>

                {{-- PRICING & STOCK --}}
                <div class="form-card">
                    <h2>Pricing & Stock</h2>

                    <div class="form-row-2">
                        <div class="form-group">
                            <label>Cost Price <span class="req">*</span></label>
                            <input type="number" step="0.01" name="cost_price"
                                value="{{ old('cost_price') }}"
                                class="form-input @error('cost_price') is-invalid @enderror"
                                placeholder="0.00">
                            @error('cost_price') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="form-group">
                            <label>Selling Price <span class="req">*</span></label>
                            <input type="number" step="0.01" name="price"
                                value="{{ old('price') }}"
                                class="form-input @error('price') is-invalid @enderror"
                                placeholder="0.00">
                            @error('price') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <div class="form-row-2">
                        <div class="form-group">
                            <label>Stock</label>
                            <input type="number" name="stock" value="{{ old('stock', 0) }}"
                                class="form-input @error('stock') is-invalid @enderror">
                            @error('stock') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="form-group">
                            <label>Min Stock</label>
                            <input type="number" name="min_stock" value="{{ old('min_stock', 0) }}"
                                class="form-input @error('min_stock') is-invalid @enderror">
                            @error('min_stock') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN --}}
            <div class="form-column">

                {{-- IMAGE --}}
                <div class="form-card">
                    <h2>Product Image</h2>
                    <div class="form-group">
                        <div class="image-upload-wrap" id="uploadWrap">
                            <input type="file" name="image" accept="image/*" id="imageInput">
                            <span class="image-upload-icon">🖼</span>
                            <span class="image-upload-label">
                                <span>Click to upload</span> or drag and drop
                            </span>
                            <small class="image-upload-label" style="margin-top:4px;font-size:12px;">
                                PNG, JPG, WEBP up to 2MB
                            </small>
                        </div>
                        <div class="image-preview" id="imagePreview" style="width: 100px">
                            <img id="previewImg" src="" alt="Preview" style="width: 100%" >
                            <div class="image-preview-name" id="previewName"></div>
                        </div>
                    </div>
                </div>

                {{-- EXTRA --}}
                <div class="form-card">
                    <h2>Extra Information</h2>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="4"
                            class="form-input"
                            placeholder="Optional product description...">{{ old('description') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label>Expiry Date</label>
                        <input type="date" name="expiry_date"
                            value="{{ old('expiry_date') }}" class="form-input">
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-select">
                            <option value="active"   {{ old('status') == 'active'   ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- ACTIONS --}}
        <div class="form-actions">
            <a href="{{ route('admin.products.index') }}" class="btn-cancel">Cancel</a>
            <button type="submit" class="btn-submit">
                ✓ Save Product
            </button>
        </div>

    </form>
</div>

@endsection

@push('scripts')
<script>
(function () {

    // ── Image preview ──────────────────────────────────────────────
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
                previewImg.src        = e.target.result;
                previewName.textContent = file.name;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        });
    }

    // ── Toast notification ─────────────────────────────────────────
    function showToast(message, type = 'success') {
        const icons = { success: '✓', error: '✕' };
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <span class="toast-icon">${icons[type] || '✓'}</span>
            <span>${message}</span>
            <button class="toast-close" onclick="this.parentElement.remove()">✕</button>
        `;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.animation = 'slideOut .3s ease-out forwards';
            toast.addEventListener('animationend', () => toast.remove());
        }, 4000);
    }

    // Show flash messages
    @if(session('success'))
        showToast(@json(session('success')), 'success');
    @endif
    @if(session('error'))
        showToast(@json(session('error')), 'error');
    @endif

})();
</script>

@endpush
