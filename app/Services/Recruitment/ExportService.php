<?php

namespace App\Services\Recruitment;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportService
{
    /**
     * Prepare application data for export.
     */
    public function prepareApplicationData(Collection $applications, array $fields): array
    {
        $exportData = [];
        
        foreach ($applications as $application) {
            $row = [];
            
            foreach ($fields as $field) {
                $row[$field] = match($field) {
                    'name' => $application->candidate_name ?? $application->user?->name ?? 'N/A',
                    'email' => $application->candidate_email ?? $application->user?->email ?? 'N/A',
                    'phone' => $application->candidate_phone ?? $application->user?->phone ?? 'N/A',
                    'stage' => $application->formatted_stage,
                    'score' => $application->score ?? 'Not Scored',
                    'applied_date' => $application->applied_on->format('Y-m-d H:i:s'),
                    'job_title' => $application->job?->title ?? 'N/A',
                    'department' => $application->job?->department?->name ?? 'N/A',
                    'experience_years' => $application->years_of_experience ?? 'N/A',
                    'expected_salary' => $application->expected_salary ?? 'N/A',
                    'linkedin_url' => $application->linkedin_url ?? 'N/A',
                    'recommendation' => $application->recommendation ?? 'N/A',
                    default => 'N/A'
                };
            }
            
            $exportData[] = $row;
        }
        
        return $exportData;
    }

    /**
     * Export data to CSV format.
     */
    public function exportToCsv(array $data, string $filename): string
    {
        if (empty($data)) {
            throw new \InvalidArgumentException('No data to export');
        }

        $headers = array_keys($data[0]);
        
        $filePath = storage_path("app/exports/{$filename}");
        $this->ensureExportDirectory();
        
        $handle = fopen($filePath, 'w');
        
        // Write headers
        fputcsv($handle, $headers);
        
        // Write data rows
        foreach ($data as $row) {
            fputcsv($handle, $row);
        }
        
        fclose($handle);
        
        return $filePath;
    }

    /**
     * Export data to Excel format.
     */
    public function exportToExcel(array $data, string $filename): string
    {
        if (empty($data)) {
            throw new \InvalidArgumentException('No data to export');
        }

        $this->ensureExportDirectory();
        $filePath = storage_path("app/exports/{$filename}");
        
        $export = new ApplicationsExport($data);
        Excel::store($export, "exports/{$filename}");
        
        return $filePath;
    }

    /**
     * Export data to PDF format.
     */
    public function exportToPdf(array $data, string $filename): string
    {
        if (empty($data)) {
            throw new \InvalidArgumentException('No data to export');
        }

        $this->ensureExportDirectory();
        
        $headers = array_keys($data[0]);
        
        $pdf = Pdf::loadView('recruitment::exports.applications-pdf', [
            'headers' => $headers,
            'data' => $data,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'total_records' => count($data)
        ]);
        
        $filePath = storage_path("app/exports/{$filename}");
        $pdf->save($filePath);
        
        return $filePath;
    }

    /**
     * Generate filename based on format and timestamp.
     */
    public function generateFilename(string $format): string
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $extension = match($format) {
            'csv' => 'csv',
            'excel' => 'xlsx',
            'pdf' => 'pdf',
            default => 'txt'
        };
        
        return "recruitment_applications_{$timestamp}.{$extension}";
    }

    /**
     * Ensure export directory exists.
     */
    private function ensureExportDirectory(): void
    {
        $exportPath = storage_path('app/exports');
        
        if (!is_dir($exportPath)) {
            mkdir($exportPath, 0755, true);
        }
    }

    /**
     * Clean up old export files.
     */
    public function cleanupOldFiles(int $daysOld = 7): int
    {
        $exportPath = storage_path('app/exports');
        $cutoffTime = now()->subDays($daysOld)->timestamp;
        $deletedCount = 0;
        
        if (!is_dir($exportPath)) {
            return 0;
        }
        
        $files = glob($exportPath . '/*');
        
        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $cutoffTime) {
                unlink($file);
                $deletedCount++;
            }
        }
        
        return $deletedCount;
    }
}
