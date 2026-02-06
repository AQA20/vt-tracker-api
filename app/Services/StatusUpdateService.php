<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\StatusUpdate;

class StatusUpdateService
{
    /**
     * Update the status of a StatusUpdate record.
     */
    public function updateStatus(StatusUpdate $statusUpdate, ?Status $status): StatusUpdate
    {
        $statusUpdate->update([
            'status' => $status,
        ]);

        if ($status === Status::REJECTED) {
            $maxRev = $statusUpdate->revisions()->max('revision_number');
            $nextRev = (is_null($maxRev)) ? 1 : min($maxRev + 1, 9);

            $statusUpdate->revisions()->create([
                'revision_number' => $nextRev,
                'revision_date' => now(),
            ]);
        }

        return $statusUpdate;
    }

    /**
     * Upload a PDF file for a StatusUpdate.
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    public function uploadPdf(StatusUpdate $statusUpdate, \Illuminate\Http\UploadedFile $file): StatusUpdate
    {
        $status = $statusUpdate->status;

        if ($status === Status::IN_PROGRESS) {
            abort(422, 'Cannot upload PDF for in-progress status updates.');
        }

        if ($status === Status::APPROVED) {
            $path = $file->store('approvals', 'public');
            if (! $path) {
                abort(500, 'Failed to store approval PDF.');
            }

            $statusUpdate->approvals()->updateOrCreate(
                ['approval_code' => \App\Enums\ApprovalCode::A],
                [
                    'pdf_path' => $path,
                    'approved_at' => now(),
                ]
            );
        } else {
            // Submitted or Rejected
            $path = $file->store('revisions', 'public');
            if (! $path) {
                abort(500, 'Failed to store revision PDF.');
            }

            // Get latest revision number using max() to avoid conflicting ORDER BY from relationship
            $maxRev = $statusUpdate->revisions()->max('revision_number');
            $nextRev = (is_null($maxRev)) ? 0 : min($maxRev + 1, 9);

            $statusUpdate->revisions()->create([
                'pdf_path' => $path,
                'revision_number' => $nextRev,
                'revision_date' => now(),
            ]);
        }

        return $statusUpdate->load(['revisions', 'approvals']);
    }
}
