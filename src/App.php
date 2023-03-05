<?php

namespace App;

use App\Services\CSVService;

class App
{
    /**
     * Виконує формування та завантаження CSV файлу
     *
     * @return void
     */
    public function getCSVFile(): void
    {
        $csv_service = new CSVService();

        $csv_service->downloadCSV();
    }
}