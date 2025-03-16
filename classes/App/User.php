<?php namespace App;

/**
 * Created by PhpStorm.
 * User: henno
 * Date: 29/10/16
 * Time: 22:24
 */
class User
{
    static function register($userName, $userEmail, $userPassword, $userIsAdmin = 0)
    {
        // Get data to be inserted from function argument list
        $data = get_defined_vars();

        // Hash the password
        $data['userPassword'] = password_hash($userPassword, PASSWORD_DEFAULT);

        // Insert user into database
        $userId = Db::insert('users', $data);

        // Return new user's ID
        return $userId;
    }

    public static function get($criteria = null, $orderBy = null)
    {
        $criteria = $criteria ? 'AND ' . implode("AND", $criteria) : '';
        $orderBy = $orderBy ? $orderBy : 'userName';
        return Db::getAll("
            SELECT userId, userName, userPersonalCode, userIsAdmin, groupId
            FROM users
            WHERE userDeleted=0 $criteria
            ORDER BY $orderBy");
    }

    public static function login($userId)
    {
        Activity::create(ACTIVITY_LOGIN, $userId);
        $_SESSION['userId'] = $userId;
    }

    public static function edit(int $userId, array $data)
    {
        if (!is_numeric($userId) || $userId < 0) {
            throw new \Exception('Invalid userId');
        }

        Db::update('users', $data, "userId = $userId");
    }

    public static function delete(int $userId)
    {
        if (!is_numeric($userId) || $userId < 0) {
            throw new \Exception('Invalid userId');
        }

        // Attempt to delete user from the database (works if user does not have related records in other tables)
        try {
            Db::delete('users', 'userId = ?', [$userId]);
        } catch (\Exception $e) {
            // If removing user did not work due to foreign key constraints then mark the user as deleted
            Db::update('users', [
                'userDeleted' => 1
            ], "userId=$userId");
        }
    }
    
    /**
     * Finds a user by their personal code
     * 
     * @param string $personalCode The user's personal code
     * @return array|null The user data or null if not found
     */
    public static function findByPersonalCode(string $personalCode): ?array
    {
        return Db::getFirst("SELECT * FROM users WHERE userPersonalCode = ?", [$personalCode]);
    }
    
    /**
     * Creates a new student user
     * 
     * @param string $personalCode Student's personal code
     * @param string $name Student's full name
     * @param int $systemId The ID of the external system
     * @param int|null $groupId The ID of the student's group
     * @param int|null $teacherId The ID of the teacher creating the student (for activity logging)
     * @param string|null $subjectName Subject name (for activity logging)
     * @return int The new user's ID
     */
    public static function createStudent(
        string $personalCode, 
        string $name, 
        int $systemId, 
        ?int $groupId = null,
        ?int $teacherId = null,
        ?string $subjectName = null
    ): int {
        // Generate email in format: their.full.name.without.umlauts@vikk.ee
        $email = strtolower(str_replace(
            [' ', 'õ', 'ä', 'ö', 'ü', 'Õ', 'Ä', 'Ö', 'Ü'],
            ['.', 'o', 'a', 'o', 'u', 'O', 'A', 'O', 'U'],
            $name
        )) . '@vikk.ee';
        
        $userData = [
            'userPersonalCode' => $personalCode,
            'userName' => $name,
            'userEmail' => $email,
            'userIsTeacher' => 0,
            'systemId' => $systemId,
            'groupId' => $groupId
        ];
        
        $userId = Db::insert('users', $userData);
        
        // Log user creation if teacher ID is provided
        if ($teacherId) {
            $activityData = [
                'systemId' => $systemId,
                'userName' => $name,
                'userPersonalCode' => $personalCode,
                'userIsTeacher' => 0,
                'groupId' => $groupId
            ];
            
            if ($subjectName) {
                $activityData['subjectName'] = $subjectName;
            }
            
            Activity::create(ACTIVITY_CREATE_USER_SYNC, $teacherId, $userId, $activityData);
        }
        
        return $userId;
    }
    
    /**
     * Updates a user's name if it has changed in the external system (e.g., after marriage)
     * 
     * @param array $user The user data from Kriit
     * @param string $remoteName The user name from the external system
     * @param int $systemId The ID of the external system
     * @return bool Whether the name was updated
     */
    public static function updateNameIfNeeded(array $user, string $remoteName, int $systemId): bool
    {
        // If names are the same, no update needed
        if ($user['userName'] === $remoteName) {
            return false;
        }
        
        // Update the user's name in the database
        Db::update('users', [
            'userName' => $remoteName
        ], "userId = {$user['userId']}");
        
        // Log the name change
        Activity::create(ACTIVITY_UPDATE_USER_NAME, null, $user['userId'], [
            'systemId' => $systemId,
            'oldName' => $user['userName'],
            'newName' => $remoteName,
            'userPersonalCode' => $user['userPersonalCode']
        ]);
        
        return true;
    }
    
    /**
     * Creates a new teacher user
     * 
     * @param string $personalCode Teacher's personal code
     * @param string $name Teacher's full name
     * @param int $systemId The ID of the external system
     * @return int The new user's ID
     */
    public static function createTeacher(string $personalCode, string $name, int $systemId): int
    {
        $userData = [
            'userPersonalCode' => $personalCode,
            'userName'         => $name,
            'userIsTeacher'    => 1,
            'systemId'         => $systemId
        ];
        
        $userId = Db::insert('users', $userData);
        
        // Log user creation
        Activity::create(ACTIVITY_ADD_USER, null, $userId, $userData);
        
        return $userId;
    }
    
    /**
     * Find a user by their ID
     * 
     * @param int $userId The user ID
     * @return array|null The user data or null if not found
     */
    public static function findById(int $userId): ?array
    {
        return Db::getFirst("SELECT * FROM users WHERE userId = ?", [$userId]);
    }

}
