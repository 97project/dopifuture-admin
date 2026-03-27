<?php

declare(strict_types=1);

namespace App\Models\Vega;

use Illuminate\Database\Eloquent\Model;

/**
 * Vega remote DB — users tablosu.
 * Email ile Panel26 user → Vega user eşleştirmesi için read-only model.
 */
class VegaDbUser extends Model
{
    protected $connection = 'vega_db';
    protected $table = 'users';

    /**
     * Panel26 user email'lerinden Vega user ID'lerini bul.
     *
     * @param  array  $emails  ['user@example.com', ...]
     * @return array  ['user@example.com' => 42, ...]  email → vega_user_id map
     */
    public static function mapEmailsToIds(array $emails): array
    {
        if (empty($emails)) {
            return [];
        }

        return static::whereIn('email', $emails)
            ->pluck('id', 'email')
            ->toArray();
    }
}
