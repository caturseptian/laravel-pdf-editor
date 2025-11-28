# Laravel PDF Editor (QR or PNG Stamping)

Single page PDF editor built with Laravel 5.6 and PHP 7.3.

Flow:
1. User uploads a PDF.
2. PDF is previewed on the same page with pagination.
3. User generates a QR code or uploads a PNG.
4. User drags and resizes the QR or PNG on any page.
5. User saves and downloads the final stamped PDF.

## Features
- Upload PDF on one page
- PDF preview with Prev and Next pagination
- Generate QR code (PNG)
- Upload custom PNG
- Drag, drop, resize stamps on preview
- Export final multi page PDF with embedded stamps

## Tech Stack
- Laravel 5.6
- PHP 7.3
- PDF.js for rendering preview
- Interact.js for drag and resize
- FPDI + TCPDF for stamping and exporting
- Simple QrCode for QR generation

## Requirements
- PHP 7.3
- Composer
- Laravel 5.6 compatible environment
- GD or Imagick (for PNG handling, standard PHP install usually OK)

## Installation
Clone repo and install dependencies:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Create public storage symlink:
```bash
php artisan storage:link
```

Create required folders:
```bash
mkdir -p storage/app/public/pdfs
mkdir -p storage/app/public/stamps
mkdir -p storage/app/public/finals
```

Fix permissions:
```bash
chmod -R 775 storage bootstrap/cache
```

Routes
```bash
Route::get('/pdf-editor', 'PdfEditorController@index')->name('pdf.editor');
Route::post('/pdf-editor/upload', 'PdfEditorController@uploadPdf')->name('pdf.upload');
Route::post('/pdf-editor/upload-image', 'PdfEditorController@uploadImage')->name('image.upload');
Route::post('/pdf-editor/generate-qr', 'PdfEditorController@generateQr')->name('qr.generate');
Route::post('/pdf-editor/save', 'PdfEditorController@save')->name('pdf.save');
Route::get('/pdf-editor/download/{file}', 'PdfEditorController@download')->name('pdf.download');
```


<p align="center"><img src="https://res.cloudinary.com/dtfbvvkyp/image/upload/v1566331377/laravel-logolockup-cmyk-red.svg" width="400"></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/license.svg" alt="License"></a>
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

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 1500 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[British Software Development](https://www.britishsoftware.co)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- [UserInsights](https://userinsights.com)
- [Fragrantica](https://www.fragrantica.com)
- [SOFTonSOFA](https://softonsofa.com/)
- [User10](https://user10.com)
- [Soumettre.fr](https://soumettre.fr/)
- [CodeBrisk](https://codebrisk.com)
- [1Forge](https://1forge.com)
- [TECPRESSO](https://tecpresso.co.jp/)
- [Runtime Converter](http://runtimeconverter.com/)
- [WebL'Agence](https://weblagence.com/)
- [Invoice Ninja](https://www.invoiceninja.com)
- [iMi digital](https://www.imi-digital.de/)
- [Earthlink](https://www.earthlink.ro/)
- [Steadfast Collective](https://steadfastcollective.com/)
- [We Are The Robots Inc.](https://watr.mx/)
- [Understand.io](https://www.understand.io/)
- [Abdel Elrafa](https://abdelelrafa.com)
- [Hyper Host](https://hyper.host)

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).
