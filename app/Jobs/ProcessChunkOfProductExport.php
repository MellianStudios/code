<?php

namespace App\Jobs;

use App\Exports\ExportInterface;
use App\Models\Export\Export;
use App\Models\Export\ExportLog;
use App\Models\Export\ExportOption;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessChunkOfProductExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Export $export_model;
    protected ExportOption $export_option;
    protected ExportInterface $exporter_instance;
    protected bool $is_last;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Export $export_model, ExportOption $export_option, ExportInterface $exporter_instance, bool $is_last)
    {
        $this->export_model = $export_model;
        $this->export_option = $export_option;
        $this->exporter_instance = $exporter_instance;
        $this->is_last = $is_last;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->exporter_instance->export();

        if ($this->is_last) {
            ExportLog::create([
                'export_id' => $this->export_model->id,
                'export_option_id' => $this->export_option->id,
                'title' => 'Export for ' . $this->export_model->name . ' ' . $this->export_option->language . ' done',
                'status' => 'success',
            ]);
        }
    }
}
