<style>
    #subject-table th {
        background-color: #f2f2f2;
    }

    #subject-table tr {
        cursor: pointer;
    }

    .text-danger {
        color: red;
    }


    .modal-dialog {
        width: 70%;
        resize: both;
        overflow: visible;
        max-width: 100%;
    }

    .modal-content {
        height: 100%;
    }

</style>

<div class="row">
    <h1>Grupid</h1>
    <div class="col text-end mb-3">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addGroupModal"
                data-context="admin">
            Lisa uus grupp
        </button>
    </div>
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

<div class="modal fade" id="addGroupModal" tabindex="-1" role="dialog" aria-labelledby="addGroupModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addGroupModalLabel">Lisa uus grupp</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="addGroupForm">
                    <div class="mb-3">
                        <label for="groupName" class="mb-2">
                            Grupi nimi <span class="text-danger" data-bs-toggle="tooltip"
                                             title="See väli on kohustuslik">*</span>
                        </label>
                        <input type="text" class="form-control" id="groupName" name="groupName"
                               placeholder="Sisesta grupi nimi">
                    </div>

                    <!-- JSON Input -->
                    <div class="mb-3">
                        <label for="jsonInput" class="mb-2">
                            Õpilased <i class="bi bi-info-circle" data-bs-toggle="tooltip"
                                        title="JSON, mille saab opetaja-assistent Chrome’i laiendiga Tahvlist, kui ainepäevikus vajutada 'Õpilaste andmed JSON-ina' nuppu."></i>
                        </label>
                        <textarea class="form-control" id="jsonInput" name="jsonInput" rows="5"
                                  placeholder="Sisesta siia JSON andmed."></textarea>
                        <div id="jsonError" class="text-danger" style="display: none;">Vigane JSON formaat</div>
                        <!-- Error message -->
                    </div>
                    <div class="text-end">
                        <button type="submit" id="submitGroup" class="btn btn-primary me-2" disabled>Lisa uus</button>
                        <button type="button" id="cancelGroup" class="btn btn-secondary">Tühista</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    $(document).ready(function () {

        const cancelGroup = $('#cancelGroup');
        const submitGroup = $('#submitGroup');
        // Initialize CodeMirror
        let editor = CodeMirror.fromTextArea(document.getElementById("jsonInput"), {
            mode: "application/json",
            lineNumbers: false,
            matchBrackets: true,
            autoCloseBrackets: true,
            theme: "monokai",
        });

        // Refresh CodeMirror editor when modal is shown
        $('#addGroupModal').on('shown.bs.modal', function () {


            $('[data-bs-toggle="tooltip"]').tooltip(); // Initialize tooltips
            editor.refresh(); // Refresh CodeMirror to fix cursor issue
        });

        editor.on('change', function () {
            editor.save();
            validateForm();
        });

        cancelGroup.on('click', function () {
            clearGroupFrom();
        });

        function validateForm() {
            const groupName = $('#groupName').val();
            const jsonInput = editor.getValue();

            let isValidJson = true;
            try {
                if (jsonInput.trim()) JSON.parse(jsonInput);
            } catch (e) {
                isValidJson = false;
            }

            if (!isValidJson && jsonInput.trim()) {
                $('#jsonError').show();
            } else {
                $('#jsonError').hide();
            }

            $('#submitGroup').prop('disabled', !groupName || !isValidJson);
        }

        function clearGroupFrom() {
            $('#groupName').val('');
            editor.setValue('');
            $('#jsonError').hide();
            $('#submitGroup').prop('disabled', true);

        }

        // Trigger validation when the group name changes
        $('#group Name').on('input', validateForm);

        submitGroup.on('click', async function () {
            const groupName = $('#groupName').val();
            let jsonInput = editor.getValue();

            if (jsonInput) {
                try {
                    jsonInput = JSON.parse(jsonInput.trim());
                } catch (e) {
                    alert('Vigane JSON formaat');
                    return;
                }
            }

            const data = {
                groupName: groupName,
                students: jsonInput
            };

             await ajax('admin/addGroup', data,  async function (response) {
                 console.log("response", response);
                if (response.status === 200) {
                    window.location.reload();
                } else {
                    alert('Error: ' + response);
                }
            }, function (response) {
                alert('Error: ' + response);
            });

        });

    });


</script>


