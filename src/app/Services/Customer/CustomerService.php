<?php

namespace App\Services\Customer;

    use App\Models\Customer;
    use Illuminate\Http\Request;
    use Illuminate\Database\Eloquent\Collection;
    use Illuminate\Pagination\LengthAwarePaginator;

    class CustomerService
    {

        // get all customer with pagination
        public function getPagination(Request $request, int $perPage = 15): LengthAwarePaginator
        {
            $query = Customer::select('id', 'name', 'phone', 'email','status')
                ->where('status', 'active');

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
                });
            }

            return $query->latest()->paginate($perPage)->withQueryString();
        }

        // search active customers
        public function searchForPOS(?string $keyword = ''): Collection
        {
            $query = Customer::select('id', 'name', 'phone')
                ->where('status', 'active');

            if (!empty($keyword)) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', "%{$keyword}%")
                    ->orWhere('phone', 'LIKE', "%{$keyword}%");
                });
            }

            // if it null it will get first user 20
            return $query->orderBy('name')
                ->limit(20)
                ->get();
        }

        // get all active customers for POS UI
        public function getAllCustomers(): Collection
        {
            return Customer::select('id', 'name', 'phone','status')
                ->where('status', 'active')
                ->orderBy('name')
                ->get();
        }

        // create a new customer
        public function createCustomer(array $data): Customer
        {
            return Customer::create([
                'name'    => $data['name'],
                'phone'   => $data['phone'] ?? null,
                'email'   => $data['email'] ?? null,
                'address' => $data['address'] ?? null,
                'status'  => $data['status'] ?? Customer::STATUS_ACTIVE,
            ]);
        }

        // quick add customer from POS
        public function quickCreateCustomer(string $name, string $phone): Customer
        {
            return Customer::create([
                'name'  => $name,
                'phone' => $phone,
                'status' => 'active',
            ]);
        }

        // update customer
        public function updateCustomer(Customer $customer, array $data): bool
        {
            return $customer->update([
                'name'    => $data['name'],
                'phone'   => $data['phone'] ?? null,
                'email'   => $data['email'] ?? null,
                'address' => $data['address'] ?? null,
                'status'  => $data['status'] ?? $customer->status,
            ]);
        }

        // deactive a customer
        public function deactivate(Customer $customer): Customer
        {
            $customer->update(['status' => 'inactive']);

            return $customer->fresh();
        }
    }
