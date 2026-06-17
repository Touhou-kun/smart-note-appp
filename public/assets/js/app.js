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

    function bindConfirmForms(root = document) {
        root.querySelectorAll('form[data-confirm]').forEach((form) => {
            if (form.dataset.confirmReady === 'true') {
                return;
            }

            form.addEventListener('submit', (event) => {
                const message = form.getAttribute('data-confirm') || 'Are you sure?';
                if (!window.confirm(message)) {
                    event.preventDefault();
                }
            });

            form.dataset.confirmReady = 'true';
        });
    }

    bindConfirmForms();

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

    function debounce(callback, delay = 300) {
        let timer;
        return (...args) => {
            window.clearTimeout(timer);
            timer = window.setTimeout(() => callback(...args), delay);
        };
    }

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    function appUrl(path) {
        const base = window.location.pathname.startsWith('/Project1') ? '/Project1' : '';
        return `${base}/${path.replace(/^\/+/, '')}`;
    }

    function bindNotesSearch() {
        const form = document.querySelector('[data-notes-search-form]');
        const results = document.querySelector('[data-notes-search-results]');
        if (!form || !results) {
            return;
        }

        const input = form.querySelector('input[name="search"]');
        let activeController = null;
        const fields = Array.from(form.querySelectorAll('input[name], select[name]'));

        const buildSearchUrl = () => {
            const params = new URLSearchParams(new FormData(form));
            Array.from(params.keys()).forEach((key) => {
                if (params.get(key) === '') {
                    params.delete(key);
                }
            });

            const query = params.toString();
            return query ? `${form.action}?${query}` : form.action;
        };

        const syncFormFromUrl = () => {
            const params = new URLSearchParams(window.location.search);
            fields.forEach((field) => {
                field.value = params.get(field.name) || '';
            });
        };

        const updateHistory = (url, mode) => {
            if (mode === 'none' || url === window.location.href) {
                return;
            }

            if (mode === 'replace') {
                window.history.replaceState({}, '', url);
                return;
            }

            window.history.pushState({}, '', url);
        };

        const loadResults = async (historyMode = 'push') => {
            const url = buildSearchUrl();
            activeController?.abort();
            activeController = new AbortController();
            results.style.opacity = '0.55';

            try {
                const response = await fetch(url, {
                    headers: { 'X-Requested-With': 'fetch' },
                    signal: activeController.signal,
                });
                const html = await response.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const nextResults = doc.querySelector('[data-notes-search-results]');

                if (!response.ok || !nextResults) {
                    throw new Error('Unable to search notes.');
                }

                results.innerHTML = nextResults.innerHTML;
                bindConfirmForms(results);
                bindNotesEditor(results);
                updateHistory(url, historyMode);
            } catch (error) {
                if (error.name === 'AbortError') {
                    return;
                }

                form.submit();
            } finally {
                results.style.opacity = '1';
            }
        };

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            loadResults();
        });

        form.querySelectorAll('select').forEach((select) => {
            select.addEventListener('change', () => loadResults());
        });

        if (input) {
            input.addEventListener('input', debounce(() => loadResults('replace'), 300));
        }

        window.addEventListener('popstate', () => {
            syncFormFromUrl();
            loadResults('none');
        });
    }

    function bindDashboardSearch() {
        const form = document.querySelector('[data-dashboard-search-form]');
        const results = document.querySelector('[data-dashboard-search-results]');
        if (!form || !results) {
            return;
        }

        const input = form.querySelector('[data-dashboard-search-input]');
        const emptyState = document.createElement('div');
        emptyState.className = 'empty-state dashboard-search-empty';
        emptyState.textContent = 'No notes found.';
        emptyState.hidden = true;
        results.after(emptyState);

        const updateDashboardUrl = () => {
            const params = new URLSearchParams(window.location.search);
            const query = (input?.value || '').trim();

            if (query === '') {
                params.delete('search');
            } else {
                params.set('search', query);
            }

            const nextUrl = params.toString()
                ? `${form.action}?${params.toString()}`
                : form.action;
            window.history.replaceState({}, '', nextUrl);
        };

        const applySearch = () => {
            const query = (input?.value || '').trim().toLowerCase();
            let visibleCount = 0;

            results.querySelectorAll('[data-search-text]').forEach((card) => {
                const text = (card.dataset.searchText || '').toLowerCase();
                const matches = query === '' || text.includes(query);
                card.hidden = !matches;
                if (matches) {
                    visibleCount += 1;
                }
            });

            emptyState.hidden = visibleCount > 0;
        };

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            applySearch();
            updateDashboardUrl();
        });

        if (input) {
            input.addEventListener('input', debounce(() => {
                applySearch();
                updateDashboardUrl();
            }, 300));
        }

        applySearch();
    }

    function bindNotesEditor(root = document) {
        const cards = root.querySelectorAll('[data-editable-note]');
        if (!cards.length) {
            return;
        }

        cards.forEach((card) => {
            if (card.dataset.editorReady === 'true') {
                return;
            }

            const readView = card.querySelector('[data-note-read-view]');
            const form = card.querySelector('[data-note-edit-form]');
            const cancel = card.querySelector('[data-note-cancel-edit]');
            const status = card.querySelector('[data-note-edit-status]');
            let closeListener = null;

            const openEditor = () => {
                if (!form || !readView || !form.hidden) {
                    return;
                }

                readView.hidden = true;
                form.hidden = false;
                card.classList.add('is-editing');
                form.querySelector('input[name="title"]')?.focus();

                window.setTimeout(() => {
                    closeListener = (event) => {
                        if (!card.contains(event.target)) {
                            submitInlineForm(form);
                        }
                    };
                    document.addEventListener('mousedown', closeListener);
                }, 0);
            };

            const closeEditor = () => {
                if (closeListener) {
                    document.removeEventListener('mousedown', closeListener);
                    closeListener = null;
                }

                if (form && readView) {
                    form.hidden = true;
                    readView.hidden = false;
                    card.classList.remove('is-editing');
                }
            };

            card.addEventListener('click', (event) => {
                if (event.target.closest('a, button, form, input, textarea, select, label')) {
                    return;
                }

                openEditor();
            });

            form?.addEventListener('submit', (event) => {
                event.preventDefault();
                submitInlineForm(form);
            });

            cancel?.addEventListener('click', (event) => {
                event.preventDefault();
                closeEditor();
            });

            card.querySelectorAll('[data-note-action]').forEach((button) => {
                button.addEventListener('click', (event) => {
                    event.preventDefault();
                    runNoteAction(button);
                });
            });

            async function submitInlineForm(editForm) {
                if (editForm.hidden) {
                    return;
                }

                if (closeListener) {
                    document.removeEventListener('mousedown', closeListener);
                    closeListener = null;
                }

                const formData = new FormData(editForm);
                if (!formData.has('csrf_token')) {
                    formData.append('csrf_token', csrfToken());
                }
                status.textContent = 'Saving...';

                try {
                    const response = await fetch(appUrl('api/notes/update'), {
                        method: 'POST',
                        body: formData,
                    });
                    const data = await response.json();
                    if (!response.ok || !data.success) {
                        throw new Error(data.error || 'Unable to save note.');
                    }

                    renderNote(card, data.note);
                    status.textContent = 'Saved';
                    closeEditor();
                } catch (error) {
                    status.textContent = error.message;
                    window.setTimeout(() => {
                        closeListener = (event) => {
                            if (!card.contains(event.target)) {
                                submitInlineForm(editForm);
                            }
                        };
                        document.addEventListener('mousedown', closeListener);
                    }, 0);
                }
            }
        });
    }

    function renderNote(card, note) {
        const readView = card.querySelector('[data-note-read-view]');
        if (!readView || !note) {
            return;
        }

        const title = readView.querySelector('h2');
        const preview = readView.querySelector('p');
        const meta = readView.querySelector('.note-meta');
        const tags = readView.querySelector('.tag-row');

        if (title) {
            title.textContent = note.title || '';
        }

        if (preview) {
            const content = note.content || '';
            preview.textContent = content.length > 140 ? `${content.slice(0, 140)}...` : content;
        }

        if (meta) {
            meta.innerHTML = `<span class="badge">${escapeHtml(note.priority || 'Normal')}</span>`;
            if (Number(note.is_pinned) === 1) {
                meta.insertAdjacentHTML('beforeend', '<span class="badge badge-pin">Pinned</span>');
            }
            if (Number(note.is_favorite) === 1) {
                meta.insertAdjacentHTML('beforeend', '<span class="badge">Favorite</span>');
            }
        }

        if (tags) {
            const tagHtml = (note.tags || [])
                .map((tag) => `<span>#${escapeHtml(tag.name || '')}</span>`)
                .join('');
            tags.innerHTML = `<span>${escapeHtml(note.category_name || 'Uncategorized')}</span>${tagHtml}`;
        }

        let image = readView.querySelector('.note-thumb');
        if (note.image_path) {
            if (!image) {
                image = document.createElement('img');
                image.className = 'note-thumb';
                readView.prepend(image);
            }
            image.src = appUrl(`public/uploads/${note.image_path}`);
            image.alt = note.title || 'Note image';
        } else if (image) {
            image.remove();
        }
    }

    async function runNoteAction(button) {
        const action = button.dataset.noteAction;
        const noteId = button.dataset.noteId;
        const endpoint = action === 'favorite' ? 'api/notes/toggle-favorite' : 'api/notes/archive';
        const formData = new FormData();
        formData.append('id', noteId);
        formData.append('csrf_token', csrfToken());

        const response = await fetch(appUrl(endpoint), {
            method: 'POST',
            body: formData,
        });
        const data = await response.json();
        if (!response.ok || !data.success) {
            return;
        }

        if (action === 'archive') {
            button.closest('[data-editable-note]')?.remove();
            return;
        }

        button.textContent = data.is_favorite ? 'Unfavorite' : 'Favorite';
    }

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, (char) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;',
        }[char]));
    }

    bindNotesSearch();
    bindDashboardSearch();
    bindNotesEditor();
})();

