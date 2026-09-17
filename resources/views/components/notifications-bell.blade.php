{{-- Колокольчик уведомлений. Подключается в layouts/navigation. --}}

<div class="dropdown" id="notifDropdown">
    <button class="btn btn-link position-relative p-2 text-light text-decoration-none"
            id="notifBell"
            data-bs-toggle="dropdown"
            aria-expanded="false">
        <i class="bi bi-bell fs-5"></i>
        <span id="notifBadge"
              class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notif-badge d-none">
            0
        </span>
    </button>

    <div class="dropdown-menu dropdown-menu-end p-0 shadow notif-menu">

        {{-- Шапка --}}
        <div class="d-flex align-items-center justify-content-between px-3 py-2 notif-header">
            <span class="fw-semibold text-app small">Уведомления</span>
            <button class="btn btn-link btn-sm text-muted p-0 fs-075 text-decoration-none" id="markAllRead">
                Прочитать все
            </button>
        </div>

        {{-- Список уведомлений --}}
        <div id="notifList">
            <div class="text-center py-4 text-muted small" id="notifEmpty">
                <i class="bi bi-bell-slash d-block fs-3 opacity-25 mb-2"></i>
                Нет уведомлений
            </div>
        </div>

    </div>
</div>

@once
@push('scripts')
<script>
(function () {
    const icons = {
        submitted:           'bi-send text-info',
        hr_edited:           'bi-pencil text-warning',
        supervisor_review:   'bi-hourglass-split text-warning',
        supervisor_approved: 'bi-check-circle text-success',
        supervisor_rejected: 'bi-x-circle text-danger',
        supervisor_on_hold:  'bi-pause-circle text-warning',
        closed:              'bi-lock text-secondary',
        confirmed_closed:    'bi-lock-fill text-dark',
    };

    function setBadge(count) {
        const badge = document.getElementById('notifBadge');
        badge.textContent = count > 99 ? '99+' : count;
        badge.classList.toggle('d-none', count <= 0);
    }

    function loadNotifications() {
        fetch('/notifications', { headers: { Accept: 'application/json' } })
            .then(r => r.json())
            .then(data => {
                const list = document.getElementById('notifList');
                const empty = document.getElementById('notifEmpty');

                setBadge(data.unread_count);
                list.innerHTML = '';

                if (!data.notifications.length) {
                    list.appendChild(empty);
                    return;
                }

                data.notifications.forEach(n => {
                    const isUnread = !n.read_at;
                    const iconClass = icons[n.type] || 'bi-bell text-primary';
                    const posName = n.vacancy_request?.position?.name || '';

                    const item = document.createElement('div');
                    item.className = 'notif-item d-flex align-items-start gap-2 px-3 py-2'
                        + (isUnread ? ' notif-item--unread' : '');
                    item.dataset.id = n.id;

                    const message = document.createElement('div');
                    message.className = 'small text-app lh-sm';
                    message.textContent = n.message;

                    const position = document.createElement('div');
                    position.className = 'text-muted fs-07';
                    position.textContent = posName;

                    const time = document.createElement('div');
                    time.className = 'text-muted fs-07';
                    time.textContent = timeAgo(n.created_at);

                    const body = document.createElement('div');
                    body.className = 'flex-grow-1';
                    body.append(message);
                    if (posName) {
                        body.append(position);
                    }
                    body.append(time);

                    const icon = document.createElement('div');
                    icon.className = 'mt-1 flex-shrink-0';
                    icon.innerHTML = `<i class="bi ${iconClass} fs-5"></i>`;

                    item.append(icon, body);

                    if (isUnread) {
                        const dot = document.createElement('div');
                        dot.className = 'flex-shrink-0 mt-2';
                        dot.innerHTML = '<span class="app-dot text-app-primary"></span>';
                        item.append(dot);
                    }

                    item.addEventListener('click', () => {
                        markRead(n.id);
                        if (n.vacancy_request_id) {
                            window.location.href = `/statements/${n.vacancy_request_id}`;
                        }
                    });

                    list.appendChild(item);
                });
            })
            .catch(console.error);
    }

    function markRead(id) {
        fetch(`/notifications/${id}/read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                Accept: 'application/json',
            },
        }).then(() => loadNotifications());
    }

    document.getElementById('markAllRead').addEventListener('click', (e) => {
        e.stopPropagation();
        fetch('/notifications/read-all', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                Accept: 'application/json',
            },
        }).then(() => loadNotifications());
    });

    function timeAgo(dateStr) {
        const diff = Math.floor((Date.now() - new Date(dateStr)) / 1000);
        if (diff < 60)    return 'только что';
        if (diff < 3600)  return Math.floor(diff / 60) + ' мин. назад';
        if (diff < 86400) return Math.floor(diff / 3600) + ' ч. назад';
        return Math.floor(diff / 86400) + ' д. назад';
    }

    document.getElementById('notifBell').addEventListener('click', loadNotifications);

    function updateBadge() {
        fetch('/notifications', { headers: { Accept: 'application/json' } })
            .then(r => r.json())
            .then(data => setBadge(data.unread_count));
    }

    updateBadge();
    setInterval(updateBadge, 30000);
})();
</script>
@endpush
@endonce
