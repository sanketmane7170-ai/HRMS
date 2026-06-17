<?php

namespace App\Services\Recruitment;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    /**
     * Upload resume file for recruitment application
     */
    public function uploadResume(UploadedFile $file, $jobId = null, $candidateEmail = null): string
    {
        // Generate unique filename
        $extension = $file->getClientOriginalExtension();
        $filename = $this->generateResumeFilename($file->getClientOriginalName(), $jobId, $candidateEmail, $extension);
        
        // Define upload path
        $path = 'recruitment/resumes/' . date('Y/m');
        
        // Store file
        $filePath = Storage::disk('public')->putFileAs($path, $file, $filename);
        
        // Return the storage path
        return $filePath;
    }
    
    /**
     * Upload offer letter document
     */
    public function uploadOfferLetter(UploadedFile $file, $applicationId): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename = 'offer_letter_' . $applicationId . '_' . time() . '.' . $extension;
        
        $path = 'recruitment/offers/' . date('Y/m');
        
        $filePath = Storage::disk('public')->putFileAs($path, $file, $filename);
        
        return $filePath;
    }
    
    /**
     * Delete uploaded file
     */
    public function deleteFile(string $filePath): bool
    {
        if (Storage::disk('public')->exists($filePath)) {
            return Storage::disk('public')->delete($filePath);
        }
        
        return false;
    }
    
    /**
     * Get file URL for display/download
     */
    public function getFileUrl(string $filePath): string
    {
        return Storage::disk('public')->url($filePath);
    }
    
    /**
     * Validate file type and size
     */
    public function validateResumeFile(UploadedFile $file): array
    {
        $errors = [];
        
        // Check file size (5MB max)
        if ($file->getSize() > 5242880) {
            $errors[] = 'File size must not exceed 5MB';
        }
        
        // Check file type
        $allowedMimes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            $errors[] = 'File must be a PDF, DOC, or DOCX document';
        }
        
        // Check file extension
        $allowedExtensions = ['pdf', 'doc', 'docx'];
        if (!in_array(strtolower($file->getClientOriginalExtension()), $allowedExtensions)) {
            $errors[] = 'File extension must be pdf, doc, or docx';
        }
        
        return $errors;
    }
    
    /**
     * Generate unique resume filename
     */
    private function generateResumeFilename($originalName, $jobId, $candidateEmail, $extension): string
    {
        $cleanName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
        $emailPrefix = $candidateEmail ? Str::slug(explode('@', $candidateEmail)[0]) : 'candidate';
        $jobPrefix = $jobId ? "job_{$jobId}" : 'application';
        
        return "{$jobPrefix}_{$emailPrefix}_{$cleanName}_" . time() . ".{$extension}";
    }
    
    /**
     * Get file size in human readable format
     */
    public function getFileSize(string $filePath): string
    {
        if (!Storage::disk('public')->exists($filePath)) {
            return 'Unknown';
        }
        
        $bytes = Storage::disk('public')->size($filePath);
        
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;
        
        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }
        
        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }
}
