<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description');
            $table->string('image');
            $table->timestamps();
        });

        Schema::create('master_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('master_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 120);
            $table->text('description');
            $table->date('date');
            $table->string('time_slot', 5);
            $table->unsignedInteger('capacity');
            $table->decimal('price', 10, 2);
            $table->timestamps();

            $table->unique(['master_id', 'date', 'time_slot']);
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('master_class_id')->constrained('master_classes')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'master_class_id']);
        });

        if (DB::getDriverName() === 'sqlite') {
            DB::statement("CREATE TRIGGER master_classes_time_slot_insert BEFORE INSERT ON master_classes
                WHEN NEW.time_slot NOT IN ('09:00', '11:00', '13:00', '15:00')
                BEGIN
                    SELECT RAISE(ABORT, 'invalid time slot');
                END");
            DB::statement("CREATE TRIGGER master_classes_time_slot_update BEFORE UPDATE ON master_classes
                WHEN NEW.time_slot NOT IN ('09:00', '11:00', '13:00', '15:00')
                BEGIN
                    SELECT RAISE(ABORT, 'invalid time slot');
                END");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('DROP TRIGGER IF EXISTS master_classes_time_slot_insert');
            DB::statement('DROP TRIGGER IF EXISTS master_classes_time_slot_update');
        }

        Schema::dropIfExists('bookings');
        Schema::dropIfExists('master_classes');
        Schema::dropIfExists('categories');
    }
};
