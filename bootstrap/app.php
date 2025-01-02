
<?php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Session\Middleware\StartSession;
return Application::configure(basePath: dirname(DIR))
  ->withRouting(
    web: DIR . "/../routes/web.php",
    commands: DIR . "/../routes/console.php",
    health: "/up",
    then: function ($router) {
      Route::prefix("api/")
        ->middleware("api")
        ->name("api")
        ->group(base_path("routes/api.php"));
    }
  )
  ->withMiddleware(function (Middleware $middleware) {
    $middleware->validateCsrfTokens(except: ["api/*"]);
    $middleware->append(StartSession::class);
  })
  ->withExceptions(function (Exceptions $exceptions) {
    //
  })
  ->create();