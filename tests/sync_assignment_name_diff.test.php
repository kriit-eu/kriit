<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../constants.php';
// Test for assignmentName difference detection in Sync::getSystemDifferences
use App\Sync;


// Fetch an existing subject and assignment from the DB

// Use Plastitööd subject and assignment
$subject = [
    'subjectId' => 2,
    'subjectExternalId' => 336151,
    'subjectName' => 'Plastitööd',
    'groupId' => 2,
    'teacherId' => 3
];
$assignment = [
    'assignmentExternalId' => 3258802,
    'assignmentName' => 'Iseseisev töö (ÕV3, ÕV4, ÕV5)',
    'subjectId' => 2
];
$teacher = [
    'userPersonalCode' => '37702166012',
    'userName' => 'Andrus Küttä'
];
$group = [
    'groupName' => 'AM22'
];


// Prepare remote payload with a different assignmentName
$remoteSubjects = [
    [
        'subjectExternalId' => $subject['subjectExternalId'],
        'groupName' => $group['groupName'],
        'teacherPersonalCode' => $teacher['userPersonalCode'],
        'teacherName' => $teacher['userName'],
        'assignments' => [
            [
                'assignmentExternalId' => $assignment['assignmentExternalId'],
                'assignmentName' => 'DIFFERENT NAME', // force a real difference
                'assignmentInstructions' => 'Do this work',
                'assignmentDueAt' => '2025-07-20',
                'assignmentEntryDate' => '2025-07-10',
                'results' => []
            ]
        ]
    ]
];

$systemId = 1;
$diffs = Sync::getSystemDifferences($remoteSubjects, $systemId);

if (empty($diffs)) {
    echo "FAILED: No differences detected\n";
    exit(1);
}

echo "DEBUG: Full diffs output:\n";
print_r($diffs);

$found = false;
foreach ($diffs as $subject) {
    foreach ($subject['assignments'] ?? [] as $assignment) {
        if (isset($assignment['assignmentName'])) {
            $diff = $assignment['assignmentName'];
            $kriitVal = isset($diff['kriit']) ? $diff['kriit'] : '(missing)';
            $remoteVal = isset($diff['remote']) ? $diff['remote'] : '(missing)';
            echo "DIFFERENCE: assignmentName\n";
            echo "  Kriit:  $kriitVal\n";
            echo "  Remote: $remoteVal\n";
            $found = true;
        }
    }
}
if (!$found) {
    echo "FAILED: assignmentName difference not detected\n";
    exit(1);
}

$systemId = 1;
$diffs = Sync::getSystemDifferences($remoteSubjects, $systemId);

if (empty($diffs)) {
    echo "FAILED: No differences detected\n";
    exit(1);
}

$found = false;
foreach ($diffs as $subject) {
    foreach ($subject['assignments'] ?? [] as $assignment) {
        if (isset($assignment['assignmentName'])) {
            echo "PASSED: assignmentName difference detected\n";
            $found = true;
        }
    }
}
if (!$found) {
    echo "FAILED: assignmentName difference not detected\n";
    exit(1);
}
