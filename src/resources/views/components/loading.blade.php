{{-- resources/views/components/loading.blade.php
     Usage: <x-loading />
            <x-loading message="Please wait" />
--}}

{{-- Styles INLINE — not @push — so they always render regardless of layout --}}
<style>
#page-loader {
    position: fixed;
    inset: 0;
    background: #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    transition: opacity 0.5s ease, visibility 0.5s ease;
}

#page-loader.is-hidden {
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
}

.cart-loader {
    position: relative;
    width: 160px;
    height: 200px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-end;
}

@media (max-width: 768px) { .cart-loader { transform: scale(0.85); } }
@media (max-width: 480px) { .cart-loader { transform: scale(0.70); } }

.items-container {
    position: absolute;
    top: 20px;
    left: 0;
    width: 100%;
    height: 100px;
    z-index: 1;
}

.item {
    position: absolute;
    opacity: 0;
    animation: drop-item 4s cubic-bezier(0.3, 0, 0.5, 1) infinite;
}

.item svg {
    width: 100%;
    height: 100%;
    display: block;
}

#item-mobile    { top: -15px; left: 58px; width: 20px; height: 32px; --end-rot: -15deg; animation-delay: 0.05s; }
#item-laptop    { top: -10px; left: 65px; width: 35px; height: 26px; --end-rot:  10deg; animation-delay: 0.8s;  }
#item-tab       { top: -20px; left: 82px; width: 24px; height: 32px; --end-rot:  25deg; animation-delay: 1.6s;  }
#item-headphone { top: -15px; left: 55px; width: 28px; height: 28px; --end-rot:  -5deg; animation-delay: 2.4s;  }
#item-mixer     { top: -25px; left: 72px; width: 26px; height: 34px; --end-rot:   5deg; animation-delay: 3.2s;  }

#cart-icon {
    position: relative;
    z-index: 2;
    width: 140px;
    height: 120px;
    animation: cart-bounce 0.8s ease-in-out infinite;
    animation-delay: 0.2s;
}

#cart-icon svg {
    width: 100%;
    height: 100%;
}

.loading-text {
    margin-top: 16px;
    font-size: 15px;
    font-weight: 600;
    color: #334155;
    letter-spacing: 0.5px;
    white-space: nowrap;
    font-family: 'Inter', 'DM Sans', sans-serif;
}

.dot {
    display: inline-block;
    animation: wave 1.5s infinite;
}
.dot:nth-child(1) { animation-delay: 0.0s; }
.dot:nth-child(2) { animation-delay: 0.1s; }
.dot:nth-child(3) { animation-delay: 0.2s; }

@keyframes drop-item {
    0%        { transform: translateY(-20px) scale(0.8) rotate(0deg); opacity: 0; }
    10%       { opacity: 1; transform: translateY(20px) scale(1) rotate(calc(var(--end-rot) / 2)); }
    25%       { transform: translateY(55px) scale(1) rotate(var(--end-rot)); opacity: 1; }
    35%, 100% { transform: translateY(75px) scale(0.9) rotate(var(--end-rot)); opacity: 0; }
}

@keyframes cart-bounce {
    0%, 100% { transform: translateY(0);     }
    40%       { transform: translateY(2.5px); }
    60%       { transform: translateY(0);     }
}

@keyframes wave {
    0%, 60%, 100% { transform: translateY(0);    }
    30%            { transform: translateY(-3px); }
}

@media (prefers-reduced-motion: reduce) {
    .item, #cart-icon, .dot { animation: none; }
    .item { opacity: 1; transform: translateY(40px); }
}
</style>

<div id="page-loader" aria-hidden="true">
    <div class="cart-loader">

        <div class="items-container">

            {{-- Mobile --}}
            <div id="item-mobile" class="item">
                <svg viewBox="0 0 24 36" xmlns="http://www.w3.org/2000/svg">
                    <rect x="2" y="2" width="20" height="32" rx="3" fill="#3b82f6"/>
                    <rect x="4" y="4" width="16" height="25" rx="1" fill="#eff6ff"/>
                    <circle cx="12" cy="31.5" r="1.5" fill="#eff6ff"/>
                </svg>
            </div>

            {{-- Laptop --}}
            <div id="item-laptop" class="item">
                <svg viewBox="0 0 40 30" xmlns="http://www.w3.org/2000/svg">
                    <rect x="6" y="4" width="28" height="18" rx="1" fill="#64748b"/>
                    <rect x="8" y="6" width="24" height="14" fill="#cbd5e1"/>
                    <polygon points="2,24 38,24 40,28 0,28" fill="#334155"/>
                </svg>
            </div>

            {{-- Tablet --}}
            <div id="item-tab" class="item">
                <svg viewBox="0 0 32 40" xmlns="http://www.w3.org/2000/svg">
                    <rect x="2" y="2" width="28" height="36" rx="2" fill="#a855f7"/>
                    <rect x="4" y="4" width="24" height="32" fill="#faf5ff"/>
                </svg>
            </div>

            {{-- Headphones --}}
            <div id="item-headphone" class="item">
                <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                    <path d="M 6 16 C 6 4, 26 4, 26 16" fill="none" stroke="#ef4444" stroke-width="4"/>
                    <rect x="2" y="14" width="8" height="14" rx="4" fill="#ef4444"/>
                    <rect x="22" y="14" width="8" height="14" rx="4" fill="#ef4444"/>
                </svg>
            </div>

            {{-- Mixer --}}
            <div id="item-mixer" class="item">
                <svg viewBox="0 0 32 40" xmlns="http://www.w3.org/2000/svg">
                    <path d="M 8 20 L 24 20 L 28 36 L 4 36 Z" fill="#14b8a6"/>
                    <circle cx="16" cy="28" r="4" fill="#ccfbf1"/>
                    <polygon points="10,20 22,20 24,8 8,8" fill="#cbd5e1"/>
                    <rect x="6" y="4" width="20" height="4" rx="2" fill="#0f766e"/>
                    <path d="M 8 10 L 3 10 L 3 18 L 8 18" fill="none" stroke="#94a3b8" stroke-width="2.5" stroke-linejoin="round"/>
                </svg>
            </div>

        </div>

        {{-- Cart --}}
        <div id="cart-icon">
            <svg viewBox="0 0 140 120" xmlns="http://www.w3.org/2000/svg">
                <g fill="none" stroke="#334155" stroke-width="5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="35"  y1="90"  x2="110" y2="90"/>
                    <line x1="40"  y1="90"  x2="50"  y2="70"/>
                    <polyline points="10,15 25,15 40,30"/>
                    <line x1="40"  y1="30"  x2="50"  y2="70"/>
                    <line x1="68"  y1="30"  x2="71"  y2="70"/>
                    <line x1="96"  y1="30"  x2="93"  y2="70"/>
                    <line x1="125" y1="30"  x2="115" y2="70"/>
                    <line x1="40"  y1="30"  x2="125" y2="30"/>
                    <line x1="43"  y1="43"  x2="122" y2="43"/>
                    <line x1="47"  y1="57"  x2="118" y2="57"/>
                    <line x1="50"  y1="70"  x2="115" y2="70"/>
                    <circle cx="45"  cy="105" r="8"/>
                    <circle cx="105" cy="105" r="8"/>
                </g>
            </svg>
        </div>

        <div class="loading-text">
            {{ $message }}<span class="dot">.</span><span class="dot">.</span><span class="dot">.</span>
        </div>

    </div>
</div>

{{-- Script also inline — runs immediately, no dependency on @stack --}}
<script>
(function () {
    var loader = document.getElementById('page-loader');
    if (!loader) return;

    function hideLoader() {
        loader.classList.add('is-hidden');
        loader.addEventListener('transitionend', function () {
            loader.remove();
        }, { once: true });
    }

    if (document.readyState === 'complete') {
        setTimeout(hideLoader, 600);
    } else {
        window.addEventListener('load', function () {
            setTimeout(hideLoader, 600);
        });
    }

    // Safety net — always hide after 5 s
    setTimeout(hideLoader, 5000);
})();
</script>
