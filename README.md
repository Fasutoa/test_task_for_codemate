🧪 Тесты
Тесты написаны с использованием PHPUnit и Laravel Testing Framework. Они покрывают сценарии пополнения, снятия, перевода и проверки баланса, включая случаи с автосозданием пользователей.
Запустите тесты:
textphp artisan test
Пример успешного вывода:
textPASS Tests\Feature\BalanceControllerTest
✓ it can deposit money to existing user
✓ it creates user if not exists on deposit
...
