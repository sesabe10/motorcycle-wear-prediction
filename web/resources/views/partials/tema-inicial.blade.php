<script>
    (function () {
        var guardado = null;
        try { guardado = localStorage.getItem('motocheck-tema'); } catch (e) {}
        var oscuro = guardado ? guardado === 'oscuro' : window.matchMedia('(prefers-color-scheme: dark)').matches;
        document.documentElement.dataset.tema = oscuro ? 'oscuro' : 'claro';
    })();
</script>
