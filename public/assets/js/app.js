(function () {
    const themeKey = 'smart-note-theme';
    const savedTheme = localStorage.getItem(themeKey);

    if (savedTheme === 'dark') {
        document.body.classList.add('dark-mode');
    }

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            localStorage.setItem(themeKey, document.body.classList.contains('dark-mode') ? 'dark' : 'light');
        });
    });

    document.querySelectorAll('[data-sidebar-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                sidebar.classList.toggle('open');
            }
        });
    });

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.getAttribute('data-confirm') || 'Are you sure?';
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('[data-toast]').forEach((toast) => {
        window.setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-6px)';
            window.setTimeout(() => toast.remove(), 220);
        }, 3500);
    });

    document.querySelectorAll('[data-image-input]').forEach((input) => {
        input.addEventListener('change', () => {
            const preview = document.querySelector('[data-image-preview]');
            const file = input.files && input.files[0];
            if (!preview || !file) {
                return;
            }

            preview.src = URL.createObjectURL(file);
            preview.hidden = false;
        });
    });
})();

