<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderPrint;
use Illuminate\Console\Command;
use App\Services\GoogleClient;

class ResyncSheet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:resync-sheet {--force : Принудительно переотправить все заявки}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Принудительная синхронизация всех заявок в Google таблицу';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('force')) {
            $this->info('Удаление флагов отправки для всех заявок...');
            OrderPrint::truncate();
            $this->info('Флаги удалены.');
        }

        $this->info('Начинаю синхронизацию заявок в Google таблицу...');
        
        $orders = Order::orderBy('id')->get();
        $total = $orders->count();
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $successCount = 0;
        $errorCount = 0;

        foreach ($orders as $order) {
            try {
                $data = $order->prepareSheetData();
                // Используем forceUpdate=true для обновления существующих заявок
                GoogleClient::write($data[0], true);
                
                // Обновляем или создаем флаг отправки
                $order->print()->firstOrCreate();
                $successCount++;
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Ошибка при отправке заявки #{$order->id}: " . $e->getMessage());
                $errorCount++;
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Синхронизация завершена!");
        $this->info("Успешно отправлено: {$successCount}");
        
        if ($errorCount > 0) {
            $this->warn("Ошибок: {$errorCount}");
        }

        return Command::SUCCESS;
    }
}

