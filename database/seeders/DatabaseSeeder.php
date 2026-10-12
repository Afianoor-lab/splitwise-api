<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Group;
use App\Models\Expense;
use App\Models\Settlement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->truncate();
        DB::table('groups')->truncate();
        DB::table('expenses')->truncate();
        DB::table('settlements')->truncate();

        // 1. Create 10 Users
        $users = [];
        for ($k = 1; $k <= 10; $k++) {
            $users[] = User::create([
                'name' => "User $k",
                'email' => "user$k@example.com",
                'password' => Hash::make('password123'),
                'api_token' => bin2hex(random_bytes(32)),
            ]);
        }

        $uIds = array_map(fn($u) => (string)$u->id, $users);

        // 2. Create 3 Groups
        $group1 = Group::create([
            'name' => 'Northern Trip',
            'description' => 'Trip expenses',
            'owner_id' => $uIds[0],
            'member_ids' => array_slice($uIds, 0, 4)
        ]);

        $group2 = Group::create([
            'name' => 'Apartment Rent',
            'description' => 'Flat bills',
            'owner_id' => $uIds[2],
            'member_ids' => array_slice($uIds, 2, 4)
        ]);

        $group3 = Group::create([
            'name' => 'Dinner Party',
            'description' => 'Weekend hangouts',
            'owner_id' => $uIds[5],
            'member_ids' => array_slice($uIds, 5, 5)
        ]);

        // 3. Create 10+ Expenses across all three split types
        $expensesData = [
            // Group 1 Expenses (Equal)
            ['group_id' => $group1->id, 'description' => 'Hotel Reservation', 'amount' => 12000, 'paid_by' => $uIds[0], 'split_type' => 'equal', 'participants' => [['user_id' => $uIds[0], 'amount' => 3000], ['user_id' => $uIds[1], 'amount' => 3000], ['user_id' => $uIds[2], 'amount' => 3000], ['user_id' => $uIds[3], 'amount' => 3000]]],
            ['group_id' => $group1->id, 'description' => 'Fuel Expense', 'amount' => 4000, 'paid_by' => $uIds[1], 'split_type' => 'equal', 'participants' => [['user_id' => $uIds[0], 'amount' => 1000], ['user_id' => $uIds[1], 'amount' => 1000], ['user_id' => $uIds[2], 'amount' => 1000], ['user_id' => $uIds[3], 'amount' => 1000]]],
            ['group_id' => $group1->id, 'description' => 'Toll Tax', 'amount' => 800, 'paid_by' => $uIds[2], 'split_type' => 'equal', 'participants' => [['user_id' => $uIds[0], 'amount' => 200], ['user_id' => $uIds[1], 'amount' => 200], ['user_id' => $uIds[2], 'amount' => 200], ['user_id' => $uIds[3], 'amount' => 200]]],
            ['group_id' => $group1->id, 'description' => 'Barbecue Dinner', 'amount' => 6000, 'paid_by' => $uIds[0], 'split_type' => 'equal', 'participants' => [['user_id' => $uIds[0], 'amount' => 1500], ['user_id' => $uIds[1], 'amount' => 1500], ['user_id' => $uIds[2], 'amount' => 1500], ['user_id' => $uIds[3], 'amount' => 1500]]],

            // Group 2 Expenses (Exact)
            ['group_id' => $group2->id, 'description' => 'Grocery Shopping', 'amount' => 5000, 'paid_by' => $uIds[2], 'split_type' => 'exact', 'participants' => [['user_id' => $uIds[2], 'amount' => 3000], ['user_id' => $uIds[3], 'amount' => 2000]]],
            ['group_id' => $group2->id, 'description' => 'Electricity Bill', 'amount' => 8000, 'paid_by' => $uIds[3], 'split_type' => 'exact', 'participants' => [['user_id' => $uIds[2], 'amount' => 4000], ['user_id' => $uIds[3], 'amount' => 4000]]],
            ['group_id' => $group2->id, 'description' => 'Internet Bill', 'amount' => 3000, 'paid_by' => $uIds[2], 'split_type' => 'exact', 'participants' => [['user_id' => $uIds[2], 'amount' => 1500], ['user_id' => $uIds[3], 'amount' => 1500]]],

            // Group 3 Expenses (Percentage)
            ['group_id' => $group3->id, 'description' => 'Restaurant Bill', 'amount' => 10000, 'paid_by' => $uIds[5], 'split_type' => 'percentage', 'participants' => [['user_id' => $uIds[5], 'percentage' => 50, 'amount' => 5000], ['user_id' => $uIds[6], 'percentage' => 50, 'amount' => 5000]]],
            ['group_id' => $group3->id, 'description' => 'Bowling Alley', 'amount' => 4000, 'paid_by' => $uIds[6], 'split_type' => 'percentage', 'participants' => [['user_id' => $uIds[5], 'percentage' => 40, 'amount' => 1600], ['user_id' => $uIds[6], 'percentage' => 60, 'amount' => 2400]]],
            ['group_id' => $group3->id, 'description' => 'Coffee & Snacks', 'amount' => 2000, 'paid_by' => $uIds[7], 'split_type' => 'percentage', 'participants' => [['user_id' => $uIds[7], 'percentage' => 50, 'amount' => 1000], ['user_id' => $uIds[8], 'percentage' => 50, 'amount' => 1000]]],
        ];

        foreach ($expensesData as $exp) {
            Expense::create($exp);
        }

        // 4. Create Settlements
        Settlement::create([
            'group_id' => $group1->id,
            'paid_by' => $uIds[1],
            'paid_to' => $uIds[0],
            'amount' => 3000,
            'note' => 'Hotel Settlement'
        ]);

        Settlement::create([
            'group_id' => $group3->id,
            'paid_by' => $uIds[6],
            'paid_to' => $uIds[5],
            'amount' => 2500,
            'note' => 'Dinner Settlement'
        ]);
    }
}