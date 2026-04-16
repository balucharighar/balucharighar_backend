<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Feedback extends Model
{
    use HasFactory;

    protected $table = 'feedbacks';

    protected $fillable = [
        'name',
        'city',
        'useful',
        'liked',
        'confused',

        'pay',
        'price',

        'improvement',
        'feature',

        'rating'
    ];

    protected $casts = [
        'rating' => 'integer',
    ];
}
