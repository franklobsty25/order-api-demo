<?php

namespace App\Enums;

enum Category: string
{
    case Food = 'food';
    case Fruit = 'fruit';
    case Cereal = 'cereal';
    case Grain = 'grain';
    case Vegetable = 'vegetable';
    case Continental = 'continental';
    case Other = 'other';
}
