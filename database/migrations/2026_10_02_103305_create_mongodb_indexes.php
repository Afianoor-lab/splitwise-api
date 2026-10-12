<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MongoDB\Laravel\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mongodb')->table('users', function (Blueprint $collection) {
            $collection->unique('email');
            $collection->index('api_token');
        });

        Schema::connection('mongodb')->table('expenses', function (Blueprint $collection) {
            $collection->index('group_id');
            $collection->index('paid_by');
            $collection->index('created_at');
        });

        Schema::connection('mongodb')->table('settlements', function (Blueprint $collection) {
            $collection->index('group_id');
            $collection->index('paid_by');
            $collection->index('paid_to');
        });
    }

    public function down(): void
    {
        Schema::connection('mongodb')->table('users', function (Blueprint $collection) {
            $collection->dropIndex(['email']);
            $collection->dropIndex(['api_token']);
        });

        Schema::connection('mongodb')->table('expenses', function (Blueprint $collection) {
            $collection->dropIndex(['group_id']);
            $collection->dropIndex(['paid_by']);
            $collection->dropIndex(['created_at']);
        });

        Schema::connection('mongodb')->table('settlements', function (Blueprint $collection) {
            $collection->dropIndex(['group_id']);
            $collection->dropIndex(['paid_by']);
            $collection->dropIndex(['paid_to']);
        });
    }
};