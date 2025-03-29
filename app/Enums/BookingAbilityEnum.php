<?php

namespace App\Enums;


enum BookingAbilityEnum: string
{
    case BOOKING_CREATE = 'booking:create';
    case BOOKING_SHOW = 'booking:show';
    case BOOKING_DESTROY = 'booking:destroy';
}
