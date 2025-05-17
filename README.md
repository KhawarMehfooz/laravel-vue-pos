# Laravel + Vue POS System

A basic Point of Sale (POS) system built with Laravel (backend) and Vue.js (frontend).

## Features

- [x] Category Management  
- [x] Company Management  
- [x] Product Management  
- [ ] POS Functionality (in progress)

## Setup Instructions

Follow these steps to run the project locally:

```bash
git clone https://github.com/KhawarMehfooz/laravel-vue-pos.git
cd laravel-vue-pos
npm install
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
composer run dev
```
> Note:
This project uses an SQLite database by default.
Make sure you have the SQLite extension enabled and a writable database/database.sqlite file created.

## Default Login Credentials

The following user is created by the database seeder:

- Email: `test@example.com`
- Password: `password`
