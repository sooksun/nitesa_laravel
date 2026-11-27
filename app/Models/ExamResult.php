<?php

namespace App\Models;

use App\Enums\ExamScope;
use App\Enums\ExamType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExamResult extends Model
{
    public const SCORE_COLUMNS = ['thai', 'math', 'english', 'science', 'readAloud', 'readComprehension'];

    protected $table = 'examresult';
    protected $keyType = 'string';
    public $incrementing = false;

    public const CREATED_AT = 'createdAt';
    public const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'examType',
        'academicYear',
        'scope',
        'schoolId',
        'examinees',
        'thai',
        'math',
        'english',
        'science',
        'readAloud',
        'readComprehension',
        'average',
        'qualityLevel',
        'sourceCode',
        'sourceName',
    ];

    protected $casts = [
        'examType' => ExamType::class,
        'scope' => ExamScope::class,
        'examinees' => 'integer',
        'thai' => 'float',
        'math' => 'float',
        'english' => 'float',
        'science' => 'float',
        'readAloud' => 'float',
        'readComprehension' => 'float',
        'average' => 'float',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::ulid();
            }
        });
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'schoolId');
    }

    /**
     * SELECT expressions that average every score of an exam over a set of school rows.
     * Exams that report examinee counts are weighted by them; the others use a plain mean.
     *
     * @return array<int, \Illuminate\Contracts\Database\Query\Expression>
     */
    public static function aggregateColumns(ExamType $type): array
    {
        $columns = [];
        foreach ([...array_keys($type->subjects()), 'average'] as $column) {
            $field = "examresult.`{$column}`";
            $columns[] = DB::raw(
                $type->hasExaminees()
                    ? "ROUND(SUM({$field} * examresult.examinees) / NULLIF(SUM(CASE WHEN {$field} IS NOT NULL THEN examresult.examinees END), 0), 2) AS `{$column}`"
                    : "ROUND(AVG({$field}), 2) AS `{$column}`"
            );
        }
        $columns[] = DB::raw('COUNT(*) AS schoolCount');
        $columns[] = DB::raw('SUM(examresult.examinees) AS examineeTotal');

        return $columns;
    }

    /**
     * Comparison rows of an exam keyed by scope value. The area row falls back to a figure
     * computed from every school when the source file did not publish one (NT).
     *
     * @return array<string, array{label: string, scores: array<string, ?float>, average: ?float, examinees: ?int, computed: bool}>
     */
    public static function benchmarks(ExamType $type, string $academicYear): array
    {
        $subjects = array_keys($type->subjects());
        $rows = static::query()
            ->where('examType', $type->value)
            ->where('academicYear', $academicYear)
            ->where('scope', '!=', ExamScope::SCHOOL->value)
            ->get()
            ->keyBy(fn(self $row) => $row->scope->value);

        $benchmarks = [];
        foreach ([ExamScope::AREA, ExamScope::PROVINCE, ExamScope::OBEC, ExamScope::NATIONAL] as $scope) {
            $row = $rows->get($scope->value);
            if ($row !== null) {
                $benchmarks[$scope->value] = [
                    'label' => $scope->label(),
                    'scores' => collect($subjects)->mapWithKeys(fn($s) => [$s => $row->{$s}])->all(),
                    'average' => $row->average,
                    'examinees' => $row->examinees,
                    'computed' => false,
                ];
            }
        }

        if (! isset($benchmarks[ExamScope::AREA->value])) {
            $computed = static::query()
                ->where('examType', $type->value)
                ->where('academicYear', $academicYear)
                ->where('scope', ExamScope::SCHOOL->value)
                ->toBase()
                ->select(static::aggregateColumns($type))
                ->first();

            if ($computed !== null && (int) $computed->schoolCount > 0) {
                $area = [
                    'label' => ExamScope::AREA->label(),
                    'scores' => collect($subjects)
                        ->mapWithKeys(fn($s) => [$s => $computed->{$s} === null ? null : (float) $computed->{$s}])
                        ->all(),
                    'average' => $computed->average === null ? null : (float) $computed->average,
                    'examinees' => $computed->examineeTotal === null ? null : (int) $computed->examineeTotal,
                    'computed' => true,
                ];
                $benchmarks = [ExamScope::AREA->value => $area] + $benchmarks;
            }
        }

        return $benchmarks;
    }
}
