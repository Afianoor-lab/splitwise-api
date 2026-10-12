<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Services\ExpenseService;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Exception;

class ExpenseController extends Controller
{
    protected ExpenseService $expenseService;

    public function __construct(ExpenseService $expenseService)
    {
        $this->expenseService = $expenseService;
    }

    /**
     * Requirement #26 & #38: Listing, Pagination & Filtering
     */
    public function index(Request $request, string $groupId)
    {
        try {
            $expenses = $this->expenseService->getGroupExpenses($request->user(), $groupId, $request->all());
            return ApiResponse::success($expenses, 'Expenses fetched successfully.');
        } catch (Exception $e) {
            $code = ($e->getCode() >= 400 && $e->getCode() <= 500) ? $e->getCode() : 400;
            return ApiResponse::error($e->getMessage(), $code);
        }
    }

    /**
     * Requirement #27: Expense Details
     */
    public function show(Request $request, string $id)
    {
        try {
            $expense = $this->expenseService->getExpenseDetails($request->user(), $id);
            return ApiResponse::success($expense, 'Expense details fetched successfully.');
        } catch (Exception $e) {
            $code = ($e->getCode() >= 400 && $e->getCode() <= 500) ? $e->getCode() : 400;
            return ApiResponse::error($e->getMessage(), $code);
        }
    }

    public function store(CreateExpenseRequest $request, string $groupId)
    {
        try {
            $expense = $this->expenseService->createExpense(
                $request->user(),
                $groupId,
                $request->validated()
            );

            return ApiResponse::success($expense, 'Expense created successfully.', 201);
        } catch (Exception $e) {
            $code = ($e->getCode() >= 400 && $e->getCode() <= 500) ? $e->getCode() : 400;
            return ApiResponse::error($e->getMessage(), $code);
        }
    }

    public function update(UpdateExpenseRequest $request, string $id)
    {
        try {
            $expense = $this->expenseService->updateExpense(
                $request->user(),
                $id,
                $request->validated()
            );

            return ApiResponse::success($expense, 'Expense updated successfully.', 200);

        } catch (Exception $e) {
            $code = ($e->getCode() >= 400 && $e->getCode() <= 500) ? $e->getCode() : 400;
            return ApiResponse::error($e->getMessage(), $code);
        }
    }

    public function destroy(Request $request, string $id)
    {
        try {
            $this->expenseService->deleteExpense($request->user(), $id);

            return ApiResponse::success([], 'Expense deleted successfully.', 200);

        } catch (Exception $e) {
            $code = ($e->getCode() >= 400 && $e->getCode() <= 500) ? $e->getCode() : 400;
            return ApiResponse::error($e->getMessage(), $code);
        }
    }
}