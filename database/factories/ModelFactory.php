<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\User::class, function (Faker\Generator $faker) {

    return [
        'name' => $faker->name,
        'email' => $faker->email,
        'password' => bcrypt(str_random(10)),
        'remember_token' => str_random(10),
    ];
});

$factory->define(PhpSoft\Articles\Models\Category::class, function ($faker) {

    return [
        'name' => $faker->name,
        'alias' => $faker->slug,
        'image' => $faker->uuid,
        'description' => $faker->text,
        'parent_id' => 0,
        'order' => 0,
        'status' => 1,
    ];
});

$factory->define(PhpSoft\Articles\Models\Article::class, function($faker) {

    return [
        'title' => $faker->text,
        'content' => $faker->text,
        'alias' => $faker->slug,
        'image' => $faker->uuid,
        'description' => $faker->text,
        'user_id' => 1,
        'order' => 0,
        'status' => 1,
        'category_id' => 1
    ];
});
