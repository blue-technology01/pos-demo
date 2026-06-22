@push('styles')
<style>
.alert-wrapper {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;

    width: 320px;
    max-width: calc(100vw - 40px);

    display: flex;
    flex-direction: column;
    gap: 10px;
}

.alert {
    padding: 12px 16px;
    margin-bottom: 10px;
    border-radius: 8px;
    font-size: 14px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    animation: slideIn 0.3s ease;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
}

.alert ul {
    margin: 0;
    padding-left: 18px;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
</style>
@endpush

<div class="alert-wrapper">

    @if (session('success'))
        <div class="alert alert-success alert-box">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-error alert-box">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error alert-box">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

</div>

<script>
setTimeout(() => {
    document.querySelectorAll('.alert-box').forEach(el => {
        el.style.transition = "0.5s";
        el.style.opacity = "0";
        setTimeout(() => el.remove(), 500);
    });
}, 3000);
</script>
