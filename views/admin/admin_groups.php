<style>
    #subject-table th {
        background-color: #f2f2f2;
    }
    #subject-table tr {
        cursor: pointer;
    }
</style>
<div class="row">
    <h1>Grupid</h1>
    <div class="table-responsive">
        <table id="subject-table" class="table table-bordered">
            <thead>
            <tr>
                <th>Nimi</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($groups as $group): ?>
                <tr onclick="window.location.href='admin/groups/<?= $group['groupId'] ?>'">
                    <td><b><?= $group['groupName'] ?></b></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
