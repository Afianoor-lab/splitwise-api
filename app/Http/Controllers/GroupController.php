<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateGroupRequest;
use App\Services\GroupService;
use App\Services\BalanceService;
use App\Services\DebtSimplificationService;
use App\Helpers\ApiResponse;
use App\Models\Group;
use Illuminate\Http\Request;

class GroupController
{
    protected GroupService $groupService;
    protected BalanceService $balanceService;
    protected DebtSimplificationService $simplificationService;

    public function __construct(
        GroupService $groupService, 
        BalanceService $balanceService,
        DebtSimplificationService $simplificationService
    ) {
        $this->groupService = $groupService;
        $this->balanceService = $balanceService;
        $this->simplificationService = $simplificationService;
    }

    public function index(Request $request)
    {
        $groups = Group::all();
        return ApiResponse::success($groups, 'Groups retrieved successfully.');
    }

    public function store(CreateGroupRequest $request)
    {
        $group = $this->groupService->createGroup($request->user(), $request->validated());
        return ApiResponse::success($group, 'Group created successfully.', 201);
    }

    public function show(string $id)
    {
        $group = Group::find($id);

        if (!$group) {
            return ApiResponse::error('Group not found.', 404);
        }

        return ApiResponse::success($group, 'Group details retrieved successfully.');
    }

    public function addMember(Request $request, string $group)
    {
        $request->validate(['user_id' => 'required|string']);
        $updatedGroup = $this->groupService->addMember($request->user(), $group, $request->input('user_id'));
        return ApiResponse::success($updatedGroup, 'Member added to group successfully.');
    }

    public function removeMember(Request $request, string $group, string $user)
    {
        if (method_exists($this->groupService, 'removeMember')) {
            $updatedGroup = $this->groupService->removeMember($request->user(), $group, $user);
            return ApiResponse::success($updatedGroup, 'Member removed from group successfully.');
        }

        $groupModel = Group::find($group);

        if (!$groupModel) {
            return ApiResponse::error('Group not found.', 404);
        }

        $membersField = isset($groupModel->member_ids) ? 'member_ids' : 'members';
        $currentMembers = $groupModel->$membersField ?? [];

        $updatedMembers = array_values(array_filter($currentMembers, function ($id) use ($user) {
            return (string)$id !== (string)$user;
        }));

        $groupModel->$membersField = $updatedMembers;
        $groupModel->save();

        return ApiResponse::success($groupModel, 'Member removed from group successfully.');
    }

    public function getBalances(string $groupId)
    {
        $balances = $this->balanceService->getGroupBalances($groupId);
        return ApiResponse::success($balances, 'Group balances retrieved successfully.');
    }

    public function getSimplifiedDebts(Request $request, string $groupId)
    {
        try {
            $result = $this->simplificationService->getSimplifiedDebts($request->user()->id, $groupId);
            return ApiResponse::success($result, 'Simplified debts calculated successfully.');
        } catch (\Exception $e) {
            $code = ($e->getCode() >= 400 && $e->getCode() <= 500) ? $e->getCode() : 400;
            return ApiResponse::error($e->getMessage(), $code);
        }
    }
}