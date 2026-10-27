<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $provider
 * @property string $api_key
 * @property string $from_address
 */
class MailSetting extends Model
{
    protected $fillable = ['provider', 'api_key', 'from_address'];

    protected $hidden = ['api_key'];

    protected function casts(): array
    {
        return ['api_key' => 'encrypted'];
    }
}
