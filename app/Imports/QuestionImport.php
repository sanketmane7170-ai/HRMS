<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\DB;
use Modules\PerformanceReview\Entities\Question;
use Modules\PerformanceReview\Entities\QuestionOption;
use Modules\PerformanceReview\Entities\QuestionSet;

class QuestionImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        // Skip header row
        $rows->shift();

        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                $questionSetTitle = trim($row[0]);  // Column A = question_set
                $questionSet = QuestionSet::where('name', $questionSetTitle)->first();

                if (!$questionSet) {
                    // Skip or create new question set
                    continue;
                }

                $question = Question::create([
                    'question_set_id' => $questionSet->id,
                    'question_text' => $row[1],
                    'max_score' => $row[6] ?? 10,
                ]);

                $options = [
                    'a' => $row[2],
                    'b' => $row[3],
                    'c' => $row[4],
                    'd' => $row[5],
                ];

                $correct = strtolower(trim($row[7]));

                foreach ($options as $key => $value) {
                    if (!$value) continue;

                    QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => $value,
                        'is_correct' => $key === $correct,
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
