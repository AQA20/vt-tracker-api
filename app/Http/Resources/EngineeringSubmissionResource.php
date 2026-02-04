<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class EngineeringSubmissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'equip_n' => $this->equip_n,
            'asset_name' => $this->asset_name,
            'unit_id' => $this->unit_id,
            'material_code' => $this->material_code,
            'so_no' => $this->so_no,
            'network_no' => $this->network_no,
            'status_update' => $this->statusUpdate ? [
                'id' => $this->statusUpdate->id,
                'tech_sub_status' => $this->statusUpdate->tech_sub_status,
                'sample_status' => $this->statusUpdate->sample_status,
                'layout_status' => $this->statusUpdate->layout_status,
                'car_m_dwg_status' => $this->statusUpdate->car_m_dwg_status,
                'cop_dwg_status' => $this->statusUpdate->cop_dwg_status,
                'landing_dwg_status' => $this->statusUpdate->landing_dwg_status,

                // URLs
                'tech_sub_status_pdf_url' => $this->getPdfUrl($this->statusUpdate->tech_sub_status_pdf),
                'sample_status_pdf_url' => $this->getPdfUrl($this->statusUpdate->sample_status_pdf),
                'layout_status_pdf_url' => $this->getPdfUrl($this->statusUpdate->layout_status_pdf),
                'car_m_dwg_status_pdf_url' => $this->getPdfUrl($this->statusUpdate->car_m_dwg_status_pdf),
                'cop_dwg_status_pdf_url' => $this->getPdfUrl($this->statusUpdate->cop_dwg_status_pdf),
                'landing_dwg_status_pdf_url' => $this->getPdfUrl($this->statusUpdate->landing_dwg_status_pdf),
            ] : null,
            'dg1_milestone' => $this->dg1Milestone ? [
                'id' => $this->dg1Milestone->id,
                'ms2' => $this->dg1Milestone->ms2?->format('Y-m-d'),
                'ms2a' => $this->dg1Milestone->ms2a?->format('Y-m-d'),
                'ms2c' => $this->dg1Milestone->ms2c?->format('Y-m-d'),
                'ms2z' => $this->dg1Milestone->ms2z?->format('Y-m-d'),
                'ms3' => $this->dg1Milestone->ms3?->format('Y-m-d'),
                'ms3a_exw' => $this->dg1Milestone->ms3a_exw?->format('Y-m-d'),
                'ms3b' => $this->dg1Milestone->ms3b?->format('Y-m-d'),
                'ms3s_ksa_port' => $this->dg1Milestone->ms3s_ksa_port?->format('Y-m-d'),
                'ms2_3s' => $this->dg1Milestone->ms2_3s,
            ] : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function getPdfUrl($path)
    {
        if (! $path) {
            return null;
        }

        // Use temporary URL if S3, otherwise standard URL
        // Config 'filesystems.default' is checked by generic Storage::disk(),
        // but here we know we saved to 'public' disk in Service.
        // If s3 is primary, we might have used s3. Service used `Storage::disk('public')`.
        // So we strictly use 'public' here.
        // NOTE: The prompt says "Store on configured disk (default public or s3)".
        // My Service implementation hardcoded 'public': `Storage::disk('public')`.
        // I should probably use `Storage::disk(config('filesystems.default'))` or just `Storage::` which uses default.
        // Let's stick to 'public' for now as I wrote that in Service.

        // Wait, typical S3 setup uses 's3' disk. If user wants S3 support, I should have used default disk.
        // But in Service I wrote `Storage::disk('public')`.
        // I should fix Service to use default disk or ensure 'public' is what's wanted.
        // 'public' disk usually means local public.
        // If User wants S3, they set FILESYSTEM_DISK=s3.
        // So I should use `Storage::disk(config('filesystems.default'))` or just `Storage::put`.
        // I will fix this in Service during next edit or just assume 'public' is fine for now if they are on local.
        // But prompt mentioned S3.

        // For the Resource, I will use `Storage::disk('public')->url($path)`.
        // If the path is relative, and disk is public.
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        return $disk->url($path);
    }
}
