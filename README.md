# Baano

Доска объявлений и аренды с личными кабинетами, чатами и админ-панелью.

- **Прод:** https://baano.ru/
- **Репозиторий:** `git@github.com:knaz2002/baano.git`, ветка `main`

## Стек

| Слой | Технологии |
|------|------------|
| Backend | Laravel 13, PHP 8.3+ |
| Frontend | Vue 3, Inertia.js, Vite, Tailwind |
| Админка | Filament 5 (`/admin`) |
| БД | MySQL (прод и локально через дамп) |
| Медиа | Spatie Media Library |
| Очереди / сессии / кэш | database driver |

## Быстрый старт (Windows)

```bash
composer install
copy .env.example .env
php artisan key:generate
npm install
php artisan storage:link
composer dev:win
```

Сайт: http://127.0.0.1:8000 · Админка: http://127.0.0.1:8000/admin

Подробнее — в [HOW_TO_RUN.md](HOW_TO_RUN.md).

## Документация

| Файл | Содержание |
|------|------------|
| [HOW_TO_RUN.md](HOW_TO_RUN.md) | Локальный запуск, MySQL, дамп, медиа, деплой |
| [PROJECT.md](PROJECT.md) | Функционал, маршруты, сущности, структура кода |
| [ARCHITECTURE.md](ARCHITECTURE.md) | Архитектура: Inertia/Vue, Filament, интеграции |
| [FRONTEND.md](FRONTEND.md) | Frontend: шаблоны, Inertia/SPA, props вместо API, Vue |
| [PLAN.md](PLAN.md) | План работ и статусы |
| [AI.md](AI.md) | Checkpoint для восстановления контекста в чате |

## Тестовый админ

При запуске через `db:seed` (без дампа):

- Email: `admin@baano.local`
- Пароль: `password123`
