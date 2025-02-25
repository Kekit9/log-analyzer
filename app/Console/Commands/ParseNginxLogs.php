<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LogEntry;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ParseNginxLogs extends Command
{
    protected $signature = 'logs:parse {file}'; //определяет синтаксис вызова команды
    protected $description = 'Parse Nginx log file and store in database'; // описание команды

    public function handle()
    {
        $file = $this->argument('file');

        if (!file_exists($file)) {
            $this->error("File not found: $file");
            return;
        }

        $batchSize = 1000; // для обработки больших логов их записи будут сортироваться в пакеты по 1к
        $batch = [];

        $handle = fopen($file, 'r');
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $log = json_decode($line, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->error('Invalid JSON format: ' . $line);
                    continue;
                }

                $entry = $this->parseLogEntry($log);

                if ($entry) {
                    $batch[] = $entry;

                    if (count($batch) >= $batchSize) {
                        $this->saveBatch($batch);
                        $batch = [];
                    }
                }
            }
            fclose($handle);
        }

        if (!empty($batch)) {
            $this->saveBatch($batch);
        }

        $this->info('Logs parsed and saved successfully.');
    }

    private function parseLogEntry($log)
    {
        try {
            $date = Carbon::createFromFormat('[d/M/Y:H:i:s O]', $log['date']);
        } catch (\Exception $e) {
            $this->error("Failed to parse date: " . $log['date']);
            return null;
        }

        return [
            'client_ip' => $log['client_ip'] ?? null,
            'date' => $date,
            'http_info' => $log['http_info'] ?? null,
            'error_code' => $log['error_code'] ?? null,
            'response_size' => $log['response_size'] ?? null,
            'referer_ip' => $log['referer_ip'] ?? null,
            'referer_ip_host' => $log['referer_ip_host'] ?? null,
            'user_agent' => $log['user_agent'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function saveBatch($batch)
    {
        try {
            LogEntry::insert($batch);
            $this->info("Saved batch of " . count($batch) . " entries.");
        } catch (\Exception $e) {
            $this->error("Failed to save batch: " . $e->getMessage());
            Log::error("Failed to save batch: " . $e->getMessage(), [
                'batch' => $batch,
                'exception' => $e,
            ]);
        }
    }
}
