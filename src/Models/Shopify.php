<?php

namespace CashDash\Zaar\Models;

use CashDash\Zaar\Concerns\HasOfflineSessions;
use CashDash\Zaar\Contracts\ProvidesOfflineSession;
use CashDash\Zaar\Contracts\ProvidesShopify;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Model;

class Shopify extends Model implements ProvidesOfflineSession, ProvidesShopify
{
    use HasOfflineSessions;
    use HasTimestamps;

    protected $fillable = [
        'shopify_id',
        'name',
        'domain',
        'email',
        'contact_email',
        'plan_name',
        'shopify_plus',
        'iana_timezone',
        'currency_code',
        'money_format',
        'money_with_currency_format',
        'primary_domain',
        'description',
    ];

    public static function findForShopifyDomain(string $domain): ?Model
    {
        return self::where('domain', $domain)->first();
    }
}
