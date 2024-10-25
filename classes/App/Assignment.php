<?php

namespace App;

class Assignment
{

    public static function deadlineColor(int $daysRemaining): string
    {
        if ($daysRemaining >= 3) {
            return 'badge bg-light text-dark';
        } elseif ($daysRemaining > 0) {
            return 'badge bg-warning text-dark';
        } else {
            return 'badge bg-danger';
        }
    }
}