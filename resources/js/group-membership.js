const panel = document.querySelector('[data-group-membership]');

if (panel) {
    const searchUrl = panel.dataset.searchUrl;
    const storeUrl = panel.dataset.storeUrl;
    const input = panel.querySelector('[data-student-search-input]');
    const button = panel.querySelector('[data-student-search-button]');
    const results = panel.querySelector('[data-student-search-results]');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const renderResults = (students) => {
        results.innerHTML = '';

        if (!students.length) {
            results.innerHTML = '<p class="text-sm text-stone-500">Студенты не найдены.</p>';
            results.hidden = false;
            return;
        }

        students.forEach((student) => {
            const row = document.createElement('div');
            row.className = 'flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-stone-200 bg-white p-3';

            const info = document.createElement('div');
            info.innerHTML = `<p class="font-medium text-stone-900">${student.display_name}</p><p class="text-xs text-stone-500">${student.email}</p>`;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = storeUrl ?? '';
            form.innerHTML = `
                <input type="hidden" name="_token" value="${csrfToken ?? ''}">
                <input type="hidden" name="user_id" value="${student.id}">
                <button type="submit" class="btn-primary text-sm">Добавить</button>
            `;

            row.append(info, form);
            results.append(row);
        });

        results.hidden = false;
    };

    const runSearch = async () => {
        const query = input?.value.trim();

        if (!query || query.length < 2 || !searchUrl) {
            return;
        }

        const response = await fetch(`${searchUrl}?q=${encodeURIComponent(query)}`, {
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) {
            return;
        }

        const payload = await response.json();
        renderResults(payload.students ?? []);
    };

    button?.addEventListener('click', runSearch);
    input?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            runSearch();
        }
    });
}
