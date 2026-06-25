<div class="alert-wrapper">
    @if(session('success'))
        <div class="alert alert-success alert-box">
            <div class="alert-icon">✓</div>
            <div class="alert-content">
                <div class="alert-title">Success</div>
                <div class="alert-message">
                    {{ session('success') }}
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error alert-box">
            <div class="alert-icon">✕</div>
            <div class="alert-content">
                <div class="alert-title">Error</div>
                <div class="alert-message">
                    {{ session('error') }}
                </div>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-error alert-box">
            <div class="alert-icon">!</div>

            <div class="alert-content">
                <div class="alert-title">Validation Error</div>

                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        document.querySelectorAll('.alert-box').forEach(el => {
            el.style.transition = 'all .4s ease';
            el.style.opacity = '0';
            el.style.transform = 'translateX(40px)';

            setTimeout(() => el.remove(), 400);
        });
    }, 3000);
});
</script>
