<?php

namespace App\Services;

use App\Models\StatusRevision;

class StatusRevisionService
{
    /**
     * Create a new StatusRevision record.
     */
    public function create(array $data): StatusRevision
    {
        if (! isset($data['revision_number'])) {
            $statusUpdateId = $data['status_update_id'];
            $category = $data['category'] ?? 'submitted';
            $maxRev = StatusRevision::where('status_update_id', $statusUpdateId)
                ->where('category', $category)
                ->max('revision_number');
            $data['revision_number'] = (is_null($maxRev)) ? 0 : min($maxRev + 1, 9);
        }

        return StatusRevision::create($data);
    }

    /**
     * Update a StatusRevision record.
     */
    public function update(StatusRevision $statusRevision, array $data): StatusRevision
    {
        $statusRevision->update($data);

        return $statusRevision;
    }
}
