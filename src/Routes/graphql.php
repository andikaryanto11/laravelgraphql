<?php

use Illuminate\Support\Facades\Route;
use LaravelGraphQL\App\Http\Controllers\GraphQLController;

Route::post('/graphql', [GraphQLController::class, 'index']);