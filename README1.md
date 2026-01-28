<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).




## hỗ trợ excel

composer require maatwebsite/excel

## hỗ trợ word, pdf
composer require phpoffice/phpword
composer require smalot/pdfparser


## cài composer

composer install

cp .env.example .env

php artisan key:generate

## chạy dữ liệu 

php artisan migrate  

php artisan db:seed


git commit -m "Update README"

## chạy lệnh này để backup database liên tục (phải chạy nền mới tự động backup)
php artisan schedule:work


## chạy dữ liệu 18 bảng từ file sql_insert_data.sql
//Chỉ chạy dữ liệu file sql_insert_data.sql để thêm tất cả dữ liệu file sql_insert_data.sql vào mysql

php artisan db:seed --class=SqlDataSeeder 

//Chỉnh sửa ở file sql_insert_data.sql chạy lệnh lại lệnh này để tự động cập nhật lại từ đầu như sql_insert_data.sql mới chỉnh sửa

php artisan migrate:fresh --seed --seeder=SqlDataSeeder 

## Lấy code từ github
git fetch origin 

git switch master

git branch -v (Kiểm tra nhánh đang ở hiện tại)

git pull origin master

git checkout -b <tên_branch> (Tạo nhánh mới từ master)

git stash -u (cất code chưa commit để pull push không bị báo lỗi)

git stash pop (lấy code chưa commit đã cất từ lênh git stash -u, đổi nhánh rồi stash pop cũng được)

## Việc kiểm tra lưu 1 session khi đăng nhập có 2 chỗ
1. trong database: trong file .env "SESSION_DRIVER=database" => KIỂM TRA TRONG MYSQL bảng "session" để kiểm tra (1 trình duyệt chỉ lưu 1 tài khoản, nếu đăng nhập tài khoản khác sẽ đổi id)

2. trong file .env "SESSION_DRIVER=file" => KIỂM TRA TRONG THƯ MỤC storage/framework/sessions (1 trình duyệt chỉ lưu 1 tài khoản, nếu đăng nhập tài khoản khác sẽ tên file [text](storage/framework/sessions/MIXCyjthTxnzQL2eOf2ObLgXaThVw6h0w4jd5Wd2) <=> [text](storage/framework/sessions/fzTAXtTcN68lZVjRVAoMQNbG7AWzsozq4IyHUs0y))
