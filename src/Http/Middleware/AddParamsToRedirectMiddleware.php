<?php

namespace CashDash\Zaar\Http\Middleware;

use CashDash\Zaar\Actions\TokenExchangeAuth\GetTokenFromRequest;
use CashDash\Zaar\Zaar;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AddParamsToRedirectMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($response instanceof RedirectResponse) {
            $targetUrl = $response->getTargetUrl();

            $shop = Zaar::session()?->shop ?? $request->get('shop');
            $embedded = $request->get('embedded') ?? Zaar::isEmbedded();

            $id_token = GetTokenFromRequest::make()->handle($request);

            $params = http_build_query(compact('shop', 'embedded', 'id_token'));
            $targetUrl = $targetUrl.(parse_url($targetUrl, PHP_URL_QUERY) ? '&' : '?').$params;
            $response->setTargetUrl($targetUrl);
        }

        return $response;

    }
}
