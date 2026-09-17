# Контракт рефакторинга (единый источник правды)

Документ описывает целевую структуру после рефакторинга. Все новые шаблоны обязаны
следовать ему. Стили — только из `resources/css/app.css` (тёмная тема Bootstrap 5),
никаких `<style>` и атрибутов `style="..."` в blade.

## 1. Роуты (routes/web.php)

| Имя | Метод | URI | Действие |
|---|---|---|---|
| statements.index | GET | /statements | список (видимость по роли) |
| statements.create | GET | /statements/create | форма создания (только department_head) |
| statements.store | POST | /statements | создать черновик |
| statements.show | GET | /statements/{statement} | детальная страница |
| statements.edit | GET | /statements/{statement}/edit | форма черновика |
| statements.update | PUT | /statements/{statement} | сохранить (заявитель-черновик или HR) |
| statements.destroy | DELETE | /statements/{statement} | удалить заявку |
| statements.submit-to-hr | POST | /statements/{statement}/submit-to-hr | отправить в HR |
| statements.send-to-supervisor | POST | /statements/{statement}/send-to-supervisor | HR → руководителю |
| statements.approve | POST | /statements/{statement}/approve | одобрить (super_admin) |
| statements.reject | POST | /statements/{statement}/reject | отклонить (comment обязателен) |
| statements.hold | POST | /statements/{statement}/hold | приостановить |
| statements.confirm-close | POST | /statements/{statement}/confirm-close | подтвердить закрытие |
| branches.index/store/destroy | | /branches | филиалы (super_admin) |
| departments.index/store/destroy | | /departments?branch_id= | отделы |
| subdivisions.index/store/destroy | | /subdivisions?department_id= | подразделения |
| positions.index/store/destroy | | /positions?subdivision_id= | должности |
| employee.subdivision.index | GET | /employee/subdivision | «Моё подразделение» |
| resumes.destroy | DELETE | /resumes/{resume} | удалить резюме из resume_bot |
| dashboard, profile.*, notifications.*, password.*, login/logout | | | без изменений |

## 2. Роли и статусы (только Enums, без магических строк)

- `App\Enums\UserRole`: `super_admin`, `hr_manager`, `department_head`, `employee`.
  Хелперы: `$user->hasUserRole(UserRole::X)`, `$user->hasAnyUserRole(...)`.
- `App\Enums\VacancyRequestStatus`: `draft`, `submitted`, `hr_reviewed`, `supervisor_review`,
  `approved`, `rejected`, `on_hold`, `searching`, `closed`, `confirmed_closed`.
  Методы: `label()`, `color()` (bootstrap-цвет), `options()` (value=>label),
  `isDecided()`. Модель кастует `status` в этот enum, поэтому в blade:
  `$statement->status->label()`, `$statement->status->color()`,
  `$statement->isDraft()` и т.п. Также доступны аксессоры `status_label` / `status_color`.

## 3. Проверки прав в шаблонах

- `@can('create', App\Models\VacancyRequest::class)` — кнопка «Новая заявка».
- `@can('update', $statement)` — редактирование данных.
- `@can('edit', $statement)` — форма редактирования черновика.
- `@can('submit', $statement)` — «Отправить в HR».
- `@can('sendToSupervisor', $statement)` — «Отправить руководителю» (HR).
- `@can('decide', $statement)` — approve/reject/hold (super_admin).
- `@can('confirmClose', $statement)` — подтвердить закрытие.
- `@can('delete', $statement)` — удалить заявку.
- `@can('branch.manage')`, `@can('department.manage')`, `@can('subdivision.manage')`,
  `@can('position.manage')` — кнопки создания/удаления оргструктуры.
- Директива `@role('super_admin')` / `@hasanyrole(...)` доступна (Spatie).

## 4. CSS-классы темы (resources/css/app.css)

Глобально уже переопределены Bootstrap `.card`, `.card-header`, `.table`, `.form-control`,
`.form-select`, `.dropdown-*`, `.btn-outline-*`, `body`, `.page-header`, `.text-muted`.
Использовать эти классы вместо inline-стилей:

| Класс | Назначение |
|---|---|
| `.app-shell` | обёртка страницы (min-height, фон) |
| `.app-kpi-icon` | крупная иконка KPI (2.5rem) |
| `.text-app` / `.text-app-muted` / `.text-app-primary` / `.text-app-soft` / `.text-app-placeholder` | цвета текста |
| `.fs-07` `.fs-075` `.fs-08` `.fs-09` | размеры шрифта |
| `.app-pre` / `.app-pre-lg` | многострочный текст (`white-space: pre-line`, min-height) |
| `.app-clickable` | cursor: pointer |
| `.app-col-sm` / `.app-col-md` | ограничение ширины колонки |
| `.app-empty` | пустое состояние |
| `.app-info-label` / `.app-info-value` | подпись/значение в карточках деталей |
| `.app-section-title` | заголовок секции |
| `.app-status-pill` | пилюля статуса |
| `.app-filter-btn` (+`.active`) | кнопка-фильтр |
| `.app-log` / `.app-log-dot` | лента истории |
| `.app-avatar` | круг с инициалами |
| `.app-primary-bar` | вертикальная полоса акцента |
| `.app-cat-a/.app-cat-b/.app-cat-c/.app-cat-d` | фон бейджа категории A–D |
| `.notif-*`, `.btn-user-menu`, `.bg-app-surface`, `.border-app` | навигация/уведомления |

Если нужного класса нет — берём Bootstrap-утилиту (`text-center`, `py-5`, `fw-semibold`,
`d-flex`, `gap-2`, `text-white-50`, ...), но не пишем `style="..."`.

## 5. Общие компоненты

- `<x-app-layout>` — layout с тёмной темой (Bootstrap CSS/JS + Vite `app.css`).
- `<x-flash />` — блок flash-сообщений `success` / `error` (создан в `resources/views/components/flash.blade.php`).
- `<x-application-logo class="app-logo" />`.
- Пагинация: `{{ $statements->links() }}` (Bootstrap 5 paginator уже настроен в AppServiceProvider? нет — используем класс `pagination`).

## 6. Безопасность

- Вывод пользовательских данных — только `{{ }}` (автоэкранирование). Никаких `{!! !!}`.
- `open`-redirect нет; ID валидируются Form Requests.
- Списки — только пагинированные (`per_page <= 50`).
