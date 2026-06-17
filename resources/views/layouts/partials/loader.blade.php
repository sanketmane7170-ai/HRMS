<div id="wp-global-loader" class="wp-loader-wrap">
    {{-- High-Speed Top Progress Bar --}}
    <div class="wp-top-progress-bar"></div>

    <div class="wp-loader-inner">
        {{-- High-Fidelity Branded Mark --}}
        <div class="wp-brand-mark" style="background: transparent; box-shadow: none;">
            <div class="wp-pulse-circle" style="background: rgba(79, 70, 229, 0.1);"></div>
            <img src="{{ getSmallLogo() }}" alt="WorkPilot" style="width: 100%; height: 100%; object-fit: contain;">
        </div>
        <div class="wp-loader-text">
            <span>P</span><span>r</span><span>o</span><span>c</span><span>e</span><span>s</span><span>s</span><span>i</span><span>n</span><span>g</span><span>.</span><span>.</span><span>.</span>
        </div>
    </div>
</div>

<script>
    window.addEventListener('load', function() {
        const loader = document.getElementById('wp-global-loader');
        if (loader) {
            loader.style.pointerEvents = 'none'; // stop blocking clicks immediately
            loader.style.opacity = '0';
            setTimeout(() => { loader.style.display = 'none'; }, 500);
        }
    });
    // Note: beforeunload intentionally removed — file downloads trigger it and
    // leave the loader stuck on screen since the page never actually unloads.
</script>
