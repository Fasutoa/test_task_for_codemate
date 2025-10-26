## Установка и запуск
```bash
# Клонировать репозиторий
git clone https://github.com/Fasutoa/test_task_for_codemate.git
cd test_task_for_codemate

# Запустить контейнеры
docker-compose up -d

# Установить зависимости Laravel
docker-compose exec app composer install

# Запустить миграции
docker-compose exec app php artisan migrate

# Запустить тесты
docker-compose exec app php artisan test
```

После запуска приложение доступно по http://localhost:80

## API Документация
### 1. Пополнение баланса
POST /api/deposit

```json
{
  "user_id": 1,
  "amount": 500.00,
  "comment": "Пополнение через карту"
}
```
Ответ:

```json
{
    "message": "Deposit successful",
    "user_id": "2",
    "balance": 1700
}
```
### 2. Списание средств
POST /api/withdraw

```json
{
  "user_id": 1,
  "amount": 200.00,
  "comment": "Покупка подписки"
}
```
Ответ:

```json
{
    "message": "Withdrawal successful",
    "user_id": "1",
    "balance": 400
}
```

### 3. Перевод между пользователями
POST /api/transfer

```json
{
  "from_user_id": 1,
  "to_user_id": 2,
  "amount": 150.00,
  "comment": "Перевод другу"
}
```
Ответ:

```json
{
    "message": "Transfer successful",
    "from_user_id": "1",
    "to_user_id": "2",
    "amount": "100"
}
```

### 4. Получение баланса
GET /api/balance/{user_id}

Ответ:

```json
{
  "user_id": 1,
  "balance": 350.00
}
```
## Тестирование

Проект покрыт тестами:

```bash
# Запуск всех тестов
docker-compose exec app php artisan test

# Запуск с покрытием
docker-compose exec app vendor/bin/phpunit --coverage-html coverage
```
