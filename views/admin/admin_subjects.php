<style>
    #subject-table th {
        background-color: #f2f2f2;
    }
    #subject-table tr {
        cursor: pointer;
    }
</style>
<div class="row">
    <h1>Ained</h1>
    <div class="table-responsive">
        <table id="subject-table" class="table table-bordered">
            <thead>
            <tr>
                <th>Nimi</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($subjects as $subject): ?>
                <tr onclick="window.location.href='admin/subjects/<?= $subject['subjectId'] ?>'">
                    <td><b><?= $subject['subjectName'] ?></b></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
