<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateSettlementRequest;
use App\Services\SettlementService;
use App\Services\BalanceService;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;

class SettlementController extends Controller
{
    protected SettlementService $settlementService;
    protected BalanceService $balanceService;

    public function __construct(SettlementService $settlementService, BalanceService $balanceService)
    {
        $this->settlementService = $settlementService;
        $this->balanceService = $balanceService;
    }

    /**
     * Get list of settlements for a specific group.
     */
    public function index(Request $request, string $groupId)
    {
        $settlements = $this->settlementService->getGroupSettlements($groupId);
        return ApiResponse::success($settlements, 'Settlements retrieved successfully.');
    }

    /**
     * Create a new settlement receipt/record.
     */
    public function store(CreateSettlementRequest $request, string $groupId)
    {
        $settlement = $this->settlementService->createSettlement($request->user(), $groupId, $request->validated());
        return ApiResponse::success($settlement, 'Settlement recorded successfully.', 201);
    }

    /**
     * Get group balances.
     */
    public function getBalances(string $groupId)
    {
        $balances = $this->balanceService->getGroupBalances($groupId);
        return ApiResponse::success($balances, 'Group balances retrieved successfully.');
    }
}