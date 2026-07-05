<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Customer Display</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/customer-display.css') }}">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />

<style>

</style>

</head>
<body>

<div class="body">
  {{-- ── Promo slider ── --}}
  <div class="slider-panel" id="slider-panel">
    <div class="slider-track" id="slider-track">
      <div class="slide slide--promo-1" id="slide-promo-1">
        <img class="slide__img" src="{{ asset('assets/images/customer-ui-1.png') }}" alt="Welcome">
        <div class="slide__content">
          <div class="slide__title">Welcome!</div>
          <div class="slide__subtitle">Thank you for shopping with us today. We appreciate your visit.</div>
          <div class="slide__badge">Special offers available</div>
        </div>
      </div>

      <div class="slide slide--promo-2" id="slide-promo-2">
        <img class="slide__img" src="{{ asset('assets/images/selection.jpg') }}" alt="Member rewards">
        <div class="slide__content">
          <div class="slide__title">Member rewards</div>
          <div class="slide__subtitle">Earn points with every purchase. Ask our cashier about our loyalty program!</div>
          <div class="slide__badge">Join today — it's free</div>
        </div>
      </div>

      <div class="slide slide--promo-3" id="slide-promo-3">
        <img class="slide__img" src="{{ asset('assets/images/qr-code-new.jpg') }}" alt="Quality guaranteed">
        <div class="slide__content">
          <div class="slide__title">Quality guaranteed</div>
          <div class="slide__subtitle">All products are carefully selected. Your satisfaction is our priority.</div>
          <div class="slide__badge">100% satisfaction</div>
        </div>
      </div>

      {{-- Clone of slide 1, used only so the scroll can loop seamlessly back to the start --}}
      <div class="slide slide--promo-1-clone" aria-hidden="true">
        <img class="slide__img" src="{{ asset('assets/images/customer-ui-1.png') }}" alt="">
        <div class="slide__content">
          <div class="slide__title">Welcome!</div>
          <div class="slide__subtitle">Thank you for shopping with us today. We appreciate your visit.</div>
          <div class="slide__badge">Special offers available</div>
        </div>
      </div>
    </div>
    <div class="qr-overlay" id="qr-overlay">
      <div class="qr-overlay__box">
        <div class="qr-overlay__label">Scan to pay</div>
        <div class="qr-overlay__code" id="qr-code-container">
            <img id="qr-code-img" class="width: 100%; height: 100%;" src="{{ asset('assets/images/qr.png') }}" alt="QR code">
        </div>
        <div class="qr-overlay__amount" id="qr-amount">$0.00</div>
        <div class="qr-overlay__hint">Open your banking app and scan this code</div>
      </div>
    </div>
  </div>

  <div class="info-panel" id="info-panel">

    <div class="info-header">
      <div class="info-header__avatar" id="hdr-avatar">
        <img src="{{ asset('assets/images/logo.png') }}" alt="Store logo">
      </div>
      <div class="info-header__details">
        <div class="info-header__label">Customer</div>
        <div class="info-header__name" id="hdr-name">Walk-in Customer</div>
      </div>
    </div>

    <div class="info-waiting" id="info-waiting">
        <div class="info-waiting__icon"style="font-size: 48px; color: #888;">
            <span class="material-symbols-outlined">shopping_cart</span>
        </div>
      <div class="info-waiting__text">No items yet</div>
      <div class="info-waiting__sub">Items will appear here when added</div>
    </div>

    {{-- Order content --}}
    <div id="order-content" style="display:none; flex-direction:column; flex:1; overflow:hidden;">
      <div class="order-header">
        <div class="order-header__label">Order summary</div>
        <div class="order-header__count" id="order-count">0 items</div>
      </div>
      <div class="order-items" id="order-items"></div>
      <div class="totals">
        <div class="totals__row"><span>Subtotal</span><span id="t-subtotal">$0.00</span></div>
        <div class="totals__row"><span>Tax (10%)</span><span id="t-tax">$0.00</span></div>
        <div class="totals__row"><span>Discount</span><span id="t-discount">$0.00</span></div>
        <div class="totals__row totals__row--grand">
          <span>Total due</span>
          <span id="t-total">$0.00</span>
        </div>
      </div>
    </div>

    {{-- Thank you state --}}
    <div class="thankyou" id="thankyou">
      <div class="thankyou__icon"></div>
      <div class="thankyou__title">Thank you!</div>
      <div class="thankyou__sub">Payment received. See you next time!</div>
    </div>

  </div>
</div>

<script>
  const fmt = v => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(parseFloat(v) || 0);

  const promoSlides = ['slide-promo-1', 'slide-promo-2', 'slide-promo-3'];
  let promoIndex = 0;
  let promoTimer = null;
  let hasItems   = false;

  // ── Promo slider (always running, except while QR overlay is shown) ──────
  // Slides sit side by side in #slider-track; we slide the whole track left
  // by one slide-width at a time for a smooth scrolling carousel effect.
  const sliderTrack = document.getElementById('slider-track');

  function startPromoSlider() {
    stopPromoSlider();
    promoTimer = setInterval(() => {
      goToSlide(promoIndex + 1);
    }, 4000); // time each slide stays fully in view before scrolling to the next
  }

  function stopPromoSlider() {
    if (promoTimer) { clearInterval(promoTimer); promoTimer = null; }
  }

  function goToSlide(nextIndex) {
    const dots = document.querySelectorAll('.dot');
    dots[promoIndex % promoSlides.length]?.classList.remove('active');

    promoIndex = nextIndex;
    sliderTrack.style.transform = `translateX(-${promoIndex * 100}%)`;

    dots[promoIndex % promoSlides.length]?.classList.add('active');

    // When we've scrolled past the last real slide, snap back to the start
    // without a visible jump: wait for the scroll animation to finish, kill
    // the transition, jump to slide 0, then restore the transition.
    if (promoIndex === promoSlides.length) {
      sliderTrack.addEventListener('transitionend', function reset() {
        sliderTrack.removeEventListener('transitionend', reset);
        sliderTrack.style.transition = 'none';
        promoIndex = 0;
        sliderTrack.style.transform = 'translateX(0%)';
        // force reflow so the transition removal takes effect immediately
        void sliderTrack.offsetHeight;
        sliderTrack.style.transition = '';
      });
    }
  }

  // order info
  function updateDisplay(data) {
    const { customer, items, subtotal, discount, tax, total } = data;

    document.getElementById('hdr-name').textContent = customer || 'Walk-in Customer';

    hasItems = items && items.length > 0;

    if (hasItems) {
      document.getElementById('info-waiting').style.display = 'none';
      document.getElementById('thankyou').classList.remove('show');
      const oc = document.getElementById('order-content');
      oc.style.display = 'flex';
      document.getElementById('order-count').textContent = `${items.length} item${items.length > 1 ? 's' : ''}`;
      document.getElementById('order-items').innerHTML = items.map(item => `
        <div class="order-item">
          <div class="order-item__left">
            <div class="order-item__name">${item.name}</div>
            <div class="order-item__meta">${item.uom_name || ''} × ${item.quantity}</div>
          </div>
          <div class="order-item__right">
            <div class="order-item__price">${fmt(item.subtotal)}</div>
            <div class="order-item__qty">${fmt(item.price)} each</div>
          </div>
        </div>
      `).join('');

      document.getElementById('t-subtotal').textContent = fmt(subtotal);
      document.getElementById('t-tax').textContent      = fmt(tax);
      document.getElementById('t-discount').textContent = fmt(discount);
      document.getElementById('t-total').textContent    = fmt(total);

    } else {
      document.getElementById('order-content').style.display = 'none';
      document.getElementById('thankyou').classList.remove('show');
      document.getElementById('info-waiting').style.display  = 'flex';
    }
  }

  function showThankYou() {
    document.getElementById('order-content').style.display = 'none';
    document.getElementById('info-waiting').style.display  = 'none';
    document.getElementById('thankyou').classList.add('show');
    setTimeout(() => {
      document.getElementById('thankyou').classList.remove('show');
      document.getElementById('info-waiting').style.display = 'flex';
    }, 4000);
  }
  //QR payment overly
  function showQrOverlay(qrImageUrl, amount) {
    stopPromoSlider();
    document.getElementById('qr-code-container').innerHTML = `<img id="qr-code-img" src="${qrImageUrl}" alt="QR code">`;
    document.getElementById('qr-amount').textContent  = fmt(amount);
    document.getElementById('qr-overlay').classList.add('show');
  }

  function hideQrOverlay() {
    document.getElementById('qr-overlay').classList.remove('show');
    startPromoSlider();
  }

  // listen for messages from POS window
  window.addEventListener('message', function (e) {
    if (!e.data) return;

    // Order / cart updates → right panel only
    if (e.data.type === 'UPDATE_DISPLAY') {
      const wasEmpty = !hasItems;
      updateDisplay(e.data);
      if (!wasEmpty && (!e.data.items || e.data.items.length === 0)) {
        showThankYou();
      }
    }

    // Cashier selected QR payment → show QR over the slider
    if (e.data.type === 'SHOW_QR') {
      showQrOverlay(e.data.qrImageUrl, e.data.amount);
    }

    // Payment done / cancelled, or cashier selected Cash → back to slider
    if (e.data.type === 'HIDE_QR') {
      hideQrOverlay();
    }
  });

  startPromoSlider();
</script>

</body>
</html>
