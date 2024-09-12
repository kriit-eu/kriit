<div class="d-flex justify-content-end">
    <?php if($auth->userIsAdmin): ?>
        <button type="button" class="btn btn-warning admin-btn me-4" onclick="redirectToApplicants()">Admin</button>
    <?php endif; ?>
    <button type="button" class="btn btn-secondary logout-btn" onclick="redirectToLogout()">Logout</button>
</div>

<script>
    const redirectToApplicants = () => {
        window.location.href = 'applicants';
    }

    const redirectToLogout = () => {
        window.location.href = 'logout';
    }
</script>

<style>
    .admin-btn, .logout-btn {
        position: absolute;
        top: 10px;
        z-index: 1000;
    }
    .admin-btn {
        right: 80px;
    }
    .logout-btn {
        right: 10px;
    }
</style>
