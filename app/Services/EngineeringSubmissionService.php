<?php

namespace App\Services;

use App\Models\CseDetail;
use App\Models\StatusUpdate;
use App\Models\Dg1Milestone;
use App\Imports\EngineeringSubmissionImport;
use App\Exports\EngineeringSubmissionExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;

class EngineeringSubmissionService
{
    public function list($filters = [])
    {
        return CseDetail::with(['statusUpdate', 'dg1Milestone'])
            ->when(isset($filters['search']), function ($q) use ($filters) {
                $q->where('equip_n', 'like', "%{$filters['search']}%");
            })
            ->paginate($filters['per_page'] ?? 15);
    }

    public function showById(int $id)
    {
        return CseDetail::with(['statusUpdate', 'dg1Milestone'])->findOrFail($id);
    }

    public function storeGrouped(array $payload)
    {
        return DB::transaction(function () use ($payload) {
            $cse = CseDetail::create([
                'equip_n' => $payload['equip_n'],
                'asset_name' => $payload['asset_name'] ?? null,
                'unit_id' => $payload['unit_id'] ?? null,
                'material_code' => $payload['material_code'] ?? null,
                'so_no' => $payload['so_no'] ?? null,
                'network_no' => $payload['network_no'] ?? null,
            ]);

            if (isset($payload['status_update'])) {
                $cse->statusUpdate()->create($payload['status_update']);
            } else {
                 // Ensure record exists even if empty? Prompt says "One grouped resource".
                 // Usually we want the child rows to exist to hold the status.
                 $cse->statusUpdate()->create([]);
            }

            if (isset($payload['dg1_milestone'])) {
                $cse->dg1Milestone()->create($payload['dg1_milestone']);
            } else {
                $cse->dg1Milestone()->create([]);
            }

            return $cse->load(['statusUpdate', 'dg1Milestone']);
        });
    }

    public function updateGrouped(int $cseId, array $payload)
    {
        return DB::transaction(function () use ($cseId, $payload) {
            $cse = CseDetail::findOrFail($cseId);
            
            $cse->update([
                'equip_n' => $payload['equip_n'] ?? $cse->equip_n, // Usually strict/unique, validation handles it
                'asset_name' => $payload['asset_name'] ?? $cse->asset_name,
                'unit_id' => $payload['unit_id'] ?? $cse->unit_id,
                'material_code' => $payload['material_code'] ?? $cse->material_code,
                'so_no' => $payload['so_no'] ?? $cse->so_no,
                'network_no' => $payload['network_no'] ?? $cse->network_no,
            ]);

            if (isset($payload['status_update'])) {
                $cse->statusUpdate()->updateOrCreate(
                    ['cse_id' => $cse->id],
                    $payload['status_update']
                );
            }

            if (isset($payload['dg1_milestone'])) {
                $cse->dg1Milestone()->updateOrCreate(
                    ['cse_id' => $cse->id],
                    $payload['dg1_milestone']
                );
            }

            return $cse->load(['statusUpdate', 'dg1Milestone']);
        });
    }

    public function uploadStatusPdf(int $cseId, string $field, UploadedFile $file)
    {
        // Validation of field name should be done in Request, but we can double check or just use it.
        // Paths: engineering-submissions/{equip_n}/{field}/{timestamp}_{originalName}.pdf
        
        $cse = CseDetail::findOrFail($cseId);
        $statusUpdate = $cse->statusUpdate()->firstOrCreate(['cse_id' => $cseId]);

        // Delete old file if exists
        $oldPath = $statusUpdate->$field;
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        $filename = time() . '_' . $file->getClientOriginalName();
        $path = "engineering-submissions/{$cse->equip_n}/{$field}";
        
        // Store file
        $storedPath = $file->storeAs($path, $filename, 'public');

        // Update DB
        $statusUpdate->update([$field => $storedPath]);

        return $cse->load(['statusUpdate', 'dg1Milestone']);
    }
    
    public function deleteStatusPdf(int $cseId, string $field)
    {
        $cse = CseDetail::findOrFail($cseId);
        $statusUpdate = $cse->statusUpdate; 
        
        if ($statusUpdate && $statusUpdate->$field) {
            if (Storage::disk('public')->exists($statusUpdate->$field)) {
                Storage::disk('public')->delete($statusUpdate->$field);
            }
            $statusUpdate->update([$field => null]);
        }
        
        return $cse->load(['statusUpdate', 'dg1Milestone']);
    }

    public function importFromExcel(UploadedFile $file)
    {
        $import = new EngineeringSubmissionImport();
        Excel::import($import, $file);
        return $import->result;
    }

    public function exportToExcel($filters = [])
    {
        return Excel::download(new EngineeringSubmissionExport($filters), 'engineering_submissions.xlsx');
    }
}
