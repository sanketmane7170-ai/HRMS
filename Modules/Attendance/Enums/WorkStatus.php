<?php

namespace Modules\Attendance\Enums;

enum WorkStatus: string
{
    case AVAILABLE = 'available';
    case ENGAGED = 'engaged';
    case MEAL_BREAK = 'meal_break';
    case SHORT_BREAK = 'short_break';
    case DND = 'dnd';
    case ON_LEAVE = 'on_leave';
    case OFFLINE = 'offline';

    public function label(): string
    {
        return match($this) {
            self::AVAILABLE => 'Available for Collaboration',
            self::ENGAGED => 'Engaged at Work',
            self::MEAL_BREAK => 'Meal Break',
            self::SHORT_BREAK => 'Short Break',
            self::DND => 'Do Not Disturb',
            self::ON_LEAVE => 'On Leave',
            self::OFFLINE => 'Offline',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::AVAILABLE => '#10b981', // Emerald
            self::ENGAGED => '#6366f1',   // Indigo
            self::MEAL_BREAK => '#f59e0b', // Amber
            self::SHORT_BREAK => '#3b82f6', // Blue
            self::DND => '#ef4444',       // Red
            self::ON_LEAVE => '#8b5cf6',   // Violet
            self::OFFLINE => '#64748b',    // Slate
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::AVAILABLE => 'fas fa-check-circle',
            self::ENGAGED => 'fas fa-briefcase',
            self::MEAL_BREAK => 'fas fa-utensils',
            self::SHORT_BREAK => 'fas fa-coffee',
            self::DND => 'fas fa-minus-circle',
            self::ON_LEAVE => 'fas fa-calendar-times',
            self::OFFLINE => 'fas fa-power-off',
        };
    }
}
