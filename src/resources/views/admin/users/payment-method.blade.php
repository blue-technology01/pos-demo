@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/user/payment-method.css') }}">
@endpush
@section('title', 'Payment Methods')
@section('content')
<div class="pm-wrapper">

    <div class="pm-header">
        <h2>Payment Methods</h2>
        <button class="btn-add" id="open-pm-modal">+ Add Method</button>
    </div>

    <div class="pm-table-wrap">
        <table class="pm-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Method Name</th>
                    <th>Code</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>1</td>
                    <td>Cash</td>
                    <td>cash</td>
                    <td><span class="status active">Active</span></td>
                    <td>
                        <button class="btn edit">
                            <i class="ti ti-edit"></i>
                        </button>
                        <button class="btn delete">
                             <i class="ti ti-trash" style="color:white"></i>
                        </button>
                    </td>
                </tr>
                @for($i = 0; $i < 10; $i++)
                    <tr>
                        <td>{{ $i + 2 }}</td>
                        <td>ABA</td>
                        <td>cash</td>
                        <td><span class="status active">Active</span></td>
                        <td>
                            <button class="btn edit">
                                <i class="ti ti-edit"></i>
                            </button>
                            <button class="btn delete">
                                <i class="ti ti-trash"></i>
                            </button>
                        </td>
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>
</div>

<div id="pm-dialog" title="Add New Payment Method" style="display:none;">
    <form id="pm-form" action="#" method="POST">
        @csrf
        <div class="form-group">
            <label for="pm_name">Method Name</label>
            <input type="text" id="pm_name" name="name" placeholder="e.g., ABA " required>
        </div>

        <div class="form-group">
            <label for="pm_code">Code</label>
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
    <script src="{{ asset('assets/js/dashboard/users/payment-method.js') }}"></script>
@endpush
