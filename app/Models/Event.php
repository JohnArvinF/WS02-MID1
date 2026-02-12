<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'title',
        'category',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'time_image',
        'time_image',
        'authors',
        'content',
        'image'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function getFormattedDateRangeAttribute()
    {
        if ($this->start_date->eq($this->end_date)) {
            return $this->start_date->format('M d, Y');
        }

        if ($this->start_date->format('Y') === $this->end_date->format('Y')) {
            return $this->start_date->format('M d') . ' - ' . $this->end_date->format('M d, Y');
        }

        return $this->start_date->format('M d, Y') . ' - ' . $this->end_date->format('M d, Y');
    }

    public function getFormattedTimeRangeAttribute()
    {
        if (!$this->start_time && !$this->end_time) {
            return '';
        }

        if ($this->start_time && !$this->end_time) {
            return $this->start_time->format('g:i A');
        }

        if (!$this->start_time && $this->end_time) {
            return 'Until ' . $this->end_time->format('g:i A');
        }

        return $this->start_time->format('g:i A') . ' - ' . $this->end_time->format('g:i A');
    }

    public function isMultiDay()
    {
        return !$this->start_date->eq($this->end_date);
    }

    public function news()
    {
        return $this->belongsToMany(Post::class, 'event_news')
            ->where('category', 'news')
            ->withTimestamps();
    }

    public function advisories()
    {
        return $this->belongsToMany(Post::class, 'event_advisories')
            ->where('category', 'advisory')
            ->withTimestamps();
    }

    public function getAvailableNewsAttribute()
    {
        return Post::where('category', 'news')->get();
    }

    public function getAvailableAdvisoriesAttribute()
    {
        return Post::where('category', 'advisory')->get();
    }
}
