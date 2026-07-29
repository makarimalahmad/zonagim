<?php

use App\Http\Middleware\EnsureCustomer;
use App\Http\Middleware\EnsureUserIsNotSuspended;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\RedirectAdminToPanel;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'customer' => EnsureCustomer::class,
            'frontend' => RedirectAdminToPanel::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request): string {
            if ($request->routeIs('akun.show')) {
                $request->session()->flash(
                    'status',
                    'Silakan masuk untuk melihat detail akun.',
                );
            }

            return route('login');
        });

        $middleware->web(append: [
            EnsureUserIsNotSuspended::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            SecurityHeaders::class,
        ]);

        $middleware->trustHosts(
            at: fn (): array => array_values(array_filter([
                preg_quote((string) parse_url((string) config('app.url'), PHP_URL_HOST), '/'),
                app()->environment('local') ? '127\\.0\\.0\\.1' : null,
                app()->environment('local') ? 'localhost' : null,
            ])),
            subdomains: false,
        );

        $trustedProxies = array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('TRUSTED_PROXIES', '')),
        )));

        if ($trustedProxies !== []) {
            $middleware->trustProxies(
                at: $trustedProxies,
                headers: Request::HEADER_X_FORWARDED_FOR
                    | Request::HEADER_X_FORWARDED_PORT
                    | Request::HEADER_X_FORWARDED_PROTO,
            );
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (
            Response $response,
            Throwable $_exception,
            Request $request,
        ): Response {
            $status = $response->getStatusCode();
            $components = [
                403 => 'Errors/Forbidden',
                404 => 'Errors/NotFound',
            ];

            if (
                ! isset($components[$status])
                || $request->expectsJson()
                || ! $request->acceptsHtml()
                || ! in_array($request->method(), ['GET', 'HEAD'], true)
                || ($status === 404 && $request->is('admin/*'))
            ) {
                return $response;
            }

            $errorResponse = Inertia::render($components[$status], [
                'auth' => [
                    'user' => $request->user()?->only([
                        'id',
                        'name',
                        'email',
                    ]),
                ],
            ])
                ->withViewData('errorStatus', $status)
                ->toResponse($request);

            $errorResponse->setStatusCode($status);
            $errorResponse->headers->set(
                'X-Robots-Tag',
                'noindex, nofollow, noarchive',
            );

            return $errorResponse;
        });
    })->create();
