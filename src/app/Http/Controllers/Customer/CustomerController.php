<?php

namespace App\Http\Controllers\Customer;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Services\Customer\CustomerService;
use App\Http\Requests\Customer\CustomerStoreRequest;
use App\Http\Requests\Customer\CustomerUpdateRequest;

class CustomerController extends Controller
{
    public function __construct(
        private readonly CustomerService $customerService
    ) {}

    public function index(Request $request)
    {
        $customers = $this->customerService->getPagination($request, 15);

        return view('admin.customers.index', compact('customers'));
    }

    public function searchAjax(Request $request): JsonResponse
    {
        $keyword = trim($request->get('keyword') ?? '');
        $customers = $this->customerService->searchForPOS($request->get('keyword', ''));

        return response()->json($customers);
    }

    public function store(CustomerStoreRequest $request): JsonResponse|RedirectResponse
    {
        $customer = $this->customerService->createCustomer($request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'success'  => true,
                'customer' => $customer,
            ]);
        }

        return redirect()->back()->with('success', 'Customer created successfully.');
    }

    public function update(CustomerUpdateRequest $request, Customer $customer): RedirectResponse
    {
        $this->customerService->updateCustomer($customer, $request->validated());

        return redirect()->back()->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $this->customerService->deactivate($customer);

        return redirect()->back()->with('success', 'Customer removed successfully.');
    }
}
