<?php

namespace App\Console\Commands;

use App\Models\SheetData;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ImportSheetFromXls extends Command
{
    protected $signature = 'app:import-sheet-xls {file : Path to JSON file exported from XLSX} {--clear : Clear existing data before import}';

    protected $description = 'Import configuration data from JSON file (exported from XLSX) into sheet_data table';

    public function handle()
    {
        $file = $this->argument('file');

        if (! file_exists($file)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        $this->info("Reading file: {$file}");

        $json = file_get_contents($file);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Invalid JSON: ' . json_last_error_msg());
            return 1;
        }

        $this->info("Parsed " . count($data) . " records");

        if (empty($data)) {
            $this->warn('No data to import.');
            return 0;
        }

        if ($this->option('clear')) {
            $this->warn('Clearing existing sheet_data...');
        }

        DB::beginTransaction();

        try {
            if ($this->option('clear')) {
                SheetData::where('id', '>', 0)->delete();
            }

            $bar = $this->output->createProgressBar(count($data));
            $bar->start();

            foreach (array_chunk($data, 100) as $chunk) {
                SheetData::insert($chunk);
                $bar->advance(count($chunk));
            }

            $bar->finish();
            $this->newLine();

            DB::commit();

            $this->info('Import completed. Total records in sheet_data: ' . SheetData::count());

            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Import failed: ' . $e->getMessage());
            return 1;
        }
    }
}
