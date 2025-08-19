<?php

use App\Models\Room;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->date("check_in_date");
            $table->date("check_out_date");
            $table->time("check_in_time")->nullable();
            $table->time("check_out_time")->nullable();

            $table->foreignId("room_id")->nullable()->constrained()->nullOnDelete();
            $table->foreignId("user_id")->nullable()->constrained()->nullOnDelete();

            $table->string("guest_name");
            $table->string("guest_nic");
            $table->string("contact_number");
            $table->integer("occupancy");

            $table->decimal("total", 8, 2);
            $table->decimal("advance", 8, 2)->nullable();
            $table->decimal("outstanding", 8, 2);

            $table->text("nic_front")->nullable();
            $table->text("nic_back")->nullable();
            $table->time("expected_arrival_time");
            $table->time("actual_leaving_time");
            $table->string("vehicle_number")->nullable();

            $table->enum('status', ['Booked', 'Checked In', 'Checked Out', 'Cancelled']);
            $table->timestamps();

            $table->index('guest_nic');
            $table->index('contact_number');
            $table->index('vehicle_number');
            $table->index('status');
            $table->index('check_in_date');
            $table->index('check_out_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
