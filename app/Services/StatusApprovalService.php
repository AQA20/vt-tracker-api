<?php

namespace App\Services;

use App\Models\StatusApproval;

class StatusApprovalService
{
    /**
     * Create a new StatusApproval record.
     */
    public function create(array $data): StatusApproval
    {
        return StatusApproval::create($data);
    }

    /**
     * Update a StatusApproval record.
     */
    public function update(StatusApproval $statusApproval, array $data): StatusApproval
    {
        $statusApproval->update($data);

        return $statusApproval;
    }
}
