<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $title
 * @property string $url
 * @property string $feed_url
 * @property string $notify_email
 * @property bool $is_active
 * @property Carbon|null $last_checked_at
 * @property string|null $last_error
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Blog extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'url',
        'feed_url',
        'notify_email',
        'is_active',
        'last_checked_at',
        'last_error',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_checked_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<BlogPost, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(BlogPost::class);
    }

    /**
     * @return HasOne<BlogPost, $this>
     */
    public function latestPost(): HasOne
    {
        return $this->hasOne(BlogPost::class)->latestOfMany('published_at');
    }
}
