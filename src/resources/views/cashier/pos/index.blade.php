@extends('layouts.pos')  

@section('title', 'POS System')

@section('content')
<body class="font-sans bg-gray-50 text-gray-800 h-screen overflow-hidden">
 
<div class="flex flex-col h-screen">
 
  <!-- HEADER -->
  <header class="bg-white border-b border-gray-200 px-6 flex items-center justify-between h-16 flex-shrink-0">
    <div class="flex items-center gap-5">
      <div class="w-10 h-10 bg-blue-600 rounded-2xl flex items-center justify-center text-white font-bold text-sm tracking-tight">POS</div>
      <span class="text-xl font-bold text-gray-800">POS System</span>
      <div class="flex bg-gray-100 rounded-full p-1 gap-0.5">
        <button onclick="setMode(this)" class="mode-tab px-5 py-2 rounded-full text-sm font-semibold bg-white shadow-sm text-gray-800 transition-all">Retail</button>
        <button onclick="setMode(this)" class="mode-tab px-5 py-2 rounded-full text-sm font-medium text-gray-500 hover:text-gray-700 transition-all">Wholesale</button>
      </div>
    </div>
    <div class="flex items-center gap-2">
      <button class="flex items-center gap-2 px-4 py-2 text-sm text-gray-600 font-medium hover:bg-gray-100 rounded-full transition-colors">
        <i class="fa-solid fa-chart-bar text-xs"></i> Report
      </button>
      <button class="flex items-center gap-2 px-4 py-2 text-sm text-gray-600 font-medium hover:bg-gray-100 rounded-full transition-colors">
        <i class="fa-solid fa-user text-xs"></i> Customer
      </button>
      <div class="w-9 h-9 bg-gray-200 rounded-xl flex items-center justify-center cursor-pointer hover:bg-gray-300 transition-colors text-lg">👤</div>
    </div>
  </header>
 
  <div class="flex flex-1 overflow-hidden">
 
    <!-- PRODUCTS PANEL -->
    <div class="flex-1 flex flex-col overflow-hidden">
 
      <!-- Search -->
      <div class="px-5 py-3 bg-white border-b border-gray-200 flex-shrink-0">
        <div class="relative">
          <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
          <input id="search-input" type="text" oninput="renderProducts()"
            placeholder="Search items or scan barcode..."
            class="w-full bg-gray-100 border-0 rounded-full py-3 pl-11 pr-4 text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
        </div>
      </div>
 
      <!-- Categories -->
      <div class="px-5 py-3 bg-white border-b border-gray-200 flex-shrink-0 overflow-x-auto">
        <div id="cats-bar" class="flex gap-2 min-w-max"></div>
      </div>
 
      <!-- Grid -->
      <div class="flex-1 overflow-y-auto p-5">
        <div id="product-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4"></div>
      </div>
    </div>
 
    <!-- CART SIDEBAR -->
    <div class="w-96 bg-white border-l border-gray-200 flex flex-col flex-shrink-0">
 
      <!-- Order Info + Customer -->
      <div class="px-5 py-4 border-b border-gray-200 bg-gray-50 flex-shrink-0">
        <div class="flex justify-between items-center">
          <div>
            <div class="text-xs text-gray-400 font-medium uppercase tracking-wide">Order #</div>
          </div>
          <div id="item-count" class="bg-blue-600 text-white px-4 py-1.5 rounded-full text-xs font-semibold">0 item</div>
        </div>
 
        <!-- Customer row -->
        <div class="flex items-center gap-2 mt-3">
          <div class="flex-1 relative" id="cust-search-wrap">
            <i class="fa-solid fa-user absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
            <input id="cust-input" type="text" autocomplete="off"
              placeholder="Search customer / phone..."
              oninput="searchCustomer()" onfocus="showCustDropdown()"
              class="w-full border border-gray-200 rounded-xl py-2 pl-8 pr-3 text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:border-blue-500 transition-colors">
            <div id="cust-dropdown" class="hidden absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg z-20 overflow-hidden"></div>
          </div>
          <button onclick="openAddCustomer()"
            class="w-9 h-9 flex-shrink-0 flex items-center justify-center border border-gray-200 rounded-xl text-gray-500 hover:border-blue-500 hover:text-blue-600 transition-colors">
            <i class="fa-solid fa-plus text-sm"></i>
          </button>
          <button class="flex-shrink-0 flex items-center gap-1.5 px-3 py-2 border border-gray-200 rounded-xl text-xs font-medium text-gray-600 hover:border-blue-500 hover:text-blue-600 transition-colors">
            <i class="fa-solid fa-barcode text-xs"></i> Scan
          </button>
        </div>
        <div id="cust-chip-wrap" class="hidden mt-2"></div>
      </div>
 
      <!-- Cart Items -->
      <div id="cart-items-wrap" class="flex-1 overflow-y-auto px-4 py-3">
        <div id="cart-empty" class="h-full flex flex-col items-center justify-center text-gray-400 text-center">
          <i class="fa-solid fa-cart-shopping text-5xl mb-3"></i>
          <p class="font-semibold text-sm">Cart is empty</p>
          <span class="text-xs mt-1">Add some products from the left</span>
        </div>
      </div>
 
      <!-- Totals -->
      <div class="px-5 py-4 border-t border-gray-200 bg-gray-50 flex-shrink-0">
        <div class="space-y-2">
          <div class="flex justify-between text-sm">
            <span class="text-gray-500">Subtotal</span>
            <span id="t-subtotal" class="font-medium text-gray-800">$0.00</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-gray-500">Discount</span>
            <span id="t-discount" class="font-medium text-green-600">-$0.00</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-gray-500">Tax (10%)</span>
            <span id="t-tax" class="font-medium text-gray-800">$0.00</span>
          </div>
          <div class="flex justify-between text-xl font-bold pt-2 border-t border-gray-300">
            <span>Total</span>
            <span id="t-total" class="text-blue-600">$0.00</span>
          </div>
        </div>
        <input id="discount-input" type="number" min="0" max="100" oninput="updateTotals()"
          placeholder="Discount %"
          class="w-full mt-3 text-center py-2.5 border border-gray-200 rounded-2xl text-sm bg-white focus:outline-none focus:border-blue-500 transition-colors">
      </div>
 
      <!-- Payment Methods -->
      <div class="px-5 py-4 border-t border-gray-200 flex-shrink-0">
        <div class="grid grid-cols-4 gap-2 mb-4">
          <button onclick="selectPayment(this,'cash')"
            class="pay-method aspect-square rounded-2xl flex flex-col items-center justify-center gap-1 text-xs font-semibold bg-red-50 text-red-500 border-2 border-red-300 scale-105 shadow-md transition-all">
            <i class="fa-solid fa-money-bill-wave text-xl"></i>Cash
          </button>
          <button onclick="selectPayment(this,'aba')"
            class="pay-method aspect-square rounded-2xl flex flex-col items-center justify-center gap-1 text-xs font-semibold bg-blue-50 text-blue-500 border border-blue-200 opacity-70 hover:opacity-100 transition-all">
            <i class="fa-solid fa-qrcode text-xl"></i>ABA
          </button>
          <button onclick="selectPayment(this,'wing')"
            class="pay-method aspect-square rounded-2xl flex flex-col items-center justify-center gap-1 text-xs font-semibold bg-emerald-50 text-emerald-500 border border-emerald-200 opacity-70 hover:opacity-100 transition-all">
            <i class="fa-solid fa-wallet text-xl"></i>Wing
          </button>
          <button onclick="selectPayment(this,'card')"
            class="pay-method aspect-square rounded-2xl flex flex-col items-center justify-center gap-1 text-xs font-semibold bg-purple-50 text-purple-500 border border-purple-200 opacity-70 hover:opacity-100 transition-all">
            <i class="fa-solid fa-credit-card text-xl"></i>Card
          </button>
        </div>
        <button id="process-btn" onclick="processPayment()" disabled
          class="w-full py-4 from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-black-900 font-bold rounded-xl shadow-lg transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100">
          <i class="fa-solid fa-lock mr-2 text-sm"></i>Process Payment
        </button>
      </div>    
    </div>
  </div>
</div>
 
<!-- ADD CUSTOMER MODAL -->
<div id="modal-add-customer" class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center opacity-0 pointer-events-none transition-opacity duration-200">
  <div class="bg-white rounded-3xl p-7 w-80 shadow-2xl transition-transform duration-200 scale-95">
    <div class="flex justify-between items-center mb-5">
      <h2 class="text-lg font-bold text-gray-800">Add New Guest</h2>
      <button onclick="closeModal('modal-add-customer')" class="text-gray-400 hover:text-gray-700 text-2xl leading-none">&times;</button>
    </div>
    <div class="mb-4">
      <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Name</label>
      <input id="new-cust-name" type="text" placeholder="Customer name"
        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
    </div>
    <div class="mb-6">
      <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Phone</label>
      <input id="new-cust-phone" type="text" placeholder="Phone number"
        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
    </div>
    <div class="flex gap-3">
      <button onclick="closeModal('modal-add-customer')"
        class="flex-1 py-2.5 border border-gray-200 rounded-xl text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">Cancel</button>
      <button onclick="addCustomer()"
        class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold transition-colors">Add</button>
    </div>
  </div>
</div>
 
<!-- CASH PAYMENT MODAL -->
<div id="modal-cash" class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center opacity-0 pointer-events-none transition-opacity duration-200">
  <div class="bg-white rounded-3xl p-7 w-80 shadow-2xl transition-transform duration-200 scale-95">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-lg font-bold text-gray-800">Cash Payment</h2>
      <button onclick="closeModal('modal-cash')" class="text-gray-400 hover:text-gray-700 text-2xl leading-none">&times;</button>
    </div>
    <div class="text-center mb-5">
      <div class="text-xs text-gray-400 font-medium">Total Amount</div>
      <div id="cash-total-display" class="text-4xl font-extrabold text-blue-600 tracking-tight mt-1">$0.00</div>
    </div>
    <div class="mb-3">
      <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Cash Received</label>
      <input id="cash-received" type="number" placeholder="0.00" oninput="calcChange()"
        class="w-full border-2 border-blue-400 rounded-xl px-4 py-3 text-xl font-bold text-center focus:outline-none focus:border-blue-600 transition-colors">
    </div>
    <div class="grid grid-cols-4 gap-2 mb-4">
      <button onclick="quickCash(1)"   class="py-2 border border-gray-200 rounded-xl text-sm font-semibold text-gray-700 hover:border-blue-400 hover:text-blue-600 hover:bg-blue-50 transition-all">$1</button>
      <button onclick="quickCash(5)"   class="py-2 border border-gray-200 rounded-xl text-sm font-semibold text-gray-700 hover:border-blue-400 hover:text-blue-600 hover:bg-blue-50 transition-all">$5</button>
      <button onclick="quickCash(10)"  class="py-2 border border-gray-200 rounded-xl text-sm font-semibold text-gray-700 hover:border-blue-400 hover:text-blue-600 hover:bg-blue-50 transition-all">$10</button>
      <button onclick="quickCash(20)"  class="py-2 border border-gray-200 rounded-xl text-sm font-semibold text-gray-700 hover:border-blue-400 hover:text-blue-600 hover:bg-blue-50 transition-all">$20</button>
      <button onclick="quickCash(50)"  class="py-2 border border-gray-200 rounded-xl text-sm font-semibold text-gray-700 hover:border-blue-400 hover:text-blue-600 hover:bg-blue-50 transition-all">$50</button>
      <button onclick="quickCash(100)" class="py-2 border border-gray-200 rounded-xl text-sm font-semibold text-gray-700 hover:border-blue-400 hover:text-blue-600 hover:bg-blue-50 transition-all">$100</button>
      <button onclick="quickExact()"   class="py-2 border border-gray-200 rounded-xl text-sm font-semibold text-gray-700 hover:border-blue-400 hover:text-blue-600 hover:bg-blue-50 transition-all">Exact</button>
      <button onclick="quickCash(0)"   class="py-2 border border-gray-200 rounded-xl text-sm font-semibold text-red-400 hover:bg-red-50 hover:border-red-300 transition-all">Clear</button>
    </div>
    <div class="flex justify-between items-center bg-gray-50 rounded-2xl px-4 py-3 mb-5">
      <span class="text-sm font-medium text-gray-600">Change</span>
      <span id="change-display" class="text-2xl font-extrabold text-emerald-600">$0.00</span>
    </div>
    <div class="flex gap-3">
      <button onclick="closeModal('modal-cash')"
        class="flex-1 py-2.5 border border-gray-200 rounded-xl text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">Cancel</button>
      <button id="confirm-cash-btn" onclick="confirmCash()"
        class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold transition-colors">Confirm</button>
    </div>
  </div>
</div>
 
<!-- QR / CARD MODAL -->
<div id="modal-qr" class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center opacity-0 pointer-events-none transition-opacity duration-200">
  <div class="bg-white rounded-3xl p-7 w-80 shadow-2xl text-center transition-transform duration-200 scale-95">
    <div class="flex justify-between items-center mb-5">
      <h2 id="qr-title" class="text-lg font-bold text-gray-800">Scan to Pay</h2>
      <button onclick="closeModal('modal-qr')" class="text-gray-400 hover:text-gray-700 text-2xl leading-none">&times;</button>
    </div>
    <div class="w-44 h-44 mx-auto mb-4 bg-gray-100 rounded-2xl flex items-center justify-center">
      <i class="fa-solid fa-qrcode text-7xl text-gray-300"></i>
    </div>
    <div id="qr-amount" class="text-3xl font-extrabold text-blue-600 mb-2">$0.00</div>
    <p class="text-xs text-gray-400 mb-6">Scan the QR code to complete payment</p>
    <div class="flex gap-3">
      <button onclick="closeModal('modal-qr')"
        class="flex-1 py-2.5 border border-gray-200 rounded-xl text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">Cancel</button>
      <button onclick="confirmQR()"
        class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold transition-colors">Confirm Paid</button>
    </div>
  </div>
</div>
 
<!-- SUCCESS MODAL -->
<div id="modal-success" class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center opacity-0 pointer-events-none transition-opacity duration-200">
  <div class="bg-white rounded-3xl p-8 w-80 shadow-2xl text-center transition-transform duration-200 scale-95">
    <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
      <i class="fa-solid fa-check text-3xl text-emerald-600"></i>
    </div>
    <h2 class="text-2xl font-extrabold text-gray-800 mb-1">Payment Complete!</h2>
    <p id="success-sub" class="text-sm text-gray-500 mb-6">Thank you for your purchase.</p>
    <button onclick="newOrder()"
      class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl text-sm transition-colors">
      New Order
    </button>
  </div>
</div>

@endsection

@push('scripts')
<script>
    const PRODUCTS = [
  {id:1,  name:"Boba Milk Tea",    price:12.20, icon:"🧋", cat:"Boba",  sub:"Classic",    stock:40, bg:"bg-cyan-50"},
  {id:2,  name:"Brown Sugar Boba", price:13.50, icon:"🧋", cat:"Boba",  sub:"Signature",  stock:28, bg:"bg-sky-50"},
  {id:3,  name:"Taro Boba",        price:12.20, icon:"🟣", cat:"Boba",  sub:"Taro blend", stock:15, bg:"bg-purple-50"},
  {id:4,  name:"Matcha Boba",      price:14.00, icon:"🍵", cat:"Boba",  sub:"Premium",    stock:22, bg:"bg-green-50"},
  {id:5,  name:"Strawberry Boba",  price:13.00, icon:"🍓", cat:"Boba",  sub:"Fruity",     stock:18, bg:"bg-rose-50"},
  {id:6,  name:"Orange Juice",     price:4.50,  icon:"🍊", cat:"Drink", sub:"Fresh",      stock:30, bg:"bg-orange-50"},
  {id:7,  name:"Lemon Soda",       price:3.50,  icon:"🍋", cat:"Drink", sub:"Sparkling",  stock:25, bg:"bg-yellow-50"},
  {id:8,  name:"Watermelon Juice", price:5.00,  icon:"🍉", cat:"Drink", sub:"Fresh",      stock:12, bg:"bg-red-50"},
  {id:9,  name:"Croissant",        price:3.80,  icon:"🥐", cat:"Food",  sub:"Buttery",    stock:20, bg:"bg-amber-50"},
  {id:10, name:"Sandwich",         price:7.50,  icon:"🥪", cat:"Food",  sub:"Club",       stock:14, bg:"bg-amber-50"},
  {id:11, name:"Cheesecake",       price:5.50,  icon:"🍰", cat:"Food",  sub:"NY Style",   stock:8,  bg:"bg-pink-50"},
  {id:12, name:"Muffin",           price:3.20,  icon:"🧁", cat:"Food",  sub:"Chocolate",  stock:35, bg:"bg-amber-50"},
];
 
const CUSTOMERS = [
  {id:1, name:"Sokha Meas",  phone:"012-345-678"},
  {id:2, name:"Dara Chan",   phone:"017-234-567"},
  {id:3, name:"Vanna Keo",   phone:"011-987-654"},
  {id:4, name:"Bopha Lim",   phone:"015-432-100"},
];
 
const CATS = ["All", ...new Set(PRODUCTS.map(p => p.cat))];
let cart = {}, activeCat = "All", payMethod = "cash", grandTotal = 0, orderSeq = 1;
 
function init() {
  renderCats(); renderProducts(); setOrderNum();
  document.addEventListener('click', e => {
    if (!document.getElementById('cust-search-wrap').contains(e.target))
      document.getElementById('cust-dropdown').classList.add('hidden');
  });
}
 
function setOrderNum() {
  const d = new Date();
  const ds = d.getFullYear() + String(d.getMonth()+1).padStart(2,'0') + String(d.getDate()).padStart(2,'0');
  document.getElementById('order-num').textContent = `POS-${ds}-${String(orderSeq).padStart(3,'0')}`;
}
 
function renderCats() {
  document.getElementById('cats-bar').innerHTML = CATS.map(c => `
    <button onclick="setCat('${c}')"
      class="whitespace-nowrap px-5 py-2 rounded-full text-sm font-semibold transition-all
        ${c === activeCat ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'}">
      ${c}
    </button>`).join('');
}
function setCat(c) { activeCat = c; renderCats(); renderProducts(); }
 
function renderProducts() {
  const q = document.getElementById('search-input').value.toLowerCase();
  const list = PRODUCTS.filter(p => (activeCat === 'All' || p.cat === activeCat) && p.name.toLowerCase().includes(q));
  document.getElementById('product-grid').innerHTML = list.map(p => `
    <div onclick="addToCart(${p.id})"
      class="bg-white border-2 border-gray-100 rounded-3xl overflow-hidden cursor-pointer hover:border-blue-400 hover:shadow-lg transition-all duration-150 active:scale-95 select-none">
      <div class="h-32 ${p.bg} flex items-center justify-center text-5xl">${p.icon}</div>
      <div class="p-3">
        <div class="font-semibold text-sm text-gray-800 leading-tight">${p.name}</div>
        <div class="text-xs text-gray-400 mt-0.5">${p.sub}</div>
        <div class="flex justify-between items-end mt-2">
          <div>
            <div class="text-base font-bold text-gray-900">$${p.price.toFixed(2)}</div>
            <div class="text-xs text-emerald-600 font-medium">${p.stock} in stock</div>
          </div>
          <span class="text-xs bg-emerald-100 text-emerald-700 px-2.5 py-1 rounded-full font-semibold">In Stock</span>
        </div>
      </div>
    </div>`).join('');
}
 
function addToCart(id) {
  const p = PRODUCTS.find(x => x.id === id);
  if (!p) return;
  cart[id] ? cart[id].qty++ : (cart[id] = {...p, qty: 1});
  renderCart();
}
function changeQty(id, delta) {
  if (!cart[id]) return;
  cart[id].qty += delta;
  if (cart[id].qty <= 0) delete cart[id];
  renderCart();
}
function removeItem(id) { delete cart[id]; renderCart(); }
 
function renderCart() {
  const items = Object.values(cart);
  const wrap = document.getElementById('cart-items-wrap');
  const empty = document.getElementById('cart-empty');
  if (items.length === 0) {
    wrap.innerHTML = '';
    empty.style.display = '';
    wrap.appendChild(empty);
    document.getElementById('process-btn').disabled = true;
  } else {
    empty.style.display = 'none';
    wrap.innerHTML = items.map(item => `
      <div class="flex items-center gap-3 py-2.5 border-b border-gray-100 last:border-0">
        <div class="w-11 h-11 rounded-xl bg-gray-100 flex items-center justify-center text-2xl flex-shrink-0">${item.icon}</div>
        <div class="flex-1 min-w-0">
          <div class="text-sm font-semibold text-gray-800 truncate">${item.name}</div>
          <div class="text-xs text-gray-400">$${item.price.toFixed(2)} each</div>
        </div>
        <div class="flex items-center gap-1.5">
          <button onclick="changeQty(${item.id},-1)"
            class="w-7 h-7 rounded-lg border border-gray-200 flex items-center justify-center text-gray-600 hover:border-blue-400 hover:text-blue-600 transition-colors font-bold">−</button>
          <span class="text-sm font-bold w-5 text-center">${item.qty}</span>
          <button onclick="changeQty(${item.id},1)"
            class="w-7 h-7 rounded-lg border border-gray-200 flex items-center justify-center text-gray-600 hover:border-blue-400 hover:text-blue-600 transition-colors font-bold">+</button>
        </div>
        <div class="text-sm font-bold text-gray-800 w-14 text-right">$${(item.price*item.qty).toFixed(2)}</div>
        <button onclick="removeItem(${item.id})"
          class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-300 hover:text-red-500 hover:bg-red-50 transition-colors">
          <i class="fa-solid fa-times text-xs"></i>
        </button>
      </div>`).join('');
    document.getElementById('process-btn').disabled = false;
  }
  const count = items.reduce((s,i) => s + i.qty, 0);
  document.getElementById('item-count').textContent = `${count} item${count !== 1 ? 's' : ''}`;
  updateTotals();
}
 
function updateTotals() {
  const items = Object.values(cart);
  const subtotal = items.reduce((s,i) => s + i.price * i.qty, 0);
  const discPct = Math.min(100, Math.max(0, parseFloat(document.getElementById('discount-input').value) || 0));
  const discount = subtotal * discPct / 100;
  const afterDisc = subtotal - discount;
  const tax = afterDisc * 0.10;
  grandTotal = afterDisc + tax;
  document.getElementById('t-subtotal').textContent = `$${subtotal.toFixed(2)}`;
  document.getElementById('t-discount').textContent = `-$${discount.toFixed(2)}`;
  document.getElementById('t-tax').textContent = `$${tax.toFixed(2)}`;
  document.getElementById('t-total').textContent = `$${grandTotal.toFixed(2)}`;
}
 
function selectPayment(el, method) {
  payMethod = method;
  document.querySelectorAll('.pay-method').forEach(b => {
    b.classList.remove('border-2', 'scale-105', 'shadow-md', 'opacity-100');
    b.classList.add('border', 'opacity-70');
  });
  el.classList.remove('border', 'opacity-70');
  el.classList.add('border-2', 'scale-105', 'shadow-md', 'opacity-100');
}
 
function processPayment() {
  if (!Object.keys(cart).length) return;
  if (payMethod === 'cash') {
    document.getElementById('cash-total-display').textContent = `$${grandTotal.toFixed(2)}`;
    document.getElementById('cash-received').value = '';
    document.getElementById('change-display').textContent = '$0.00';
    document.getElementById('change-display').className = 'text-2xl font-extrabold text-emerald-600';
    openModal('modal-cash');
  } else {
    const titles = {aba:'Scan ABA QR Code', wing:'Scan Wing QR Code', card:'Swipe / Tap Card'};
    document.getElementById('qr-title').textContent = titles[payMethod] || 'Scan to Pay';
    document.getElementById('qr-amount').textContent = `$${grandTotal.toFixed(2)}`;
    openModal('modal-qr');
  }
}
 
function calcChange() {
  const received = parseFloat(document.getElementById('cash-received').value) || 0;
  const change = received - grandTotal;
  const el = document.getElementById('change-display');
  el.textContent = `$${Math.abs(change).toFixed(2)}`;
  el.className = change < 0 ? 'text-2xl font-extrabold text-red-500' : 'text-2xl font-extrabold text-emerald-600';
  document.getElementById('confirm-cash-btn').disabled = change < 0;
}
function quickCash(val) {
  if (val === 0) { document.getElementById('cash-received').value = ''; calcChange(); return; }
  const cur = parseFloat(document.getElementById('cash-received').value) || 0;
  document.getElementById('cash-received').value = (cur + val).toFixed(2);
  calcChange();
}
function quickExact() { document.getElementById('cash-received').value = grandTotal.toFixed(2); calcChange(); }
function confirmCash() {
  const change = (parseFloat(document.getElementById('cash-received').value) || 0) - grandTotal;
  if (change < 0) return;
  closeModal('modal-cash');
  showSuccess(`Change: $${change.toFixed(2)}`);
}
function confirmQR() { closeModal('modal-qr'); showSuccess(`Payment via ${payMethod.toUpperCase()} received.`); }
function showSuccess(msg) { document.getElementById('success-sub').textContent = msg; openModal('modal-success'); }
function newOrder() {
  cart = {}; orderSeq++;
  document.getElementById('discount-input').value = '';
  closeModal('modal-success'); setOrderNum(); renderCart(); updateTotals();
}
 
function searchCustomer() {
  const q = document.getElementById('cust-input').value.toLowerCase();
  renderCustDropdown(CUSTOMERS.filter(c => c.name.toLowerCase().includes(q) || c.phone.includes(q)));
}
function showCustDropdown() { renderCustDropdown(CUSTOMERS); }
function renderCustDropdown(list) {
  const dd = document.getElementById('cust-dropdown');
  dd.innerHTML = list.length
    ? list.map(c => `
        <div onclick="selectCustomer(${c.id})"
          class="flex items-center gap-2 px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50 cursor-pointer">
          <i class="fa-solid fa-user-circle text-gray-400 text-xs"></i>
          <span class="font-medium">${c.name}</span>
          <span class="ml-auto text-gray-400 text-xs">${c.phone}</span>
        </div>`).join('')
    : `<div class="px-3 py-2.5 text-sm text-gray-400">No customers found</div>`;
  dd.classList.remove('hidden');
}
function selectCustomer(id) {
  const c = CUSTOMERS.find(x => x.id === id);
  document.getElementById('cust-input').value = '';
  document.getElementById('cust-dropdown').classList.add('hidden');
  const wrap = document.getElementById('cust-chip-wrap');
  wrap.classList.remove('hidden');
  wrap.innerHTML = `
    <div class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-600 rounded-full px-3 py-1 text-xs font-semibold">
      <i class="fa-solid fa-user text-xs"></i>${c.name} — ${c.phone}
      <button onclick="clearCustomer()" class="ml-1 text-blue-400 hover:text-blue-700">
        <i class="fa-solid fa-times text-xs"></i>
      </button>
    </div>`;
}
function clearCustomer() {
  document.getElementById('cust-chip-wrap').classList.add('hidden');
  document.getElementById('cust-chip-wrap').innerHTML = '';
}
function openAddCustomer() { openModal('modal-add-customer'); }
function addCustomer() {
  const name = document.getElementById('new-cust-name').value.trim();
  const phone = document.getElementById('new-cust-phone').value.trim();
  if (!name) return;
  const newC = {id: Date.now(), name, phone};
  CUSTOMERS.push(newC);
  selectCustomer(newC.id);
  document.getElementById('new-cust-name').value = '';
  document.getElementById('new-cust-phone').value = '';
  closeModal('modal-add-customer');
}
 
function openModal(id) {
  const el = document.getElementById(id);
  el.classList.remove('opacity-0', 'pointer-events-none');
  el.classList.add('opacity-100');
  el.querySelector('div').classList.remove('scale-95');
  el.querySelector('div').classList.add('scale-100');
}
function closeModal(id) {
  const el = document.getElementById(id);
  el.classList.add('opacity-0', 'pointer-events-none');
  el.classList.remove('opacity-100');
  el.querySelector('div').classList.add('scale-95');
  el.querySelector('div').classList.remove('scale-100');
}
function setMode(el) {
  document.querySelectorAll('.mode-tab').forEach(b => {
    b.classList.remove('bg-white', 'shadow-sm', 'text-gray-800', 'font-semibold');
    b.classList.add('text-gray-500', 'font-medium');
  });
  el.classList.add('bg-white', 'shadow-sm', 'text-gray-800', 'font-semibold');
  el.classList.remove('text-gray-500', 'font-medium');
}
init();
</script>
@endpush