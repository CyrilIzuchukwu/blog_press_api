# Blog API

A RESTful API for managing blogs, posts, and user interactions(Likes and Comments) built with Laravel.

## Features

- CRUD operations for blogs
- CRUD operations for posts under blogs
- Like and comment on posts
- Token-based authentication
- RESTful JSON responses

## Requirements
- PHP 8.1+
- Laravel 10+
- MySQL 5.7+
- Composer

## Installation

- Clone the repository:
    command line or bash
   git clone https://github.com/yourusername/blog_press-api.git
   cd blog-api

## Install dependencies:
- composer install

- Create a copy of the .env file from .env.example


## Generate application key
- php artisan key:generate


## Run migrations and seed the database
- php artisan migrate --seed


## Seeded Data
- Email: viewer@example.com
- Password: password



## Note: I removed some route model binding in some controller function so i can manually check for existence using the id.
