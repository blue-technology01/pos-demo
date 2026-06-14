<?php

namespace App\Services\Customer;

    use App\Models\Customer;
    use Illuminate\Http\Request;
    use Illuminate\Pagination\LengthAwarePaginator;
    use Illuminate\Database\Eloquent\Collection;

    class CustomerService
    {
        /**
         * Get all customers with pagination, search, and filter.
         */
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

        /**
         * Search active customers via AJAX.
        */
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

            // ប្រសិនបើ $keyword ទំនេរ (null/'') វានឹងរត់ទាញយកអតិថិជន ២០ នាក់ដំបូងមកបង្ហាញភ្លាមៗ
            return $query->orderBy('name')
                ->limit(20)
                ->get();
        }


        /**
         * Get all active customers for POS UI.
         */
        public function getAllCustomers(): Collection
        {
            return Customer::select('id', 'name', 'phone','status')
                ->where('status', 'active')
                ->orderBy('name')
                ->get();
        }

        /**
         * Create a new customer.
         */
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

        /**
         * Quick add customer from POS.
         */
        public function quickCreateCustomer(string $name, string $phone): Customer
        {
            return Customer::create([
                'name'  => $name,
                'phone' => $phone,
                'status' => 'active',
            ]);
        }

        /**
         * Update an existing customer.
         */
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

        /**
         * Deactivate a customer.
         */
        public function deactivate(Customer $customer): Customer
        {
            $customer->update(['status' => 'inactive']);

            return $customer->fresh();
        }
    }
