<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Set umask to allow group write permissions on created files (0002 = 775 for dirs, 664 for files)
umask(0002);

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
	->withMiddleware(function (Middleware $middleware) {
		$middleware->alias([
			'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
			'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
			'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
			'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
			'admin' => \App\Http\Middleware\Admin::class,			

		]);
	})	

    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
