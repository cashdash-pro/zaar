<?php

namespace CashDash\Zaar\Actions\Resolvers;

use CashDash\Zaar\Concerns\Actions\AsObject;
use CashDash\Zaar\Dtos\EmbeddedAuthData;
use CashDash\Zaar\Exceptions\FailedToResolveShopException;
use CashDash\Zaar\Zaar;
use Illuminate\Http\Request;

class ResolveShopDomain
{
    use AsObject;

    /**
     * @throws FailedToResolveShopException
     */
    public function handle(Request $request, ?EmbeddedAuthData $data): string
    {
        if ($data) {
            return $data->session_token->dest;
        }

        if (! $resolver = Zaar::$resolveExternalRequest) {
            throw new FailedToResolveShopException(
                'There was no resolver set to determine the shop domain from the request. Use Zaar::resolveExternalRequestsUsing() to set a resolver.'
            );
        }

        $domain = $resolver($request);
        if (! $domain) {
            // TODO: Make a configurable shop selector route config? or a easy way to redirect
            throw new FailedToResolveShopException(
                'The resolver did not return a shop domain. Make sure the resolver is returning the correct value.'
            );
        }

        return $domain;
    }
}
