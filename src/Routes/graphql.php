<?php

use Illuminate\Support\Facades\Route;
use LaravelGraphql\App\Http\Controllers\GraphqlController;

Route::post('/graphql', [GraphqlController::class, 'index']);