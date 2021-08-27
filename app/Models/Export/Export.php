<?php

namespace App\Models\Export;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperExport
 */
class Export extends Model
{
    protected $fillable = [
        'name',
        'type',
    ];

    /**
     * @return HasMany
     */
    public function options(): HasMany
    {
        return $this->hasMany(ExportOption::class);
    }
}
