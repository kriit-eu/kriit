<style>
    /* Add a light gradient background to the page */
    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #e4e9f2 100%);
        min-height: 100vh;
    }

    /* Ensure container has a transparent background to show the gradient */
    #container {
        background-color: transparent;
    }

    /* Make sure table cells have white background to stand out */
    #grading-table td {
        background-color: white;
    }

    #grading-table th {
        background-color: #f2f2f2;
    }

    /* Add subtle shadow to tables for better contrast with background */
    #grading-table {
        background-color: transparent;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.07);
    }

    /* Ensure the table-responsive container is also transparent */
    .table-responsive {
        background-color: transparent;
    }

    /* More subtle cell borders */
    #grading-table td,
    #grading-table th {
        border-color: #d8d8d8 !important;
    }

    /* Ensure table layout is fixed for consistent column widths but allows scaling */
    #grading-table {
        table-layout: fixed !important;
        border: 1px solid #d8d8d8 !important; /* More subtle border color */
        border-collapse: collapse !important; /* Ensure borders collapse properly */
        width: 100% !important; /* Use full width */
    }

    /* Set width for the position column (first column) */
    #grading-table td:first-child,
    #grading-table th:first-child {
        width: 60px;
        min-width: 60px;
        max-width: 60px;
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-weight: bold;
        color: #666;
    }

    /* Set width for the timestamp column (second column) */
    #grading-table td:nth-child(2),
    #grading-table th:nth-child(2) {
        width: 130px;
        min-width: 130px;
        max-width: 130px;
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Set width for the student name column (third column) */
    #grading-table td:nth-child(3),
    #grading-table th:nth-child(3) {
        width: 180px;
        min-width: 180px;
        max-width: 180px;
        vertical-align: middle;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Set width for the combined subject/assignment column (fourth column) */
    #grading-table td:nth-child(4),
    #grading-table th:nth-child(4) {
        vertical-align: middle;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.3;
    }

    /* Style for assignment name (bold) */
    .assignment-name {
        font-weight: bold;
        display: inline;
        margin-right: 8px;
    }

    /* Style for subject name badge - keep neutral gray */
    .subject-name {
        display: inline-block;
        background-color: #e9ecef;
        color: #495057;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.85em;
        font-weight: normal;
        vertical-align: middle;
    }

    /* Rainbow colors for table rows - more visible tints */
    .subject-row-0 { background-color: rgba(231, 76, 60, 0.25) !important; } /* Red */
    .subject-row-1 { background-color: rgba(230, 126, 34, 0.25) !important; } /* Orange */
    .subject-row-2 { background-color: rgba(241, 196, 15, 0.3) !important; } /* Yellow */
    .subject-row-3 { background-color: rgba(46, 204, 113, 0.25) !important; } /* Green */
    .subject-row-4 { background-color: rgba(52, 152, 219, 0.25) !important; } /* Blue */
    .subject-row-5 { background-color: rgba(155, 89, 182, 0.25) !important; } /* Purple */
    .subject-row-6 { background-color: rgba(233, 30, 99, 0.25) !important; } /* Pink */
    .subject-row-7 { background-color: rgba(0, 188, 212, 0.25) !important; } /* Cyan */
    .subject-row-8 { background-color: rgba(255, 152, 0, 0.25) !important; } /* Deep Orange */
    .subject-row-9 { background-color: rgba(96, 125, 139, 0.25) !important; } /* Blue Grey */
    .subject-row-10 { background-color: rgba(139, 195, 74, 0.25) !important; } /* Light Green */
    .subject-row-11 { background-color: rgba(255, 87, 34, 0.25) !important; } /* Deep Orange Red */
    .subject-row-12 { background-color: rgba(121, 85, 72, 0.25) !important; } /* Brown */
    .subject-row-13 { background-color: rgba(0, 150, 136, 0.25) !important; } /* Teal */
    .subject-row-14 { background-color: rgba(103, 58, 183, 0.25) !important; } /* Deep Purple */
    .subject-row-15 { background-color: rgba(255, 64, 129, 0.25) !important; } /* Pink Accent */
    .subject-row-16 { background-color: rgba(76, 175, 80, 0.25) !important; } /* Material Green */
    .subject-row-17 { background-color: rgba(33, 150, 243, 0.25) !important; } /* Material Blue */
    .subject-row-18 { background-color: rgba(255, 111, 0, 0.3) !important; } /* Amber */
    .subject-row-19 { background-color: rgba(55, 71, 79, 0.25) !important; } /* Dark Blue Grey */
    .subject-row-20 { background-color: rgba(211, 47, 47, 0.25) !important; } /* Dark Red */
    .subject-row-21 { background-color: rgba(123, 31, 162, 0.25) !important; } /* Dark Purple */
    .subject-row-22 { background-color: rgba(56, 142, 60, 0.25) !important; } /* Dark Green */
    .subject-row-23 { background-color: rgba(25, 118, 210, 0.25) !important; } /* Dark Blue */

    /* Ensure table cells maintain white background within colored rows */
    .subject-row-0 td, .subject-row-1 td, .subject-row-2 td, .subject-row-3 td,
    .subject-row-4 td, .subject-row-5 td, .subject-row-6 td, .subject-row-7 td,
    .subject-row-8 td, .subject-row-9 td, .subject-row-10 td, .subject-row-11 td,
    .subject-row-12 td, .subject-row-13 td, .subject-row-14 td, .subject-row-15 td,
    .subject-row-16 td, .subject-row-17 td, .subject-row-18 td, .subject-row-19 td,
    .subject-row-20 td, .subject-row-21 td, .subject-row-22 td, .subject-row-23 td {
        background-color: transparent !important;
    }

    /* Style for timestamp badge */
    .id-badge {
        background-color: #e9ecef;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 0.85em;
        color: #495057;
    }
</style>

<div class="row">
    <div class="col-12">
        <h1>Hindamine</h1>
        <div class="table-responsive" style="background-color: transparent;">
            <table id="grading-table" class="table table-bordered" style="background-color: transparent; table-layout: fixed !important;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Aeg</th>
                        <th>Õpilane</th>
                        <th>Ülesanne / Aine</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->submissions as $index => $submission): ?>
                        <tr class="subject-row-<?= $this->subjectColors[$submission['subjectId']] ?>">
                            <td><?= $index + 1 ?></td>
                            <td><?= $submission['Aeg'] ? '<span class="id-badge"><strong>' . (new DateTime($submission['Aeg']))->format('d.m.y') . '</strong> ' . (new DateTime($submission['Aeg']))->format('H:i') . '</span>' : '' ?></td>
                            <td><?= $submission['Õpilane'] ?></td>
                            <td>
                                <span class="assignment-name"><?= $submission['Ülesanne'] ?></span>
                                <span class="subject-name"><?= $submission['Aine'] ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Initialize tooltips
        [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            .map(el => new bootstrap.Tooltip(el));
    });
</script>
