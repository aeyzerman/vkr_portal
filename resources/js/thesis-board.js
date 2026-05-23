const board = document.getElementById('thesis-board');

if (board) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    let draggedCard = null;
    let sourceColumn = null;

    const updateColumnCount = (column) => {
        const count = column.querySelectorAll('.kanban-card').length;
        const badge = column.querySelector('.kanban-column-count');

        if (badge) {
            badge.textContent = String(count);
        }
    };

    const revertCard = () => {
        if (!draggedCard || !sourceColumn) {
            return;
        }

        const zone = sourceColumn.querySelector('[data-drop-zone]');
        zone?.appendChild(draggedCard);
        updateColumnCount(sourceColumn);
        updateColumnCount(draggedCard.closest('.kanban-column'));
    };

    board.querySelectorAll('.kanban-card--draggable').forEach((card) => {
        card.addEventListener('dragstart', (event) => {
            draggedCard = card;
            sourceColumn = card.closest('.kanban-column');
            card.classList.add('kanban-card--dragging');
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', card.dataset.thesisId);
        });

        card.addEventListener('dragend', () => {
            card.classList.remove('kanban-card--dragging');
            board.querySelectorAll('[data-drop-zone]').forEach((zone) => {
                zone.classList.remove('kanban-drop-target');
            });
        });
    });

    board.querySelectorAll('[data-drop-zone]').forEach((zone) => {
        zone.addEventListener('dragover', (event) => {
            if (!draggedCard) {
                return;
            }

            event.preventDefault();
            event.dataTransfer.dropEffect = 'move';
            zone.classList.add('kanban-drop-target');
        });

        zone.addEventListener('dragleave', (event) => {
            if (!zone.contains(event.relatedTarget)) {
                zone.classList.remove('kanban-drop-target');
            }
        });

        zone.addEventListener('drop', async (event) => {
            event.preventDefault();
            zone.classList.remove('kanban-drop-target');

            if (!draggedCard) {
                return;
            }

            const targetColumn = zone.closest('.kanban-column');
            const newStatus = Number(targetColumn?.dataset.status);
            const previousColumn = sourceColumn;

            if (!targetColumn || !previousColumn || Number.isNaN(newStatus)) {
                return;
            }

            if (Number(draggedCard.dataset.status) === newStatus) {
                return;
            }

            zone.appendChild(draggedCard);
            updateColumnCount(previousColumn);
            updateColumnCount(targetColumn);

            try {
                const response = await fetch(draggedCard.dataset.updateUrl, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrfToken ?? '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ status: newStatus }),
                });

                if (!response.ok) {
                    throw new Error('Не удалось обновить статус');
                }

                const payload = await response.json();
                draggedCard.dataset.status = String(newStatus);
            } catch (error) {
                revertCard();
                window.alert(error.message || 'Не удалось переместить карточку');
            } finally {
                draggedCard = null;
                sourceColumn = null;
            }
        });
    });
}
