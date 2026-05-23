<!-- Payment Modal -->
<div id="paymentModal" class="hidden fixed inset-0 z-[10000] flex items-center justify-center bg-black/70">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl mx-4 overflow-hidden">
        <div class="grid grid-cols-1 lg:grid-cols-2 min-h-[580px]">

            <!-- LEFT: Receipt -->
            <div class="bg-[#F8F9FA] p-8 flex flex-col">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <div class="inline-flex items-center gap-2 bg-teal-700 text-white text-sm px-3 py-1 rounded-full">
                            <span class="font-mono">A4</span>
                        </div>
                        <h3 class="text-xl font-semibold mt-2">Ariel Hikmat</h3>
                        <p class="text-sm text-gray-500">Order #925 / Dine In</p>
                    </div>
                    <div class="text-right text-sm text-gray-500">
                        <p>Wed, July 12, 2023</p>
                        <p>06:12 PM</p>
                    </div>
                </div>

                <div class="flex-1">
                    <h4 class="font-medium mb-3 text-gray-700">Transaction Details</h4>
                    <div id="modal-cart-items" class="space-y-3">
                        <!-- Filled by JS -->
                    </div>
                </div>

                <div class="border-t pt-4 mt-auto">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Items (5)</span>
                            <span id="modal-subtotal">$73.79</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tax (5%)</span>
                            <span id="modal-tax">$3.65</span>
                        </div>
                    </div>
                    <div class="flex justify-between items-center mt-4 pt-4 border-t text-xl font-bold">
                        <span>Total</span>
                        <span id="modal-total" class="text-teal-700">$77.34</span>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Payment -->
            <div class="p-8 flex flex-col">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold">Payment</h2>
                    <button id="closePaymentModal" class="text-3xl leading-none text-gray-400 hover:text-gray-600">&times;</button>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-600 mb-2">Select a payment method</label>
                    <div class="border border-gray-300 rounded-xl p-3 flex items-center gap-3 cursor-pointer">
                        <i class="ti ti-cash text-2xl"></i>
                        <span class="font-medium">Cash</span>
                    </div>
                </div>

                <!-- Amount Display -->
                <div class="text-center mb-8">
                    <p class="text-gray-500 text-sm">Amount to Pay</p>
                    <div id="displayAmount" class="text-6xl font-bold text-gray-800 mt-2">$0.00</div>
                </div>

                <!-- Quick Amounts -->
                <div class="grid grid-cols-4 gap-3 mb-6">
                    <button class="quick-amount-btn bg-gray-100 hover:bg-gray-200 py-3 rounded-xl text-lg font-medium" data-amount="5">$5</button>
                    <button class="quick-amount-btn bg-gray-100 hover:bg-gray-200 py-3 rounded-xl text-lg font-medium" data-amount="10">$10</button>
                    <button class="quick-amount-btn bg-gray-100 hover:bg-gray-200 py-3 rounded-xl text-lg font-medium" data-amount="20">$20</button>
                    <button class="quick-amount-btn bg-gray-100 hover:bg-gray-200 py-3 rounded-xl text-lg font-medium" data-amount="50">$50</button>
                </div>

                <!-- Number Pad -->
                <div class="grid grid-cols-3 gap-2 mb-8">
                    <button class="num-key py-6 text-2xl font-medium rounded-2xl hover:bg-gray-100">1</button>
                    <button class="num-key py-6 text-2xl font-medium rounded-2xl hover:bg-gray-100">2</button>
                    <button class="num-key py-6 text-2xl font-medium rounded-2xl hover:bg-gray-100">3</button>
                    <button class="num-key py-6 text-2xl font-medium rounded-2xl hover:bg-gray-100">4</button>
                    <button class="num-key py-6 text-2xl font-medium rounded-2xl hover:bg-gray-100">5</button>
                    <button class="num-key py-6 text-2xl font-medium rounded-2xl hover:bg-gray-100">6</button>
                    <button class="num-key py-6 text-2xl font-medium rounded-2xl hover:bg-gray-100">7</button>
                    <button class="num-key py-6 text-2xl font-medium rounded-2xl hover:bg-gray-100">8</button>
                    <button class="num-key py-6 text-2xl font-medium rounded-2xl hover:bg-gray-100">9</button>
                    <button class="num-key py-6 text-2xl font-medium rounded-2xl hover:bg-gray-100">.</button>
                    <button class="num-key py-6 text-2xl font-medium rounded-2xl hover:bg-gray-100">0</button>
                    <button id="backspace" class="py-6 text-3xl hover:bg-gray-100 rounded-2xl">⌫</button>
                </div>

                <!-- Pay Now Button -->
                <button id="payNowBtn" class="w-full py-5 bg-yellow-400 hover:bg-yellow-500 text-black text-xl font-bold rounded-2xl transition">
                    Pay Now
                </button>
            </div>
        </div>
    </div>
</div>
