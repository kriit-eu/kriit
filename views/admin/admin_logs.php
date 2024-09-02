<?php
$activityLinks = [
        ACTIVITY_SOLVED_EXERCISE => 'exercises',
        ACTIVITY_SOLVED_AGAIN_THE_SAME_EXERCISE => 'exercises',
        ACTIVITY_START_EXERCISE => 'exercises',
        ACTIVITY_CREATE_GROUP => 'groups',
        ACTIVITY_CREATE_SUBJECT => 'subjects',
        ACTIVITY_CREATE_ASSIGNMENT => 'assignments',
        ACTIVITY_UPDATE_ASSIGNMENT => 'assignments',
];
?>

<style>
    .pause, .leave {
        background-color: pink;
    }

    .play, .timeupdate {
        background-color: lightgreen;
    }

    body > div.container > a:not(.active) {
        background-color: white !important;
        color: black !important;
        border: 0;
    }

    body > div.container > a.active {

    }
</style>

<br>
<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <tr>
            <th>Aeg</th>
            <th>Kasutaja</th>
            <th>Tegevus</th>
            <th>Üksus</th>
        </tr>
        <?php foreach ($log as $row): ?>
            <tr class="<?= $row['activityName'] ?>">
                <td><?= $row['activityLogTimestamp'] ?></td>
                <td><?= $row['userName'] ?></td>
                <td><?= __($row['activityDescription'], "activities.activityDescription") ?></td>
                <td>
                    <?php if (isset($activityLinks[$row['activityId']])): ?>
                        <a href="<?= $activityLinks[$row['activityId']] ?>/<?= $row['id'] ?>"><?= $row['id'] ?></a>
                    <?php else: ?>
                        <?= $row['id'] ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach ?>
    </table>
</div>
