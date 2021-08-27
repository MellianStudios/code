<?php

namespace App\Models\Export;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperExportLog
 */
class ExportLog extends Model
{
    protected $fillable = [
        'export_id',
        'export_option_id',
        'title',
        'status',
        'data',
    ];
}
