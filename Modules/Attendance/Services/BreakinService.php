<?php

namespace Modules\Attendance\Services;

use Exception;
use Modules\Attendance\Entities\Breakin;
use Modules\Attendance\Enums\BreakinType;

class BreakinService
{

    public function performBreakInBreakOut(): Breakin|Exception
    {
        try {
            /// check if multi breakin and breakout are allowed
            if (config('attendance.multi_breakins_allowed')) {
                $breakin = $this->multiBreakInBreakOut();
            } else {
                $breakin = $this->singleBreakInBreakOut();
            }

            return $breakin;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Allow uset to have multiple breakin and breakout events
     */
    private function multiBreakInBreakOut(): Breakin
    {
        $type = BreakinType::IN;
        $record = Breakin::my()->where([
            'date' => now()->toDateString(),
            'user_id' => auth()->id()
        ])->orderByDesc('id')->limit(1)->first();

        if ($record) {
            if ($record->type == BreakinType::IN->value) {
                $type = BreakinType::OUT;
            }
        }

        $breakin = Breakin::create([
            'user_id' => auth()->id(),
            'date' => now()->toDateString(),
            'time' => date('H:i:s'),
            'type' => $type
        ]);

        return $breakin;
    }

    /**
     * Only allowed the user to have only single break in and break out
     */
    private function singleBreakInBreakOut()
    {
        $breakinExist = Breakin::my()->where([
            'date' => now()->toDateString(),
            'type' => BreakinType::IN
        ])->exists();

        if (!$breakinExist) {
            $event = Breakin::create([
                'user_id' => auth()->id(),
                'date' => now()->toDateString(),
                'time' => date('H:i:s'),
                'type' => BreakinType::IN
            ]);
        } else {
            $breakOutExist = Breakin::my()->where([
                'date' => now()->toDateString(),
                'type' => BreakinType::OUT
            ])->exists();
            if (!$breakOutExist) {
                $event = Breakin::create([
                    'user_id' => auth()->id(),
                    'date' => now()->toDateString(),
                    'time' => date('H:i:s'),
                    'type' => BreakinType::OUT
                ]);
            } else {
                throw new Exception(__trans('you_already_have_break_out'));
            }
        }

        return $event;
    }
}
