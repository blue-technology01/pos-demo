let cart = [];

const menuItems = [
    { id: 1, name: "Grilled Salmon", price: 28.50, category: "food" },
    { id: 2, name: "Chicken Burger", price: 16.00, category: "food" },
    { id: 3, name: "Caesar Salad", price: 12.50, category: "food" },
    { id: 4, name: "Coca Cola", price: 4.50, category: "beverage" },
    { id: 5, name: "Espresso", price: 5.00, category: "beverage" },
    { id: 6, name: "Chocolate Cake", price: 9.00, category: "dessert" },
    { id: 7, name: "Fruit Platter", price: 14.00, category: "dessert" },
    { id: 8, name: "Club Sandwich", price: 18.00, category: "room" },
];

function renderMenu(category = 'all') {
    const grid = document.getElementById('menuGrid');
    grid.innerHTML = '';

    const filtered = category === 'all'
        ? menuItems
        : menuItems.filter(item => item.category === category);

    filtered.forEach(item => {
        const div = document.createElement('div');
        div.className = 'menu-item';
        div.innerHTML = `
            <div class="item-info">
                <h4>${item.name}</h4>
                <span class="price">$${item.price.toFixed(2)}</span>
            </div>
        `;
        div.onclick = () => addToCart(item);
        grid.appendChild(div);
    });
}

function addToCart(item) {
    const existing = cart.find(i => i.id === item.id);
    if (existing) {
        existing.quantity = (existing.quantity || 1) + 1;
    } else {
        cart.push({ ...item, quantity: 1 });
    }
    renderCart();
}

function renderCart() {
    const container = document.getElementById('cartItems');
    container.innerHTML = '';

    let subtotal = 0;

    cart.forEach((item, index) => {
        const total = item.price * item.quantity;
        subtotal += total;

        const div = document.createElement('div');
        div.className = 'cart-item';
        div.innerHTML = `
            <div>
                <strong>${item.name}</strong><br>
                <small>$${item.price.toFixed(2)} × ${item.quantity}</small>
            </div>
            <div class="item-total">$${total.toFixed(2)}</div>
            <button class="remove-btn" onclick="removeFromCart(${index})">×</button>
        `;
        container.appendChild(div);
    });

    const tax = subtotal * 0.10;
    const total = subtotal + tax;

    document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
    document.getElementById('tax').textContent = '$' + tax.toFixed(2);
    document.getElementById('total').textContent = '$' + total.toFixed(2);
    document.getElementById('item-count').textContent = cart.length + ' items';
}

function removeFromCart(index) {
    cart.splice(index, 1);
    renderCart();
}

function clearOrder() {
    if (confirm('Clear current order?')) {
        cart = [];
        renderCart();
    }
}

function openPaymentModal() {
    if (cart.length === 0) return alert("Cart is empty!");
    document.getElementById('modal-total').textContent = document.getElementById('total').textContent;
    document.getElementById('paymentModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('paymentModal').style.display = 'none';
}

function processPayment(method) {
    alert(`Payment successful via ${method.toUpperCase()}!`);
    cart = [];
    renderCart();
    closeModal();
}

function newOrder() {
    if (confirm('Start a new order?')) {
        cart = [];
        renderCart();
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    renderMenu();

    // Category filters
    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            renderMenu(btn.dataset.category);
        });
    });
});
