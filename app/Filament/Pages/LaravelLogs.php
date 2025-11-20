<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\File;
use Filament\Notifications\Notification;

class LaravelLogs extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.laravel-logs';
    protected static ?string $navigationLabel = 'Логи системы';
    protected static ?string $navigationGroup = 'Система';
    protected static ?int $navigationSort = 5;
    protected static ?string $title = 'Логи системы';

    public $logs = [];
    public $search = '';
    public $level = 'all';
    public $perPage = 50;
    public $currentPage = 1;

    protected $queryString = [
        'search' => ['except' => ''],
        'level' => ['except' => 'all'],
        'page' => ['except' => 1],
    ];

    public function mount(): void
    {
        $this->loadLogs();
    }

    public function updatedSearch(): void
    {
        $this->currentPage = 1;
        $this->loadLogs();
    }

    public function updatedLevel(): void
    {
        $this->currentPage = 1;
        $this->loadLogs();
    }

    public function loadLogs(): void
    {
        $logsDir = storage_path('logs');
        $logFiles = [];
        
        // Получаем все файлы логов (daily формат: laravel-YYYY-MM-DD.log или single: laravel.log)
        if (File::isDirectory($logsDir)) {
            $files = File::files($logsDir);
            foreach ($files as $file) {
                $filename = $file->getFilename();
                // Проверяем, является ли файл логом Laravel
                if (preg_match('/^laravel(-\d{4}-\d{2}-\d{2})?\.log$/', $filename)) {
                    $logFiles[] = $file->getPathname();
                }
            }
        }
        
        // Сортируем по дате (новые первыми)
        usort($logFiles, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        
        if (empty($logFiles)) {
            $this->logs = [];
            return;
        }

        // Читаем все файлы логов
        $allLogs = [];
        foreach ($logFiles as $logPath) {
            if (!File::exists($logPath)) {
                continue;
            }

            // Читаем файл построчно для больших файлов
            $handle = fopen($logPath, 'r');
            if (!$handle) {
                continue;
            }

            $currentLog = null;
            $buffer = '';

            while (($line = fgets($handle)) !== false) {
                // Проверяем, начинается ли новая запись лога
                if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] local\.(\w+):\s*(.*)$/', $line, $matches)) {
                    // Сохраняем предыдущую запись
                    if ($currentLog !== null) {
                        $allLogs[] = $currentLog;
                    }
                    
                    $currentLog = [
                        'date' => $matches[1],
                        'level' => strtolower($matches[2]),
                        'message' => trim($matches[3]),
                        'stack' => '',
                        'file' => basename($logPath),
                    ];
                    $buffer = '';
                } elseif ($currentLog !== null) {
                    // Продолжение предыдущей записи
                    $buffer .= $line;
                    
                    // Проверяем, является ли это стеком вызовов
                    if (preg_match('/^(Stack trace:|#\d+)/', trim($line))) {
                        $currentLog['stack'] .= $buffer;
                        $buffer = '';
                    } elseif (empty($currentLog['stack']) && !empty(trim($line))) {
                        // Если еще нет стека, это продолжение сообщения
                        $currentLog['message'] .= "\n" . trim($line);
                    }
                }
            }
            
            // Добавляем последнюю запись из этого файла
            if ($currentLog !== null) {
                if (!empty($buffer)) {
                    $currentLog['stack'] .= $buffer;
                }
                $allLogs[] = $currentLog;
            }
            
            fclose($handle);
        }
        
        $parsedLogs = $allLogs;

        // Фильтрация по уровню
        if ($this->level !== 'all') {
            $parsedLogs = array_filter($parsedLogs, function ($log) {
                return $log['level'] === $this->level;
            });
        }

        // Фильтрация по поисковому запросу
        if (!empty($this->search)) {
            $search = strtolower($this->search);
            $parsedLogs = array_filter($parsedLogs, function ($log) use ($search) {
                return 
                    str_contains(strtolower($log['message']), $search) ||
                    str_contains(strtolower($log['stack']), $search) ||
                    str_contains(strtolower($log['date']), $search);
            });
        }

        // Реверс массива (новые логи первыми)
        $parsedLogs = array_reverse($parsedLogs);
        
        // Пагинация
        $total = count($parsedLogs);
        $offset = ($this->currentPage - 1) * $this->perPage;
        $this->logs = array_slice($parsedLogs, $offset, $this->perPage);
        
        $this->totalPages = ceil($total / $this->perPage);
    }

    public function getTotalPagesProperty(): int
    {
        return $this->totalPages ?? 1;
    }

    public function nextPage(): void
    {
        if ($this->currentPage < $this->totalPages) {
            $this->currentPage++;
            $this->loadLogs();
        }
    }

    public function previousPage(): void
    {
        if ($this->currentPage > 1) {
            $this->currentPage--;
            $this->loadLogs();
        }
    }

    public function goToPage($page): void
    {
        $this->currentPage = $page;
        $this->loadLogs();
    }

    public function clearLogs(): void
    {
        $logsDir = storage_path('logs');
        $cleared = 0;
        
        if (File::isDirectory($logsDir)) {
            $files = File::files($logsDir);
            foreach ($files as $file) {
                $filename = $file->getFilename();
                // Очищаем все файлы логов Laravel
                if (preg_match('/^laravel(-\d{4}-\d{2}-\d{2})?\.log$/', $filename)) {
                    File::put($file->getPathname(), '');
                    $cleared++;
                }
            }
        }
        
        $this->loadLogs();
        
        Notification::make()
            ->title("Очищено файлов логов: {$cleared}")
            ->success()
            ->send();
    }

    public function getLevelColor($level): string
    {
        return match($level) {
            'error' => 'text-red-600 bg-red-50',
            'warning' => 'text-yellow-600 bg-yellow-50',
            'info' => 'text-blue-600 bg-blue-50',
            'debug' => 'text-gray-600 bg-gray-50',
            default => 'text-gray-600 bg-gray-50',
        };
    }
}

