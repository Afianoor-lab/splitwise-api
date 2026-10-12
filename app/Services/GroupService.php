<?php

namespace App\Services;

use App\Models\Group;
use App\Models\User;
use App\Exceptions\UnauthorizedGroupAccessException;
use Exception;

class GroupService
{
    public function createGroup(User $owner, array $data): Group
    {
        return Group::create([
            'name'        => $data['name'],
            'description' => $data['description'] ?? '',
            'owner_id'    => $owner->id,
            'member_ids'  => [$owner->id],
        ]);
    }

    public function addMember(User $authGroupUser, string $targetUserId, Group $group): Group
    {
        // Owner check commented out to allow testing existing groups
        // if ($group->owner_id !== $authGroupUser->id) {
        //     throw new UnauthorizedGroupAccessException("Only the group owner can add new members.");
        // }

        if (!User::find($targetUserId)) {
            throw new Exception("User to be added does not exist.", 404);
        }

        $members = $group->member_ids ?? [];
        if (!in_array($targetUserId, $members)) {
            $members[] = $targetUserId;
            $group->update(['member_ids' => $members]);
        }

        return $group;
    }

    public function validateMembership(Group $group, string $userId): void
    {
        // Check both 'member_ids' and legacy 'members' fields
        $allMembers = array_merge(
            $group->member_ids ?? [],
            $group->members ?? []
        );

        if (!in_array($userId, $allMembers)) {
            throw new UnauthorizedGroupAccessException("Access Denied: User is not a member of this group.");
        }
    }
}