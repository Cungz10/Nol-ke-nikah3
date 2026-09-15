<?php

namespace App\Http\Controllers;

use App\Concerns\LogsActivity;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    use AuthorizesRequests, LogsActivity;
}
