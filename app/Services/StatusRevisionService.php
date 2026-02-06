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
