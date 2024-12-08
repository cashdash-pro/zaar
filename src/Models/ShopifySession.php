<?php

namespace CashDash\Zaar\Models;

use CashDash\Zaar\Dtos\OfflineSessionData;
use CashDash\Zaar\Dtos\OnlineSessionData;
use CashDash\Zaar\Dtos\ShopifyUserData;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopifySession extends Model
{
    use HasTimestamps;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'user_id',
        'shop',
        'expires_at',
        'access_token',
        'state',
        'scope',
        'is_online',
        'first_name',
        'last_name',
        'email',
        'email_verified',
        'account_owner',
        'locale',
        'collaborator',
        'user_scopes',
    ];

    public static function createFromOnline(OnlineSessionData $session): self {}

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function toData(): OnlineSessionData|OfflineSessionData
    {
        return $this->is_online ? OnlineSessionData::fromModel($this) : OfflineSessionData::fromModel($this);
    }

    protected function casts(): array
    {
        return [
            'expires_at' => 'immutable_datetime',
            'user_data' => ShopifyUserData::class,
            'access_token' => 'encrypted',
            'is_online' => 'boolean',
        ];
    }
}
