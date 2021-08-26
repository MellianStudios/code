<?php

namespace App\Exports\Products\ThirdParty;

use App\Models\Export\ExportOption;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class ThirdPartyExport
{
    protected FilesystemAdapter $storage;
    protected string $language;
    protected int $offset;
    protected int $take;
    protected ExportOption $export_option;
    protected bool $is_last;
    protected string $file_name;
    protected string $start_of_file;

    public function __construct()
    {
        $this->storage = Storage::disk('local');
    }

    /**
     * @param string $language
     *
     * @return $this
     */
    public function setLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @param int $offset
     * @param int $take
     *
     * @return $this
     */
    public function setChunkBoundaries(int $offset, int $take): self
    {
        $this->offset = $offset;
        $this->take = $take;

        return $this;
    }

    /**
     * @param ExportOption $export_option
     *
     * @return $this
     */
    public function setOption(ExportOption $export_option): self
    {
        $this->export_option = $export_option;

        return $this;
    }

    /**
     * @param bool $is_last
     *
     * @return $this
     */
    public function setIfIsLast(bool $is_last): self
    {
        $this->is_last = $is_last;

        return $this;
    }

    /**
     * @param string $file_name
     *
     * @return void
     */
    protected function createFileName(string $file_name): void
    {
        $this->file_name = $file_name;
    }

    /**
     * @return void
     */
    protected function checkFileExistence(): void
    {
        if (!$this->storage->exists($this->file_name)) {
            $this->storage->put($this->file_name, $this->start_of_file);
        }
    }

    /**
     * @return void
     */
    protected function refreshFileIfIsNewExport(): void
    {
        if ($this->offset === 0) {
            $this->storage->delete($this->file_name);
            $this->storage->put($this->file_name, $this->start_of_file);
        }
    }

    /**
     * @param string $file_name
     *
     * @return void
     */
    protected function prepareFile(string $file_name): void
    {
        $this->createFileName($file_name);
        $this->checkFileExistence();
        $this->refreshFileIfIsNewExport();
    }
}
