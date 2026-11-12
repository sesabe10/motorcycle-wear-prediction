<button type="button" class="tema {{ $clase ?? '' }}" data-cambiar-tema aria-label="Cambiar a modo oscuro" title="Cambiar modo claro / oscuro">
    <svg class="tema__luna" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>
    <svg class="tema__sol" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
</button>
<script>
    (function () {
        var raiz = document.documentElement;
        function pintar() {
            var oscuro = raiz.dataset.tema === 'oscuro';
            document.querySelectorAll('[data-cambiar-tema]').forEach(function (b) {
                b.setAttribute('aria-label', oscuro ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro');
                b.setAttribute('aria-pressed', oscuro ? 'true' : 'false');
            });
        }
        if (!window.motocheckTema) {
            window.motocheckTema = true;
            document.addEventListener('click', function (e) {
                if (!e.target.closest('[data-cambiar-tema]')) return;
                raiz.dataset.tema = raiz.dataset.tema === 'oscuro' ? 'claro' : 'oscuro';
                try { localStorage.setItem('motocheck-tema', raiz.dataset.tema); } catch (err) {}
                pintar();
            });
        }
        pintar();
    })();
</script>
