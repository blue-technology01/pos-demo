<?php

namespace App\Http\Controllers\Sale;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PosController extends Controller
{
    // show category product on POS
    public function index(Request $request)
    {
        $categories = Category::where('status', 'active')
            ->orderBy('name')
            ->get(['code', 'name']);

        return view('cashier.pos.index', compact('categories'));
    }
}
