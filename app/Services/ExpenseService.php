<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Group;
use App\Models\User;
use App\Exceptions\InvalidExpenseSplitException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpenseService
{
    protected GroupService $groupService;

    public function __construct(GroupService $groupService)
    {
        $this->groupService = $groupService;
    }

    /**
     * Requirement #26 & #38: Pagination, Filtering & Sorting
     */
    public function getGroupExpenses(User $authUser, string $groupId, array $filters)
    {
        $group = Group::findOrFail($groupId);
        $this->groupService->validateMembership($group, $authUser->id);

        $query = Expense::where('group_id', $groupId);

        // Apply filters
        if (!empty($filters['payer'])) {
            $query->where('paid_by', $filters['payer']);
        }

        if (!empty($filters['split_type'])) {
            $query->where('split_type', $filters['split_type']);
        }

        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query->whereBetween('created_at', [$filters['date_from'], $filters['date_to']]);
        }

        // Sorting & Pagination
        $perPage = (int)($filters['per_page'] ?? 15);
        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Requirement #27: Fetch single expense detail
     */
    public function getExpenseDetails(User $authUser, string $expenseId): Expense
    {
        $expense = Expense::findOrFail($expenseId);
        $group = Group::findOrFail($expense->group_id);

        $this->groupService->validateMembership($group, $authUser->id);

        return $expense;
    }

    public function createExpense(User $authUser, string $groupId, array $data): Expense
    {
        $group = Group::findOrFail($groupId);
        $this->groupService->validateMembership($group, $authUser->id);

        // Validate payer & participants membership
        $this->groupService->validateMembership($group, $data['paid_by']);
        foreach ($data['participants'] as $p) {
            $this->groupService->validateMembership($group, $p['user_id']);
        }

        $processedParticipants = $this->calculateAndValidateSplits(
            $data['split_type'],
            (float)$data['amount'],
            $data['participants']
        );

        // MongoDB Transaction Execution
        $session = DB::connection('mongodb')->getMongoClient()->startSession();
        $session->startTransaction();

        try {
            $expense = Expense::create([
                'group_id'     => $groupId,
                'description'  => $data['description'],
                'amount'       => (float)$data['amount'],
                'paid_by'      => $data['paid_by'],
                'split_type'   => $data['split_type'],
                'participants' => $processedParticipants,
            ]);

            $session->commitTransaction();

            // Requirement #39: Secure Logging
            Log::info('Expense Created Successfully', [
                'group_id'   => $groupId,
                'paid_by'    => $data['paid_by'],
                'amount'     => $data['amount'],
                'split_type' => $data['split_type']
            ]);

            return $expense;
        } catch (\Exception $e) {
            $session->abortTransaction();

            Log::error('Expense Creation Failed', [
                'group_id' => $groupId,
                'error'    => $e->getMessage()
            ]);

            throw $e;
        }
    }

    public function updateExpense(User $authUser, string $expenseId, array $data): Expense
    {
        $expense = Expense::findOrFail($expenseId);
        $group = Group::findOrFail($expense->group_id);

        // Validate authenticated user membership
        $this->groupService->validateMembership($group, $authUser->id);

        $amount = (float)($data['amount'] ?? $expense->amount);
        $splitType = $data['split_type'] ?? $expense->split_type;
        $participants = $data['participants'] ?? $expense->participants;
        $paidBy = $data['paid_by'] ?? $expense->paid_by;

        // Validate payer and participants membership
        $this->groupService->validateMembership($group, $paidBy);
        foreach ($participants as $p) {
            $this->groupService->validateMembership($group, $p['user_id']);
        }

        $processedParticipants = $this->calculateAndValidateSplits(
            $splitType,
            $amount,
            $participants
        );

        $expense->update([
            'description'  => $data['description'] ?? $expense->description,
            'amount'       => $amount,
            'paid_by'      => $paidBy,
            'split_type'   => $splitType,
            'participants' => $processedParticipants,
        ]);

        Log::info('Expense Updated', ['expense_id' => $expenseId]);

        return $expense;
    }

    public function deleteExpense(User $authUser, string $expenseId): bool
    {
        $expense = Expense::findOrFail($expenseId);
        $group = Group::findOrFail($expense->group_id);

        $this->groupService->validateMembership($group, $authUser->id);

        $deleted = $expense->delete();

        if ($deleted) {
            Log::info('Expense Deleted', ['expense_id' => $expenseId]);
        }

        return $deleted;
    }

    private function calculateAndValidateSplits(string $splitType, float $totalAmount, array $participants): array
    {
        $calculated = [];
        $count = count($participants);

        if ($count === 0) {
            throw new InvalidExpenseSplitException("Participants list cannot be empty.", 400);
        }

        if ($splitType === 'equal') {
            $perHead = round($totalAmount / $count, 2);
            foreach ($participants as $p) {
                $calculated[] = ['user_id' => $p['user_id'], 'amount' => $perHead];
            }
        } elseif ($splitType === 'exact') {
            $sum = array_sum(array_column($participants, 'amount'));
            if (abs($sum - $totalAmount) > 0.01) {
                throw new InvalidExpenseSplitException("Sum of participant exact amounts ($sum) must equal total amount ($totalAmount).", 400);
            }
            foreach ($participants as $p) {
                $calculated[] = ['user_id' => $p['user_id'], 'amount' => (float)$p['amount']];
            }
        } elseif ($splitType === 'percentage') {
            $totalPct = array_sum(array_column($participants, 'percentage'));
            if (abs($totalPct - 100.0) > 0.01) {
                throw new InvalidExpenseSplitException("Sum of participant percentages ($totalPct%) must equal 100%.", 400);
            }
            foreach ($participants as $p) {
                $calculated[] = [
                    'user_id' => $p['user_id'],
                    'amount'  => round(($totalAmount * $p['percentage']) / 100, 2)
                ];
            }
        }

        return $calculated;
    }
}