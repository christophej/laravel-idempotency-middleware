<?php

    namespace BrokenTitan\Idempotency\Middleware;

    use Closure;
    use Illuminate\Http\Request;
     
    class Idempotency {
        public function handle(Request $request, Closure $next, ?string $header = null, ?array $methods = null, ?int $expiration = null) {
            if (in_array($request->method(), $methods ?? config("idempotency.methods"))) {
                return $next($request);
            }

            $requestId = $request->header($header ?? config("idempotency.header"));
            if (!$requestId) {
                return $next($request);
            }

            if (!($response = cache($requestId))) {
                $response = $next($request);
            }

            cache([$requestId => $response], now()->addMinutes($expiration ?? config("idempotency.expiration"));

            return $response;
        }
    }