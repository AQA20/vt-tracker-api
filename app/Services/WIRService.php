<?php

namespace App\Services;

use App\Models\Unit;
use App\Models\WIRUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class WIRService
{
    /**
     * Upload a WIR file for a unit.
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    public function uploadWIR(
        Unit $unit,
        UploadedFile $file,
        string $progressGroup,
    ): WIRUpload {
        // Validate file type
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        if (! in_array($file->getMimeType(), $allowedMimes)) {
            abort(422, 'Invalid file type. Allowed types: jpg, png, webp');
        }

        // Validate file size (5MB max)
        if ($file->getSize() > 5 * 1024 * 1024) {
            abort(422, 'File size exceeds 5MB limit');
        }

        // Delete existing upload for this progress group
        $existing = $unit->wirUploads()->where('progress_group', $progressGroup)->first();
        if ($existing) {
            Storage::disk('public')->delete($existing->file_path);
            $existing->delete();
        }

        // Store the file
        $path = $file->store("wirs/{$unit->id}", 'public');
        if (! $path) {
            abort(500, 'Failed to store WIR file');
        }

        // Create WIR upload record
        $wirUpload = $unit->wirUploads()->create([
            'progress_group' => $progressGroup,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'uploaded_by' => auth()->id(),
        ]);

        return $wirUpload;
    }

    /**
     * Delete a WIR upload.
     */
    public function deleteWIR(WIRUpload $wir): void
    {
        Storage::disk('public')->delete($wir->file_path);
        $wir->delete();
    }

    /**
     * Get WIR uploads by unit and progress group.
     */
    public function getWIRByGroup(Unit $unit, string $progressGroup): ?WIRUpload
    {
        return $unit->wirUploads()
            ->where('progress_group', $progressGroup)
            ->first();
    }
}
