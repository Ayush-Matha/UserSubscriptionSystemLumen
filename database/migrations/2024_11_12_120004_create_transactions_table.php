<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('transaction_id');
            $table->unsignedBigInteger('user_id');
            $table->double('amount',15,2);
            $table->enum('payment_type',['UPI','Credit Card','Debit Card']);
            $table->unsignedBigInteger('plan_id');
            $table->json('payment_option_details');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('plan_id')->references('plan_id')->on('plans')->onDelete('cascade');
        });

        // Create a trigger to insert a subscription after a new transaction is created
        // DB::unprepared("
        //     CREATE TRIGGER after_transaction_insert_create_subscription
        //     AFTER INSERT ON transactions
        //     FOR EACH ROW
        //     BEGIN
        //         INSERT INTO subscriptions (t_id, u_id, plan_id, expiry, created_at, updated_at)
        //         VALUES (
        //             NEW.transaction_id, 
        //             NEW.user_id, 
        //             NEW.plan_id,
        //             DATE_ADD(NOW(), INTERVAL  YEAR), -- Sets expiry 1 year from now
        //             NOW(), 
        //             NOW()
        //         );
        //     END;
        // ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the trigger first
        // DB::unprepared("DROP TRIGGER IF EXISTS after_transaction_insert_create_subscription");

        Schema::dropIfExists('transactions');
    }
};
