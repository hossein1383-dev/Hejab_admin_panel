<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class MigrateController extends Controller
{
    public function migrateByDataBaseName(Request $request)
    {
        Artisan::call('migrate', ['--force' => true]);
        dd(Artisan::output());
    }

    public function rollBackByDataBaseName(Request $request)
    {
        Artisan::call('migrate:rollback', ['--force' => true]);
        dd(Artisan::output());
    }
}
