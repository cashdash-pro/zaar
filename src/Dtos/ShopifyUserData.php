<?php

namespace CashDash\Zaar\Dtos;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class ShopifyUserData implements Castable
{
    public function __construct(

    ) {}

    public static function fromExchange(mixed $json): self
    {
        return new self;
    }

    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class implements CastsAttributes
        {
            public function get($model, $key, $value, $attributes): ?ShopifyUserData
            {
                $data = json_decode($value, true);

                return $data ? new ShopifyUserData(...$data) : null;
            }

            public function set($model, $key, $value, $attributes): false|string|null
            {
                if ($value === null) {
                    return null;
                }

                return json_encode([
                    'user_id' => $value->user_id,
                    'first_name' => $value->first_name,
                    'last_name' => $value->last_name,
                    'email' => $value->email,
                    'email_verified' => $value->email_verified,
                    'account_owner' => $value->account_owner,
                    'locale' => $value->locale,
                    'collaborator' => $value->collaborator,
                    'user_scopes' => $value->user_scopes,
                ]);
            }
        };
    }
}
