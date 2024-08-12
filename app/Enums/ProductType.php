<?php 
namespace App\Enums;
enum ProductType: string
{
    case VEG = 'veg';
    case NONVEG = 'non-veg';
    case VEGAN = 'vegan';
}