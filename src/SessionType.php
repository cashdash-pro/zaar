<?php

namespace CashDash\Zaar;

enum SessionType: string
{
    case ONLINE = 'online';
    case OFFLINE = 'offline';
}
