<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('proprietaire_agences', 'genre_representant_id')) {
            DB::statement(
                'ALTER TABLE `proprietaire_agences` ADD `genre_representant_id` BIGINT UNSIGNED NULL AFTER `name_representant`'
            );
        }

        if (!Schema::hasColumn('proprietaire_agences', 'type_pieces_representant_id')) {
            DB::statement(
                'ALTER TABLE `proprietaire_agences` ADD `type_pieces_representant_id` BIGINT UNSIGNED NULL AFTER `email_representant`'
            );
        }

        if (!Schema::hasColumn('proprietaire_agences', 'numpiece_representant')) {
            DB::statement(
                'ALTER TABLE `proprietaire_agences` ADD `numpiece_representant` VARCHAR(100) NULL AFTER `type_pieces_representant_id`'
            );
        }

        if (!Schema::hasColumn('proprietaire_agences', 'photo_representant')) {
            DB::statement(
                'ALTER TABLE `proprietaire_agences` ADD `photo_representant` VARCHAR(255) NULL AFTER `numpiece_representant`'
            );
        }

        DB::statement(
            'UPDATE `proprietaire_agences`
             SET `genre_representant_id` = NULL
             WHERE `genre_representant_id` = 0'
        );
    }

    public function down(): void
    {
        if (Schema::hasColumn('proprietaire_agences', 'photo_representant')) {
            DB::statement('ALTER TABLE `proprietaire_agences` DROP COLUMN `photo_representant`');
        }

        if (Schema::hasColumn('proprietaire_agences', 'numpiece_representant')) {
            DB::statement('ALTER TABLE `proprietaire_agences` DROP COLUMN `numpiece_representant`');
        }

        if (Schema::hasColumn('proprietaire_agences', 'type_pieces_representant_id')) {
            DB::statement('ALTER TABLE `proprietaire_agences` DROP COLUMN `type_pieces_representant_id`');
        }

        if (Schema::hasColumn('proprietaire_agences', 'genre_representant_id')) {
            DB::statement('ALTER TABLE `proprietaire_agences` DROP COLUMN `genre_representant_id`');
        }
    }
};
