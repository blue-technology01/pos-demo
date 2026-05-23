@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/stock/update-stock.css') }}">
@endpush

{{-- Removed the trailing semi-colon here --}}
@section('title', 'Stock History')

@section('content')
    <div class="pm-wrapper">
        <div class="pm-search-box">
            <input type="text" placeholder="Search product..." id="product-search">
            <button class="btn-filter">Filter</button>
        </div>
        <div class="pm-table-wrap">
            <table class="pm-table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Stock Before</th>
                        <th>Qty Sold</th>
                        <th>Stock After</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Koka Kola</td>
                        <td>100</td>
                        <td>40</td>
                        <td>60</td>
                    </tr>
                    <tr>
                        <td>Sting</td>
                        <td>100</td>
                        <td>40</td>
                        <td>60</td>
                    </tr>
                    @for($i=0; $i < 10; $i++)
                        <tr>
                            <td>Beer</td>
                            <td>100</td>
                            <td>40</td>
                            <td>60</td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        <div class="pm-pagination">
            {{-- Left: result summary & Per Page Selector --}}
            <div class="pm-pagination__meta">
                <span class="pm-pagination__text">
                    Showing <strong>1</strong> - <strong>5</strong> of <strong>24</strong> results
                </span>
                <div class="pm-pagination__per-page">
                    <label for="per-page-select">Show:</label>
                    <select id="per-page-select" class="pm-pagination__select">
                        <option value="15">15</option>
                        <option value="24" selected>24</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>

            {{-- Right: page links --}}
            <div class="pm-pagination__links">
                <span class="pm-pagination__btn pm-pagination__btn--disabled">&laquo;</span>
                <span class="pm-pagination__btn pm-pagination__btn--active">1</span>
                <a href="#" class="pm-pagination__btn">2</a>
                <a href="#" class="pm-pagination__btn">3</a>
                <a href="#" class="pm-pagination__btn">4</a>
                <a href="#" class="pm-pagination__btn">5</a>
                <a href="#" class="pm-pagination__btn">&raquo;</a>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Your future search filtering JS can go here safely
    </script>
@endpush
