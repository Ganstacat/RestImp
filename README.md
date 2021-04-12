## Что это?
Тренировка в создании RESTful API. 

## Как установить? 
1. $ git clone https://github.com/Ganstacat/RestImp
2. Перейти в папку проекта ($ cd restImp), $ composer install
3. Сделать копию .env.example, переименовать копию в .env ($ cp .env.example .env)
4. В .env файле, указать параметры для базы данных: DB_DATABASE, DB_USERNAME, DB_PASSWORD
5. $ php artisan key:generate
6. $ php artisan migrate --seed
7. $ php artisan serve
8. Посмотреть доступные пути: $ php artisan route:list