## About Scaffold.IO API

Scaffold.IO API is a Laravel 8 RESTFul starter kit for SPA and mobile clients. This kit includes the following features:
- Full implementation of a Token-based Authentication with [Sanctum](https://laravel.com/docs/8.x/sanctum)
- Partial implementation of Role-based Access Control with [Spatie](https://spatie.be/docs/laravel-permission/v5/introduction)
- Full implementation of CRUD for user profile with profile picture upload
- Full implementation of CRUD for test resources
- Full implementation Forgot and Reset Password with Email Notification

## Set up your local development environment
- Create a **.env** file from the **.env.example** that came with this project
- In the **.env** file, update the **APP_NAME**, **APP_URL**, the **DB_** variables, and the MAIL variables if you decide to use a different test mailing service or account. You may also change the **SPA_RESET_PASSWORD_URL** if you're spinning a different URL for your SPA.
- Locate your **php.ini** file and change the value **upload_max_filesize** to **8M**. See this [guide](https://devanswers.co/ubuntu-php-php-ini-configuration-file/) if you're having trouble finding the directory of your php.ini file
- Run the command `composer install`  to install all the project and dev dependencies
- Save the **.env** file and run the command `php artisan key:generate`
- Run the command `php artisan migrate` to create all the necessary tables of the database
- Run the command `php artisan db:seed --class=RbacSeeder` to populate the database with user roles and permissions. See *database/seeders/RbacSeeder.php*
- You may now run `php artisan serve` to serve the API locally. Use [Postman](https://www.postman.com/downloads/) and this [documentation](https://documenter.getpostman.com/view/5841607/U16gPnBU) for testing the endpoints

## Style Guide Ver. 0.1
- Use **ForRequest** validators as much as possible
- Favor single quotes over double quotes
- Use the **ApiResponder** trait in your controllers
- Use the **ApiErrorResponse** helper class for building error responses
- Use the command `php artisan make:controller ControllerName --api` when making new Controller classes
- Use `snake_case` for DB table columns, request inputs, and resource views
- Use `CamelCase` for class names
- Create separate API route files per resource/feature. Load all of them in `routes/api.php`
- Follow and implement the [PHPDoc](https://docs.phpdoc.org/3.0/guide/guides/docblocks.html) style guide
- Use `app\Exceptions\Handler.php` for centralized error handling
