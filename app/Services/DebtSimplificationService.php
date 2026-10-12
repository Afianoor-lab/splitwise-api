<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Settlement;
use App\Models\Group;

class DebtSimplificationService
{
    public function getSimplifiedDebts(string $userId, string $groupId): array
    {
        $group = Group::findOrFail($groupId);

        // Flexible Membership Check for both 'members' and 'member_ids'
        $members = $group->member_ids ?? $group->members ?? [];
        $isMember = in_array($userId, array_map('strval', (array)$members)) || (string)$group->owner_id === (string)$userId;

        if (!$isMember) {
            throw new \Exception('User is not a member of this group.', 403);
        }

        $netBalances = [];

        // 1. Process Expenses
        $expenses = Expense::where('group_id', $groupId)->get();
        foreach ($expenses as $expense) {
            $payer = (string)$expense->paid_by;
            $amount = (float)$expense->amount;

            $netBalances[$payer] = ($netBalances[$payer] ?? 0) + $amount;

            if (!empty($expense->participants) && is_array($expense->participants)) {
                foreach ($expense->participants as $participant) {
                    $pUser = (string)($participant['user_id'] ?? $participant['user'] ?? '');
                    if ($pUser) {
                        $pAmount = (float)($participant['amount'] ?? 0);
                        $netBalances[$pUser] = ($netBalances[$pUser] ?? 0) - $pAmount;
                    }
                }
            }
        }

        // 2. Process Settlements
        $settlements = Settlement::where('group_id', $groupId)->get();
        foreach ($settlements as $settlement) {
            $payer = (string)$settlement->paid_by;
            $receiver = (string)$settlement->paid_to;
            $amount = (float)$settlement->amount;

            $netBalances[$payer] = ($netBalances[$payer] ?? 0) + $amount;
            $netBalances[$receiver] = ($netBalances[$receiver] ?? 0) - $amount;
        }

        // 3. Separate Debtors and Creditors
        $debtors = [];
        $creditors = [];

        foreach ($netBalances as $uId => $balance) {
            $rounded = round($balance, 2);
            if ($rounded < -0.01) {
                $debtors[] = ['user_id' => $uId, 'amount' => abs($rounded)];
            } elseif ($rounded > 0.01) {
                $creditors[] = ['user_id' => $uId, 'amount' => $rounded];
            }
        }

        // 4. Greedy Minimization
        $transactions = [];
        $i = 0;
        $j = 0;

        while ($i < count($debtors) && $j < count($creditors)) {
            $debtor = &$debtors[$i];
            $creditor = &$creditors[$j];

            $minAmount = min($debtor['amount'], $creditor['amount']);

            $transactions[] = [
                'from' => $debtor['user_id'],
                'to' => $creditor['user_id'],
                'amount' => round($minAmount, 2)
            ];

            $debtor['amount'] -= $minAmount;
            $creditor['amount'] -= $minAmount;

            if (round($debtor['amount'], 2) <= 0) {
                $i++;
            }
            if (round($creditor['amount'], 2) <= 0) {
                $j++;
            }
        }

        return [
            'net_balances' => $netBalances,
            'simplified_transactions' => $transactions
        ];
    }
}