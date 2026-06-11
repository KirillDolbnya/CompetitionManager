# VK Bot

## Описание проекта
Система управления спортивными соревнованиями, импорта турнирных данных из Excel и взаимодействия с участниками через VK-бота.

## Основные возможности
- Управление соревнованиями через административную панель Filament
- Импорт данных участников из Excel
- Поиск спортсменов по ФИО через VK-бота
- Закрытый раздел для тренеров с авторизацией
- Отображение информации о текущем соревновании
- Автоматическое удаление завершенных соревнований через Laravel Scheduler

---

## Технологии
- PHP 8.3
- Laravel 13
- Filament v5
- MySQL 8.0
- Docker / Docker Compose
- VK API
- Laravel Scheduler
- maatwebsite/excel

---

## Установка и запуск проекта

1. Клонируем репозиторий:
```bash
git clone https://github.com/KirillDolbnya/CompetitionManager.git
```

2. Копируем файлы конфигурации `.env.example`:
```bash
cp .env.example .env
```

3. Собираем и запускаем контейнеры в фоновом режиме:
```bash
docker compose up -d --build
```

4. Устанавливаем зависимости:
```bash
docker exec laravel_app composer install
```

5. Генерируем ключ приложения:
```bash
docker exec laravel_app php artisan key:generate
```

6. Выполняем миграции базы данных:
```bash
docker exec laravel_app php artisan migrate
```

7. Создаем учетную запись для административной панели:
```bash
docker exec laravel_app php artisan make:filament-user
```

---
