@extends('layouts.app')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/product/stock/create.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/stock/show.css') }}">
@endpush

@section('title', 'Stock Adjustment Details')

@section('content')
    <div class="stock-page-header">
        <div>
            <h4 class="stock-page-title">Stock Adjustment #{{ $stockAjustment->id }}</h4>
            <p class="stock-page-subtitle">View the details of this adjustment request.</p>
        </div>
        <a href="{{ route('admin.products.stock.index') }}" class="stock-btn stock-btn-outline">
            <i class="ti ti-arrow-left"></i> Back to List
        </a>
    </div>
    <div class="stock-card">
        <div class="stock-show-header">
            <span class="stock-badge stock-badge-{{ $stockAjustment->status }}">
                {{ ucfirst($stockAjustment->status) }}
            </span>
            <span class="stock-show-date">
                Requested on {{ $stockAjustment->created_at->format('d M Y, h:i A') }}
            </span>
        </div>

        <div class="stock-show-grid">
            <div class="stock-show-field">
                <span class="stock-show-label">Product</span>
                <span class="stock-show-value">
                    {{ $stockAjustment->product?->name ?? '-' }}
                    <span class="stock-show-sub">({{ $stockAjustment->product_code }})</span>
                </span>
            </div>

            <div class="stock-show-field">
                <span class="stock-show-label">Warehouse</span>
                <span class="stock-show-value">{{ $stockAjustment->warehouse?->name ?? '-' }}</span>
            </div>

            <div class="stock-show-field">
                <span class="stock-show-label">Adjustment Date</span>
                <span class="stock-show-value">
                    {{ \Carbon\Carbon::parse($stockAjustment->adjustment_date)->format('d M Y') }}
                </span>
            </div>

            <div class="stock-show-field">
                <span class="stock-show-label">New Quantity</span>
                <span class="stock-show-value">{{ number_format($stockAjustment->new_quantity) }}</span>
            </div>

            <div class="stock-show-field">
                <span class="stock-show-label">Reason</span>
                <span class="stock-show-value">{{ ucfirst($stockAjustment->reason_code) }}</span>
            </div>

            <div class="stock-show-field">
                <span class="stock-show-label">Created By</span>
                <span class="stock-show-value">{{ $stockAjustment->creator?->name ?? '-' }}</span>
            </div>

            @if ($stockAjustment->status !== 'pending')
                <div class="stock-show-field">
                    <span class="stock-show-label">{{ ucfirst($stockAjustment->status) }} By</span>
                    <span class="stock-show-value">{{ $stockAjustment->approver?->name ?? '-' }}</span>
                </div>

                <div class="stock-show-field">
                    <span class="stock-show-label">{{ ucfirst($stockAjustment->status) }} At</span>
                    <span class="stock-show-value">
                        {{ $stockAjustment->approved_at?->format('d M Y, h:i A') ?? '-' }}
                    </span>
                </div>
            @endif

            <div class="stock-show-field stock-show-field-full">
                <span class="stock-show-label">Remark</span>
                <span class="stock-show-value">{{ $stockAjustment->remark ?? '-' }}</span>
            </div>
        </div>

        @if ($stockAjustment->status === 'pending')
            <div class="stock-form-actions">
                <form action="{{ route('admin.products.stock.destroy', $stockAjustment) }}" method="POST"
                    onsubmit="return confirm('Delete this adjustment permanently?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="stock-btn stock-btn-outline stock-btn-danger">
                        <i class="ti ti-trash"></i> Delete
                    </button>
                </form>

                <a href="{{ route('admin.products.stock.edit', $stockAjustment) }}" class="stock-btn stock-btn-primary">
                    <i class="ti ti-edit"></i> Edit Adjustment
                </a>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
@endpush
