<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'title',
        'category',
        'date',
        'time',
        'authors',
        'content',
        'image',
    ];

    public function newsEvents()
    {
        return $this->belongsToMany(Event::class, 'event_news')
            ->withTimestamps();
    }

    public function advisoryEvents()
    {
        return $this->belongsToMany(Event::class, 'event_advisories')
            ->withTimestamps();
    }

    public function events()
    {
        if ($this->category === 'news') {
            return $this->newsEvents();
        } elseif ($this->category === 'advisory') {
            return $this->advisoryEvents();
        }
        
        return collect();
    }
}
