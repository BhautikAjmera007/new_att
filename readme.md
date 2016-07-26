# Laravel PHP Framework

[![Build Status](https://travis-ci.org/laravel/framework.svg)](https://travis-ci.org/laravel/framework)
[![Total Downloads](https://poser.pugx.org/laravel/framework/d/total.svg)](https://packagist.org/packages/laravel/framework)
[![Latest Stable Version](https://poser.pugx.org/laravel/framework/v/stable.svg)](https://packagist.org/packages/laravel/framework)
[![Latest Unstable Version](https://poser.pugx.org/laravel/framework/v/unstable.svg)](https://packagist.org/packages/laravel/framework)
[![License](https://poser.pugx.org/laravel/framework/license.svg)](https://packagist.org/packages/laravel/framework)

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable, creative experience to be truly fulfilling. Laravel attempts to take the pain out of development by easing common tasks used in the majority of web projects, such as authentication, routing, sessions, queueing, and caching.

Laravel is accessible, yet powerful, providing powerful tools needed for large, robust applications. A superb inversion of control container, expressive migration system, and tightly integrated unit testing support give you the tools you need to build any application with which you are tasked.

## Official Documentation

Documentation for the framework can be found on the [Laravel website](http://laravel.com/docs).

# Welcome to the Attendance Managment

## Prerequisite
Below are the prerequisite for the project.

    - PHP >= 5.5.9
    - Laravel 5.2
    - Mongo DB

# Installation

After clone attendance managment you need to Migrate Database into your system, run the following command and wait for it to finish for database migration.

```
php artisan migrate

It will generate below tables

1)  cron
2)  holiday
3)  leave_details
4)  wfh_details
5)  wfc_details
6)  report
7)  sendmail_details
8)  users
9)  password_reset
10) migrations
10) worktime_details
```

Now you can run the below artisan command to store data into the database table

## To get Work From Home, Work From Client and Leave details execute the below command

```
php artisan leave
```

## To get Holiday data execute the below command

```
php artisan holiday
```

## To send mail to absent employee execute below command

```
php artisan absent
```

## To send reminder mail to absent employee execute below command

```
php artisan reminder
```

## Cron Execution Timing

```
php artisan leave 			09:00 AM
php artisan absent			09:30 AM
php artisan reminder		10:00 AM
php artisan worktime		10:30 AM
```