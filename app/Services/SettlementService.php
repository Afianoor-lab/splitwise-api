<?php

namespace App\Services;

use App\Models\Settlement;
use App\Models\Group;
use App\Models\User;
use App\Exceptions\InvalidSettlementException;
use Illuminate\Support\Facades\Log;

class SettlementService
{
    protected GroupService $groupService;

    public function __construct(GroupService $groupService)
    {
        $this->groupService = $groupService;
    }

    /**
     * Get list of settlements for a specific group.
     */
    public function getGroupSettlements(string $groupId)
    {
        return Settlement::where('group_id', $groupId)->get();
    }

    /**
     * Create a new settlement receipt/record.
     */
    public function createSettlement(User $payer, string $groupId, array $data): Settlement
    {
        $group = Group::findOrFail($groupId);

        $this->groupService->validateMembership($group, $payer->id);
        $this->groupService->validateMembership($group, $data['paid_to']);

        if ($payer->id === $data['paid_to']) {
            throw new InvalidSettlementException("Payer and Receiver cannot be the same user.");
        }

        $attachmentPath = null;
        if (isset($data['attachment']) && $data['attachment']->isValid()) {
            $attachmentPath = $data['attachment']->store('settlements', 'public');
        }

        $settlement = Settlement::create([
            'group_id'       => $groupId,
            'paid_by'        => $payer->id,
            'paid_to'        => $data['paid_to'],
            'amount'         => (float)$data['amount'],
            'note'           => $data['note'] ?? '',
            'attachment_url' => $attachmentPath ? asset('storage/' . $attachmentPath) : null,
        ]);

        // Requirement #39: Secure Logging
        Log::info('Settlement Recorded Successfully', [
            'group_id' => $groupId,
            'paid_by'  => $payer->id,
            'paid_to'  => $data['paid_to'],
            'amount'   => $data['amount']
        ]);

        return $settlement;
    }
}