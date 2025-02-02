<?php namespace App;

/**
 * Activity class
 */
class Activity
{

    public static function create($activityId, $userId = null, $id = null, $details = null)
    {
        // Use USER_ID if no userId is provided
        $userId = $userId ?? USER_ID;

        // Ensure userId is an integer
        if (is_array($userId)) {
            error_log('Activity::create received array for userId: ' . print_r($userId, true));
            throw new \Exception('userId must be an integer, array given');
        }
        $userId = (int)$userId;

        // Convert array or object details to JSON string
        if (is_array($details) || is_object($details)) {
            $details = json_encode($details, JSON_UNESCAPED_UNICODE);
        }

        // Insert the activity into DB
        Db::insert('activityLog', [
            'userId' => $userId,
            'activityId' => $activityId,
            'activityLogTimestamp' => date('Y-m-d H:i:s'),
            'id' => $id,
            'details' => $details
        ]);
    }

    public static function getUserLatestActivityTime($userId, $activityId)
    {
        $userId = (int)$userId;
        $activityId = (int)$activityId;
        return Db::getOne("SELECT MAX(activityLogTimestamp) FROM activityLog WHERE userId = $userId and activityId = $activityId ORDER BY activityLogId");
    }

    public static function logs($criteria = null)
    {
        $where = SQL::getWhere($criteria);
        return Db::getAll("
            SELECT *, DATE_FORMAT(activityLogTimestamp, '%Y-%m-%d %H:%i') activityLogTimestamp
            FROM activityLog JOIN users USING (userId) JOIN activities USING (activityId)
            $where
            ORDER BY activityLogId DESC
            ");
    }

}
