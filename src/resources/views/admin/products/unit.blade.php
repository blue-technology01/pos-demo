@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/product/unit.css') }}">
@endpush

@section('title', 'Dashboard Product Units')

@section('content')
    <div class="unit-section">
        <div class="unit-section__header">
            <div>
                <h1 class="unit-section__title">Measurement Units</h1>
            </div>
            <button type="button" id="open-pm-modal" class="unit-section__btn-add">
                + Add New Unit
            </button>
        </div>

        <div class="unit-card">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th class="col-id">ID</th>
                            <th>Unit Name</th>
                            <th>Short Code</th>
                            <th>Allow Decimals</th>
                            <th>Status</th>
                            <th class="col-actions">Action</th>
                        </tr>
                    </thead>
                    <tbody id="unit-table-body">
                        <tr data-unit-id="1">
                            <td><span class="unit-id-text">#01</span></td>
                            <td><span class="unit-name-text">Pieces</span></td>
                            <td><span class="unit-badge">PCS</span></td>
                            <td><span class="decimal-allowed--no">No (Whole Numbers Only)</span></td>
                            <td><span class="status-badge status-badge--active">Active</span></td>
                            <td>
                                <div class="action-group">
                                    <button class="btn-action" title="Edit Unit" data-action="edit">
                                        <i data-lucide="edit-3"></i>
                                    </button>
                                    <button class="btn-action btn-action--delete" title="Delete Unit" data-action="delete">
                                        <i data-lucide="trash-2"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr data-unit-id="2">
                            <td><span class="unit-id-text">#02</span></td>
                            <td><span class="unit-name-text">Kilograms</span></td>
                            <td><span class="unit-badge">KG</span></td>
                            <td><span class="decimal-allowed--yes">Yes (e.g., 1.50 kg)</span></td>
                            <td><span class="status-badge status-badge--active">Active</span></td>
                            <td>
                                <div class="action-group">
                                    <button class="btn-action" title="Edit Unit" data-action="edit">
                                        <i data-lucide="edit-3"></i>
                                    </button>
                                    <button class="btn-action btn-action--delete" title="Delete Unit" data-action="delete">
                                        <i data-lucide="trash-2"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- popup form unit --}}
    <div id="pm-dialog" title="Add Unit" style="display:none;">
        <form id="pm-form" action="#" method="POST">
            @csrf
            <div class="form-group">
                <label for="pm_name">Unit Name</label>
                <input type="text" id="pm_name" name="name" placeholder="e.g., ABA " required>
            </div>

            <div class="form-group">
                <label for="pm_code">Code</label>
                <input type="text" id="pm_code" name="code" placeholder="e.g., cc" required>
            </div>
            <div class="form-group">
                <label for="pm_code">Allow Decimals</label>
                <input type="text" id="pm_code" name="code" placeholder="e.g., cc" required>
            </div>
            <div class="form-group">
                <label for="pm_status">Status</label>
                <select id="pm_status" name="status">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/dashboard/product/unit.js') }}"></script>
@endpush
