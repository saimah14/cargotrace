<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->string('transport_mode', 10)->nullable();
            $table->string('sender_name', 100)->nullable();
            $table->string('sender_phone', 30)->nullable();
            $table->string('origin_address')->nullable();
            $table->string('receiver_name', 100)->nullable();
            $table->string('receiver_phone', 30)->nullable();
            $table->string('destination_address')->nullable();
            $table->decimal('cost', 12, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn([
                'transport_mode',
                'sender_name',
                'sender_phone',
                'origin_address',
                'receiver_name',
                'receiver_phone',
                'destination_address',
                'cost',
            ]);
        });
    }
};
