<style>
    #subject-table th {
        background-color: #f2f2f2;
    }
</style>
<div class="row">

    <h1>Gruppid</h1>
    <div class="table-responsive">
        <table id="subject-table" class="table table-bordered">
            <thead>
            <tr>
                <th>Grupp</th>
                <th>Ainete arv</th>
                <th>Tegevused</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($groups as $group): ?>
                <tr data-href="subjects/<?= $group['groupId'] ?>">
                    <td><b><?= $group['groupName'] ?></b></td>
                    <td><b><?= $group['subjectCount'] ?></b></td>
                    <td>
                        <a href="gropus/<?= $group['groupId'] ?>">Vaata</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
