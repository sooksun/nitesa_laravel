<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    private const LEVEL_COLUMNS = [
        'MINISTER' => 'ministerPolicyId',
        'OBEC' => 'obecPolicyId',
        'AREA' => 'areaPolicyId',
    ];

    public function up(): void
    {
        Schema::create('supervisionpolicy', function (Blueprint $table) {
            $table->string('id', 26)->primary();
            $table->string('supervisionId', 26);
            $table->string('policyId', 26);
            $table->string('level', 20);
            $table->unsignedInteger('position')->default(0);
            $table->timestamp('createdAt')->nullable();

            $table->unique(['supervisionId', 'level', 'policyId'], 'supervisionpolicy_unique');
            $table->index(['policyId', 'level'], 'idx_supervisionpolicy_policy_level');

            $table->foreign('supervisionId')->references('id')->on('supervision')->onDelete('cascade');
            $table->foreign('policyId')->references('id')->on('policy')->onDelete('cascade');
        });

        $this->copyLegacyColumns();
    }

    /**
     * Copies each single-value policy column into the pivot. Safe to run again: levels that already have rows are skipped.
     */
    public function copyLegacyColumns(): void
    {
        foreach (self::LEVEL_COLUMNS as $level => $column) {
            DB::table('supervision')
                ->whereNotNull($column)
                ->whereNotExists(function ($query) use ($level) {
                    $query->selectRaw('1')
                        ->from('supervisionpolicy')
                        ->whereColumn('supervisionpolicy.supervisionId', 'supervision.id')
                        ->where('supervisionpolicy.level', $level);
                })
                ->select('id', $column)
                ->orderBy('id')
                ->get()
                ->chunk(500)
                ->each(function ($supervisions) use ($level, $column) {
                    $now = now();
                    $rows = [];
                    foreach ($supervisions as $supervision) {
                        $rows[] = [
                            'id' => (string) Str::ulid(),
                            'supervisionId' => $supervision->id,
                            'policyId' => $supervision->{$column},
                            'level' => $level,
                            'position' => 0,
                            'createdAt' => $now,
                        ];
                    }
                    DB::table('supervisionpolicy')->insert($rows);
                });
        }
    }

    /**
     * Writes the first policy of each level back to the single-value columns before dropping the table.
     */
    public function down(): void
    {
        $this->restoreLegacyColumns();

        Schema::dropIfExists('supervisionpolicy');
    }

    public function restoreLegacyColumns(): void
    {
        foreach (self::LEVEL_COLUMNS as $level => $column) {
            $firstByPosition = [];
            DB::table('supervisionpolicy')
                ->where('level', $level)
                ->orderBy('supervisionId')
                ->orderBy('position')
                ->orderBy('id')
                ->select('supervisionId', 'policyId')
                ->get()
                ->each(function ($row) use (&$firstByPosition) {
                    $firstByPosition[$row->supervisionId] ??= $row->policyId;
                });

            DB::table('supervision')
                ->whereNotNull($column)
                ->whereNotIn('id', array_keys($firstByPosition))
                ->update([$column => null]);

            foreach ($firstByPosition as $supervisionId => $policyId) {
                DB::table('supervision')->where('id', $supervisionId)->update([$column => $policyId]);
            }
        }
    }
};
