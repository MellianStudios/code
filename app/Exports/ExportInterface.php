<?php

namespace App\Exports;

interface ExportInterface
{
    /**
     * @return mixed|void
     */
    public function export();
}
