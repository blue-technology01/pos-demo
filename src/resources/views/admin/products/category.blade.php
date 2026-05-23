    @extends('layouts.app')

    @push('styles')
        <link rel="stylesheet" href="{{ asset('assets/css/dashboard/product/category.css') }}">
    @endpush

    @section('title', 'Dashboard Product Category')

    @section('content')
        <div class="unit-section">
            <div class="unit-section__header">
                <button type="button" id="open-pm-modal" class="unit-section__btn-add">
                    Add New Category
                </button>
            </div>

            <div class="unit-card">
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th class="col-id">ID</th>
                                <th>Category Name</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th class="col-actions">Action</th>
                            </tr>
                        </thead>
                        <tbody id="unit-table-body">
                            <tr data-unit-id="1">
                                <td><span class="unit-id-text">#01</span></td>
                                <td><span class="unit-name-text">Drink</span></td>
                                <td><span class="unit-badge">Good for all product</span></td>
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
                    <label for="pm_name">Category Name</label>
                    <input type="text" id="pm_name" name="name" placeholder="e.g., Drink " required>
                </div>

                <div class="form-group">
                    <label for="pm_code">Description</label>
                    <input type="text" id="pm_code" name="code" placeholder="e.g., cc">
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
        <script src="{{ asset('assets/js/dashboard/product/category.js') }}"></script>
    @endpush
