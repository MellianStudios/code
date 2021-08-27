<?php

namespace App\Models\Export;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperExportOption
 */
class ExportOption extends Model
{
    protected $fillable = [
        'language',
        'active',
    ];

    /**
     * @return BelongsTo
     */
    public function export(): BelongsTo
    {
        return $this->belongsTo(Export::class);
    }
}
