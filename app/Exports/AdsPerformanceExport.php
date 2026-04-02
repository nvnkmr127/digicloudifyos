<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class AdsPerformanceExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithTitle
{
    protected $data;

    protected $title;

    protected $headers;

    public function __construct($data, $title = 'Ads Performance', $headers = [])
    {
        $this->data = $data;
        $this->title = $title;
        $this->headers = $headers;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function title(): string
    {
        return $this->title;
    }

    public function headings(): array
    {
        return $this->headers ?: [
            'Name',
            'Spend',
            'Impressions',
            'Clicks',
            'Leads',
            'CTR (%)',
            'CPL',
            'ROAS',
        ];
    }

    public function map($row): array
    {
        return $row;
    }
}
