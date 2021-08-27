<?php

namespace App\Console\Commands;

use App\Exports\Products\ThirdParty\ThirdPartyExportAbstract;
use App\Jobs\ProcessChunkOfProductExport;
use App\Models\Export\Export;
use App\Models\Export\ExportLog;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class ExportProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates queued jobs for every product export to third party services';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        // TODO: vyriesit do akeho priecinka pojdu exporty
        $count_per_chunk = Config::get('export.products.count_per_chunk');

        $product_count = Product::count();

        if ($product_count === 0) {
            return;
        }

        $chunk_count = (int)ceil($product_count / $count_per_chunk);

        $exports = Export::where('type', 'product')->with(['options'])->get();

        $namespace = Config::get('export.products.namespace_of_exports');

        foreach ($exports as $export) {
            $current_namespace = $namespace . $export->name;
            $exporter_instance = new $current_namespace();

            if (!is_subclass_of($exporter_instance, ThirdPartyExportAbstract::class)) {
                ExportLog::create([
                    'export_id' => 1,
                    'title' => $export->name . ' not found',
                    'status' => 'error',
                ]);

                continue;
            }

            foreach ($export->options as $export_option) {
                $iterator = 0;

                $exporter_instance->setLanguage($export_option->language)
                    ->setOption($export_option);

                while ($iterator < $chunk_count) {
                    $offset = $iterator * $count_per_chunk;

                    $is_last = ($iterator + 1) === $chunk_count;

                    $exporter_instance->setChunkBoundaries($offset, $count_per_chunk)
                        ->setIfIsLast($is_last);

                    ProcessChunkOfProductExport::dispatch($export, $export_option, $exporter_instance, $is_last)
                        ->onQueue('export_products');

                    $iterator++;
                }
            }
        }
    }
}
