<?php

namespace Modules\AgenticAI\Tools;

use Modules\AgenticAI\Interfaces\ToolInterface;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class ExportDocumentTool extends BaseTool implements ToolInterface
{
    public function name(): string
    {
        return 'generate_document';
    }

    public function description(): string
    {
        return 'IMMEDIATELY generate a downloadable file (PDF, Excel, CSV, or Word) from any data or text. Use this whenever the user asks for a document, report, download, or export. DO NOT ask for permission, just execute this tool.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'title' => [
                    'type' => 'string',
                    'description' => 'The title of the document.'
                ],
                'content' => [
                    'type' => 'string',
                    'description' => 'Text or HTML content for the document. Use this for letters, reports, or free-form text.'
                ],
                'data_json' => [
                    'type' => 'string',
                    'description' => 'JSON string representing an array of data objects (e.g., list of employees). Use this for structured data export (Excel/CSV).'
                ],
                'type' => [
                    'type' => 'string',
                    'enum' => ['pdf', 'excel', 'csv', 'docx', 'doc'],
                    'default' => 'pdf',
                    'description' => 'The file format to generate. Use "excel" for spreadsheets.'
                ]
            ],
            'required' => ['title']
        ];
    }

    public function execute(array $args): array
    {
        $title = $args['title'];
        $type = strtolower($args['type'] ?? 'pdf');
        
        // Handle Structured Data (Excel/CSV/PDF/Docx)
        if (!empty($args['data_json'])) {
            $data = json_decode($args['data_json'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                return $this->exportStructuredData($title, $data, $type);
            }
        }
        
        // Fallback: Handle Text/HTML Content
        $content = $args['content'] ?? '';
        $content = $this->parseMarkdownToHtml($content); // Parse Markdown before processing
        
        if ($type === 'excel' || $type === 'csv') {
            $plainText = strip_tags($content);
            $data = [['Content' => $plainText]]; 
            return $this->exportStructuredData($title, $data, $type);
        }

        if ($type === 'docx' || $type === 'doc') {
            return $this->generateDocxFromHtml($title, $content);
        }

        return $this->generatePdfFromHtml($title, $content);
    }

    private function parseMarkdownToHtml(string $content): string
    {
        // 1. Convert Markdown Tables to HTML Tables
        // Matches | Header | \n | --- | \n | Row |
        if (preg_match('/\|.*\|[\r\n]+\|[-:| ]+\|/', $content)) {
            $lines = explode("\n", str_replace("\r", "", $content));
            $isTable = false;
            $htmlBuffer = [];
            $tableBuffer = [];

            foreach ($lines as $line) {
                $trimmed = trim($line);
                if (str_starts_with($trimmed, '|') && str_ends_with($trimmed, '|')) {
                    if (!$isTable) $isTable = true;
                    $tableBuffer[] = $trimmed;
                } else {
                    if ($isTable) {
                        $htmlBuffer[] = $this->convertMarkdownTableToHtml($tableBuffer);
                        $tableBuffer = [];
                        $isTable = false;
                    }
                    $htmlBuffer[] = $line;
                }
            }
            if (!empty($tableBuffer)) {
                $htmlBuffer[] = $this->convertMarkdownTableToHtml($tableBuffer);
            }
            $content = implode("\n", $htmlBuffer);
        }

        // 2. Simple Markdown formatting
        $content = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $content); // Bold
        $content = preg_replace('/### (.*?)$/m', '<h3>$1</h3>', $content); // H3
        $content = preg_replace('/## (.*?)$/m', '<h2>$1</h2>', $content); // H2
        $content = preg_replace('/# (.*?)$/m', '<h1>$1</h1>', $content); // H1
        
        return $content;
    }

    private function convertMarkdownTableToHtml(array $lines): string
    {
        if (count($lines) < 2) return implode("\n", $lines);

        $html = '<table border="1" style="width:100%; border-collapse: collapse; margin: 10px 0;">';
        
        // Parse Header
        $headerLine = array_shift($lines);
        $headerCols = array_filter(explode('|', trim($headerLine, '|')));
        $html .= '<thead><tr>';
        foreach ($headerCols as $col) {
            $html .= '<th style="background:#f2f2f2; padding:8px;">' . trim($col) . '</th>';
        }
        $html .= '</tr></thead>';

        // Skip Separator Line
        if (!empty($lines) && str_contains($lines[0], '---')) {
            array_shift($lines);
        }

        // Parse Rows
        $html .= '<tbody>';
        foreach ($lines as $line) {
            $cols = explode('|', trim($line, '|'));
            $html .= '<tr>';
            foreach ($cols as $col) {
                $html .= '<td style="padding:8px; border:1px solid #ddd;">' . trim($col) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';

        return $html;
    }

    //Sanket v2.0 - always ensure generated-docs dir exists; use /api/ai/download/{filename} route for correct Content-Disposition headers so browser prompts a download
    private function ensureGeneratedDocsDir(): void
    {
        if (!Storage::disk('real_public')->exists('generated-docs')) {
            Storage::disk('real_public')->makeDirectory('generated-docs');
        }
    }

    private function buildDownloadUrl(string $filename): string
    {
        return url('/api/ai/download/' . $filename);
    }

    private function exportStructuredData(string $title, array $data, string $type)
    {
        $filename = Str::slug($title) . '-' . time();
        $path = 'generated-docs/' . $filename;
        
        $downloadFilename = $filename . '.pdf';
        if ($type === 'excel') {
            $downloadFilename = $filename . '.xlsx';
            $path .= '.xlsx';
            $this->ensureGeneratedDocsDir();
            \Maatwebsite\Excel\Facades\Excel::store(
                new \Modules\AgenticAI\Exports\GenericExport($data), 
                $path, 
                'real_public'
            );
            $label = "Excel";
        } elseif ($type === 'csv') {
            $downloadFilename = $filename . '.csv';
            $path .= '.csv';
            $this->ensureGeneratedDocsDir();
            \Maatwebsite\Excel\Facades\Excel::store(
                new \Modules\AgenticAI\Exports\GenericExport($data), 
                $path, 
                'real_public',
                \Maatwebsite\Excel\Excel::CSV
            );
            $label = "CSV";
        } elseif ($type === 'docx' || $type === 'doc') {
             $html = $this->convertDataToHtml($title, $data);
             return $this->generateDocxFromHtml($title, $html);
        } else {
            // PDF from Data
            $html = $this->convertDataToHtml($title, $data);
            return $this->generatePdfFromHtml($title, $html);
        }

        $url = $this->buildDownloadUrl($downloadFilename);

        return [
            'success' => true,
            'message' => "{$label} file generated successfully.",
            'filename' => $downloadFilename,
            'url' => $url,
            'download_link' => "<a href='" . e($url) . "' target='_blank' class='download-btn'>Download " . e($title) . " ({$label})</a>"
        ];
    }

    private function convertDataToHtml(string $title, array $data)
    {
        if (empty($data)) return "<h1>No Data</h1>";
        
        $headers = array_keys((array)$data[0]);
        $rows = '';
        
        foreach ($data as $row) {
            $rows .= '<tr>';
            foreach ($row as $cell) {
                $val = is_array($cell) ? json_encode($cell) : htmlspecialchars((string)$cell);
                $rows .= "<td>{$val}</td>";
            }
            $rows .= '</tr>';
        }
        
        $thead = '';
        foreach ($headers as $h) {
            $thead .= "<th>" . ucwords(str_replace('_', ' ', $h)) . "</th>";
        }

        return "<table class='table'><thead><tr>{$thead}</tr></thead><tbody>{$rows}</tbody></table>";
    }

    private function generateDocxFromHtml(string $title, string $htmlContent)
    {
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();
        
        $section->addText($title, ['bold' => true, 'size' => 16, 'underline' => 'single']);
        $section->addTextBreak(1);
        \PhpOffice\PhpWord\Shared\Html::addHtml($section, $htmlContent, false, false);
        
        $filename = Str::slug($title) . '-' . time() . '.docx';
        $path = 'generated-docs/' . $filename;
        
        $this->ensureGeneratedDocsDir();

        $fullPath = Storage::disk('real_public')->path($path);
        
        try {
            $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
            $objWriter->save($fullPath);
        } catch (\Exception $e) {
            \Log::error("PhpWord Save Failed: " . $e->getMessage());
            return ['success' => false, 'message' => "Failed to generate Word document: " . $e->getMessage()];
        }
        
        $url = $this->buildDownloadUrl($filename);
        
        \Log::info("Generated Docx: $filename at $url");
        
        return [
            'success' => true,
            'message' => "Word document generated successfully.",
            'filename' => $filename,
            'url' => $url,
            'download_link' => "<a href='" . e($url) . "' target='_blank' class='download-btn'>Download " . e($title) . " (Docx)</a>"
        ];
    }

    private function generatePdfFromHtml(string $title, string $content)
    {
        // content is already HTML or parsed Markdown here
        
        $html = "
        <html>
        <head>
            <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/>
            <style>
                body { font-family: sans-serif; line-height: 1.6; color: #333; padding: 20px; }
                h1 { border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 12px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                .chat-message { margin-bottom: 15px; padding: 10px; border-left: 3px solid #ddd; }
                .chat-message.user { background-color: #f0f8ff; border-left-color: #4CAF50; }
                .chat-message.assistant { background-color: #fff8f0; border-left-color: #2196F3; }
                .chat-role { font-weight: bold; margin-bottom: 5px; color: #555; }
                .chat-content { margin-left: 10px; }
                .footer { margin-top: 50px; font-size: 0.8rem; color: #777; border-top: 1px solid #ddd; padding-top: 10px; }
            </style>
        </head>
        <body>
            <h1>" . e($title) . "</h1>
            <div>{$content}</div>
            <div class='footer'>Generated by Mom AI - " . date('Y-m-d H:i') . "</div>
        </body>
        </html>
        ";

        $pdf = Pdf::loadHTML($html)->setPaper('a4', 'portrait');
        $filename = Str::slug($title) . '-' . time() . '.pdf';
        $path = 'generated-docs/' . $filename;
        
        $this->ensureGeneratedDocsDir();
        Storage::disk('real_public')->put($path, $pdf->output());
        
        $url = $this->buildDownloadUrl($filename);
        
        return [
            'success' => true,
            'message' => "Document generated successfully.",
            'filename' => $filename,
            'url' => $url,
            'download_link' => "<a href='" . e($url) . "' target='_blank' class='download-btn'>Download " . e($title) . " (PDF)</a>"
        ];
    }
}

