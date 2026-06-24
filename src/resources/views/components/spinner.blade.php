<div id="loader"
     style="
        position: fixed;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,0.65);
        backdrop-filter: blur(6px);
        z-index: 99999;
     ">

    <div style="width:45px;height:45px;position:relative;">
        @for ($i = 0; $i < 8; $i++)
            <div style="position:absolute; inset:0; transform: rotate({{ $i * 45 }}deg);">
                <div style="
                    position:absolute;
                    top:0;
                    left:40%;
                    width:20%;
                    height:20%;
                    border-radius:50%;
                    background:#2563eb;
                    animation:pulse 0.9s ease-in-out infinite;
                    animation-delay:-{{ $i * 0.1 }}s;
                "></div>
            </div>
        @endfor
    </div>
</div>

<style>
@keyframes pulse {
    0%, 100% { transform: scale(0); opacity: 0.4; }
    50% { transform: scale(1); opacity: 1; }
}
</style>
