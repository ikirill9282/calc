# Инструкция по запуску Laravel приложения в Docker

## Статус
✅ Docker контейнер запущен и работает на http://localhost:8000
⚠️ Подключение к БД требует настройки на сервере

## Проблема с подключением к БД

MySQL на сервере (147.45.146.200) не разрешает удаленные подключения. Есть два варианта решения:

### Вариант 1: Настроить MySQL на сервере (рекомендуется)

На сервере выполните:

```bash
# 1. Подключитесь к MySQL
mysql -u root -p

# 2. Создайте пользователя для удаленного доступа (если еще не создан)
CREATE USER 'server'@'%' IDENTIFIED BY 'kvt7QztMjCLJZtsn';
GRANT ALL PRIVILEGES ON lk_tk82_ru.* TO 'server'@'%';
FLUSH PRIVILEGES;

# 3. Проверьте конфигурацию MySQL
# Отредактируйте /etc/mysql/mysql.conf.d/mysqld.cnf или /etc/my.cnf
# Найдите строку bind-address и измените:
bind-address = 0.0.0.0  # вместо 127.0.0.1

# 4. Перезапустите MySQL
sudo systemctl restart mysql

# 5. Проверьте firewall
sudo ufw allow 3306/tcp
```

### Вариант 2: Использовать SSH туннель

Если не хотите открывать MySQL для внешних подключений, используйте SSH туннель:

```bash
# На вашем локальном компьютере (в отдельном терминале):
ssh -L 3307:127.0.0.1:3306 root@147.45.146.200

# Затем в .env измените:
DB_HOST=127.0.0.1
DB_PORT=3307
```

## Текущая конфигурация

- **Приложение**: http://localhost:8000
- **БД хост**: 147.45.146.200
- **БД порт**: 3306
- **БД имя**: lk_tk82_ru
- **БД пользователь**: server

## Команды Docker

```bash
# Запустить контейнер
docker-compose up -d

# Остановить контейнер
docker-compose down

# Просмотр логов
docker-compose logs -f app

# Войти в контейнер
docker-compose exec app bash

# Выполнить artisan команды
docker-compose exec app php artisan migrate
docker-compose exec app php artisan migrate:status
```
