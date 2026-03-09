<?php

namespace App\Exports;

use App\Models\Campaign;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CampaignsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    protected string $organizationId;

    protected array $filters;

    public function __construct(string $organizationId, array $filters = [])
    {
        $this->organizationId = $organizationId;
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Campaign::where('organization_id', $this->organizationId)
            ->with(['client', 'adAccount']);

        if (isset($this->filters['status']) && $this->filters['status'] !== 'all') {
            $query->where('status', $this->filters['status']);
        }

        if (isset($this->filters['client_id'])) {
            $query->where('client_id', $this->filters['client_id']);
        }

        if (isset($this->filters['date_from'])) {
            $query->whereDate('start_date', '>=', $this->filters['date_from']);
        }

        if (isset($this->filters['date_to'])) {
            $query->whereDate('start_date', '<=', $this->filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Campaign ID',
            'Campaign Name',
            'Client',
            'Platform',
            'Objective',
            'Status',
            'Start Date',
            'End Date',
            'Daily Budget',
            'Lifetime Budget',
            'Created At',
        ];
    }

    public function map($campaign): array
    {
        return [
            $campaign->id,
            $campaign->name,
            $campaign->client?->name ?? 'N/A',
            $campaign->adAccount?->platform ?? 'N/A',
            $campaign->objective,
            $campaign->status,
            $campaign->start_date?->format('Y-m-d') ?? 'N/A',
            $campaign->end_date?->format('Y-m-d') ?? 'N/A',
            $campaign->daily_budget ? '$'.number_format($campaign->daily_budget, 2) : 'N/A',
            $campaign->lifetime_budget ? '$'.number_format($campaign->lifetime_budget, 2) : 'N/A',
            $campaign->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
