<?php

namespace App\Services;

use App\Exports\CampaignsExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ExportService
{
    public function exportCampaigns(string $organizationId, array $filters, string $format = 'xlsx'): string
    {
        $filename = 'campaigns_'.now()->format('Y-m-d_His').'.'.$format;
        $path = 'exports/'.$filename;

        match ($format) {
            'xlsx', 'xls' => $this->exportToExcel($organizationId, $filters, $path, $format),
            'csv' => $this->exportToCsv($organizationId, $filters, $path),
            'pdf' => $this->exportToPdf($organizationId, $filters, $path),
            default => throw new \InvalidArgumentException("Unsupported format: {$format}"),
        };

        return $path;
    }

    protected function exportToExcel(string $organizationId, array $filters, string $path, string $format): void
    {
        Excel::store(
            new CampaignsExport($organizationId, $filters),
            $path,
            'public',
            \Maatwebsite\Excel\Excel::XLSX
        );
    }

    protected function exportToCsv(string $organizationId, array $filters, string $path): void
    {
        Excel::store(
            new CampaignsExport($organizationId, $filters),
            $path,
            'public',
            \Maatwebsite\Excel\Excel::CSV
        );
    }

    protected function exportToPdf(string $organizationId, array $filters, string $path): void
    {
        $export = new CampaignsExport($organizationId, $filters);
        $campaigns = $export->collection();

        $pdf = Pdf::loadView('exports.campaigns-pdf', [
            'campaigns' => $campaigns,
            'generatedAt' => now()->format('Y-m-d H:i:s'),
        ]);

        Storage::disk('public')->put($path, $pdf->output());
    }

    public function getDownloadUrl(string $path): string
    {
        return Storage::disk('public')->url($path);
    }

    public function deleteExport(string $path): bool
    {
        return Storage::disk('public')->delete($path);
    }
}
