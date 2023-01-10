<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transcode extends Model
{
    use HasFactory;

    protected $fillable = [
        'video_url',
        'video_id',
        'title',
        'description',
        'cover_time',
        'status',
        'channel_id',
        'channel_token',
        'check_try',
        'tooltip_url',
        'thumbnail_url',
        'duration',
        'hls_playlist',
        'creation_meta',
        'disk',
        'progress',
        'user_id',
        'source_video_id'
    ];
}
