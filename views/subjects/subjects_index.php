<style>
    #subject-table th {
        background-color: #f2f2f2;
    }
</style>
<div class="row">

    <h1>Ained</h1>
    <div class="table-responsive">
        <table id="subject-table" class="table table-bordered">
            <thead>
            <tr>
                <th>Aine</th>
                <th>Õpetaja</th>
                <th>Tegevused</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($subjects as $subject): ?>
                <tr data-href="subjects/<?= $subject['subjectId'] ?>">
                    <td><b><?= $subject['subjectName'] ?></b></td>
                    <td><b><?= $subject['teacherName'] ?></b></td>
                    <td>
                        <a href="subjects/<?= $subject['subjectId'] ?>">Vaata</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
