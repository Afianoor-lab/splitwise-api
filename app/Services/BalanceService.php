<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Settlement;
use App\Models\Group;
use App\Models\User;

class BalanceService
{
    public function getGroupBalances(string $groupId): array
    {
        $group = Group::findOrFail($groupId);
        $memberIds = $group->members ?? $group->member_ids ?? [];

        $balances = array_fill_keys($memberIds, 0.0);

        // 1. Process Expenses
        $expenses = Expense::where('group_id', $groupId)->get();
        foreach ($expenses as $e) {
            if (isset($balances[$e->paid_by])) {
                $balances[$e->paid_by] += (float)$e->amount;
            }
            foreach ($e->participants ?? [] as $p) {
                if (isset($balances[$p['user_id']])) {
                    $balances[$p['user_id']] -= (float)$p['amount'];
                }
            }
        }

        // 2. Process Settlements
        $settlements = Settlement::where('group_id', $groupId)->get();
        foreach ($settlements as $s) {
            if (isset($balances[$s->paid_by])) {
                $balances[$s->paid_by] += (float)$s->amount;
            }
            if (isset($balances[$s->paid_to])) {
                $balances[$s->paid_to] -= (float)$s->amount;
            }
        }

        // Pre-fetch users
        $usersMap = User::whereIn('_id', array_keys($balances))->get()->keyBy('id');

        $formattedBalances = [];
        foreach ($balances as $userId => $bal) {
            $u = $usersMap->get($userId);
            $formattedBalances[] = [
                'user_id' => $userId,
                'name' => $u ? $u->name : 'Unknown',
                'balance' => round($bal, 2),
            ];
        }

        $simplifiedDebts = $this->simplifyDebts($balances, $usersMap);

        return [
            'group_id' => $groupId,
            'balances' => $formattedBalances,
            'simplified_debts' => $simplifiedDebts,
        ];
    }

    private function simplifyDebts(array $balances, $usersMap): array
    {
        $debtors = [];
        $creditors = [];

        foreach ($balances as $uId => $bal) {
            if ($bal < -0.01) {
                $debtors[] = ['user_id' => $uId, 'amount' => abs($bal)];
            } elseif ($bal > 0.01) {
                $creditors[] = ['user_id' => $uId, 'amount' => $bal];
            }
        }

        $settlements = [];
        $i = 0;
        $j = 0;

        while ($i < count($debtors) && $j < count($creditors)) {
            $minAmount = min($debtors[$i]['amount'], $creditors[$j]['amount']);
            $debtorUser = $usersMap->get($debtors[$i]['user_id']);
            $creditorUser = $usersMap->get($creditors[$j]['user_id']);

            $settlements[] = [
                'from_user_id' => $debtors[$i]['user_id'],
                'from_name' => $debtorUser ? $debtorUser->name : 'Unknown',
                'to_user_id' => $creditors[$j]['user_id'],
                'to_name' => $creditorUser ? $creditorUser->name : 'Unknown',
                'amount' => round($minAmount, 2),
            ];

            $debtors[$i]['amount'] -= $minAmount;
            $creditors[$j]['amount'] -= $minAmount;

            if ($debtors[$i]['amount'] < 0.01) $i++;
            if ($creditors[$j]['amount'] < 0.01) $j++;
        }

        return $settlements;
    }
}