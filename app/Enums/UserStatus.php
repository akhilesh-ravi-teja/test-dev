<?php 
namespace App\Enums;
enum UserStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
}