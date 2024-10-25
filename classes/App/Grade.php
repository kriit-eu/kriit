<?php namespace App;

class Grade
{
    static function isNegative($grade): bool
    {
        return $grade === 'MA' || (is_numeric($grade) && intval($grade) < 3);
    }

    public static function isPositive($grade): bool
    {
        return $grade === 'A' || (is_numeric($grade) && intval($grade) >= 3);
    }

    public static function isMissing($grade): bool
    {
        return empty($grade);
    }

    /**
     * @throws \Exception
     */
    public static function info($isStudent, $grade, $daysLeft, $assignmentStatusId): array
    {
        // Positive grades are always green
        if (Grade::isPositive($grade)) {
            return ['colorClass' => 'green-cell', 'tooltip' => $grade];
        }

        if ($isStudent) {

            if (Grade::isNegative($grade)) {

                if ($assignmentStatusId == ASSIGNMENT_STATUS_WAITING_FOR_REVIEW) {
                    return ['colorClass' => 'yellow-cell', 'tooltip' => 'Ülevaatamise ootel'];
                }

                if ($daysLeft <= 0) {
                    return ['colorClass' => 'red-cell', 'tooltip' => 'Vajab parandamist!'];
                }
                return ['colorClass' => 'red-cell', 'tooltip' => 'Vajab parandamist'];
            }

            if (Grade::isMissing($grade)) {

                if ($daysLeft <= 0) {

                    if ($assignmentStatusId == ASSIGNMENT_STATUS_WAITING_FOR_REVIEW) {
                        return ['colorClass' => 'yellow-cell', 'tooltip' => 'Ülevaatamise ootel'];
                    }

                    return ['colorClass' => 'red-cell', 'tooltip' => 'Esitamata ja üle tähtaja!'];
                }

                // If the assignment is not submitted and the deadline is approaching
                if ($daysLeft <= 3) {

                    if ($assignmentStatusId == ASSIGNMENT_STATUS_WAITING_FOR_REVIEW) {
                        return ['colorClass' => '', 'tooltip' => 'Ülevaatamise ootel'];
                    }

                    return ['colorClass' => 'yellow-cell', 'tooltip' => 'Tähtaeg läheneb!'];
                }

                // The deadline is still far away

                if ($assignmentStatusId == ASSIGNMENT_STATUS_WAITING_FOR_REVIEW) {
                    return ['colorClass' => '', 'tooltip' => 'Ülevaatamise ootel'];
                }

                return ['colorClass' => '', 'tooltip' => ''];
            }

            // Catch bugs
            throw new \Exception('Impossible situation: grade is not positive, negative or missing');
        }

        // Teacher sees all assignments that are waiting for his review as red
        if ($assignmentStatusId == ASSIGNMENT_STATUS_WAITING_FOR_REVIEW) {
            return ['colorClass' => 'red-cell', 'tooltip' => 'Ülevaatamise ootel'];
        }

        // Highlight students that have not submitted assignments when the deadline is due
        if ($daysLeft <= 0 && (Grade::isMissing($grade) || Grade::isNegative($grade))) {
            return ['colorClass' => 'yellow-cell', 'tooltip' => 'Deadline passed'];
        }

        return ['colorClass' => '', 'tooltip' => ''];
    }

}