<?php

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
        if (Schema::hasTable('campaigns')) {
            return;
        }

        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('type', 20); // manual|automated
            $table->string('status', 20); // draft|active|paused|archived|blocked

            $table->json('channels_json'); // ["whatsapp"] | ["email","whatsapp"]
            $table->json('content_json'); // versioned schema (version=1)
            $table->json('audience_json'); // versioned schema (version=1)
            $table->json('automation_json')->nullable(); // versioned schema (version=1)

            $table->timestamp('scheduled_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable(); // user_id

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};

