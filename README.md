# test_omniconvert

```bash
composer update
```
create .env.local in root folder and add the connection to DB
```bash
DATABASE_URL=mysql://db_user:db_pass@127.0.0.1:3306/db_name
```
```bash
php bin/console doctrine:database:create  
php bin/console doctrine:migrations:migrate
symfony serve
```

Valid endpoints:<br>
[BASE_PATH]/transactions/create?user=123&transaction=9999&amount=12.4&created_at=2019-08-01<br>
[BASE_PATH]/report
