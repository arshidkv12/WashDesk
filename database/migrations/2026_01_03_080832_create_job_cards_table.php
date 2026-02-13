<?php

use App\Enums\JobCardStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('job_cards', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();
            
            $table->foreignId('customer_id')
                ->constrained()
                ->cascadeOnDelete();
                
            $table->unsignedInteger('job_no');
            $table->unique(['user_id', 'job_no'], 'uniq_user_jobcard');

            $table->string('item')->nullable();  
            $table->text('problem')->nullable();

            $table->string('status')->default(JobCardStatus::Pending->value);

            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->date('delivery_date')->nullable();

            $table->text('notes')->nullable();

            // SaaS-ready (optional now, useful later)
            // $table->foreignId('company_id')->constrained();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_cards');
    }
};
