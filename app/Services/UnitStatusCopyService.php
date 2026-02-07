<?php

namespace App\Services;

use App\Models\StatusUpdate;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class UnitStatusCopyService
{
    /**
     * Copy status, approvals, and revisions from a source unit to a target unit for a specific category.
     */
    public function copyStatus(Unit $targetUnit, string $category, Unit $sourceUnit, string $sourceCategory): StatusUpdate
    {
        return DB::transaction(function () use ($targetUnit, $category, $sourceUnit, $sourceCategory) {
            $sourceUpdate = $sourceUnit->statusUpdates()
                ->where('category', $sourceCategory)
                ->firstOrFail();

            $targetUpdate = $targetUnit->statusUpdates()
                ->where('category', $category)
                ->first();

            if (! $targetUpdate) {
                $targetUpdate = $targetUnit->statusUpdates()->create([
                    'category' => $category,
                ]);
            }

            // 1. Delete target's existing approvals and revisions
            $targetUpdate->approvals()->delete();
            $targetUpdate->revisions()->delete();

            // 2. Update target status update main fields
            $targetUpdate->update([
                'status' => $sourceUpdate->status,
            ]);

            // 3. Copy approvals
            foreach ($sourceUpdate->approvals as $approval) {
                $targetUpdate->approvals()->create([
                    'approval_code' => $approval->approval_code,
                    'comment' => $approval->comment,
                    'approved_at' => $approval->approved_at,
                    'pdf_path' => $approval->pdf_path,
                ]);
            }

            // 4. Copy revisions
            foreach ($sourceUpdate->revisions as $revision) {
                $targetUpdate->revisions()->create([
                    'revision_number' => $revision->revision_number,
                    'revision_date' => $revision->revision_date,
                    'pdf_path' => $revision->pdf_path,
                    'category' => $revision->category,
                ]);
            }

            return $targetUpdate->load(['revisions', 'approvals']);
        });
    }

    /**
     * Copy status to multiple units.
     */
    public function bulkCopyToUnits(Unit $sourceUnit, string $category, array $targetUnitIds): void
    {
        DB::transaction(function () use ($sourceUnit, $category, $targetUnitIds) {
            $targetUnits = Unit::whereIn('id', $targetUnitIds)->get();

            foreach ($targetUnits as $targetUnit) {
                $this->copyStatus($targetUnit, $category, $sourceUnit, $category);
            }
        });
    }
}
