# Smartnet — локальная разработка в Docker

Нужны Docker Engine и Docker Compose. PHP, Composer, Node.js и MariaDB на хосте для запуска приложения не требуются.

## Первый запуск

1. Скопируйте `.env.docker.example` в `.env.docker`.
2. Укажите в `.env.docker` собственный непустой `DB_PASSWORD`. Этот файл исключён из Git. Если порт 8080 занят, задайте в нём `APP_PORT` и поправьте `APP_URL`; для Vite можно задать `VITE_PORT` (по умолчанию 5173).
3. Запустите контейнеры:

   ```sh
   docker compose --env-file .env.docker up --build -d
   ```

Сервис `bootstrap` устанавливает PHP-зависимости, создаёт `APP_KEY` при первом запуске, ждёт MariaDB и применяет миграции. После его успешного завершения стартуют приложение, Vite и обработчик очереди. Исходники монтируются из текущего каталога; зависимости хранятся в томах Docker.

Приложение: <http://localhost:8080>. Vite: <http://localhost:5173>. Изменения PHP и Blade видны после обновления страницы; Vite обновляет CSS и JavaScript автоматически. Проверка готовности приложения: <http://localhost:8080/up>.

## Команды

Для всех команд ниже используйте тот же файл окружения:

```sh
docker compose --env-file .env.docker logs -f app vite queue
docker compose --env-file .env.docker exec app php artisan route:list
docker compose --env-file .env.docker exec app php artisan migrate
docker compose --env-file .env.docker exec app php artisan test --compact
docker compose --env-file .env.docker down
```

После изменения `composer.json` повторно запустите `bootstrap`: `docker compose --env-file .env.docker up -d --force-recreate bootstrap app queue`. После изменения `package.json` пересоздайте Vite: `docker compose --env-file .env.docker up -d --force-recreate vite`.

Данные MariaDB сохраняются между перезапусками. Чтобы полностью сбросить локальную базу и тома зависимостей, выполните `docker compose --env-file .env.docker down -v`, затем снова `up --build -d`. Команда `down -v` удаляет локальные данные БД.

Если Docker сообщает, что не может подключиться к Engine, запустите Docker Desktop или другой Docker Engine и повторите команду.
