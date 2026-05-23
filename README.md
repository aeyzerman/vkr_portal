В корне проекта пускаем
1) docker compose up -d --build
2) docker compose exec app /scripts/setup.sh

## Администратор

В `.env` задайте учётные данные:

```
ADMIN_NAME="Administrator"
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD=change-me
```

После миграции создаётся автоматически (`portal:ensure-admin` в setup.sh). Вручную:

```bash
php artisan portal:ensure-admin
```

Альтернатива — регистрация первого админа через UI: задайте `ADMIN_REGISTRATION_TOKEN` в `.env`, откройте `/register` и введите токен в поле «Токен администратора» (поле видно только пока в системе нет администратора).
