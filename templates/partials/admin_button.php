<div class="d-flex justify-content-end">
    <button type="button" class="btn btn-warning" onclick="redirectToApplicants()">Admin</button>
</div>

<script>
    const redirectToApplicants = () => {
        window.location.href = 'applicants';
    }
</script>

<style>
    .admin-btn {
        position: fixed;
        top: 10px;
        left: 10px;
        z-index: 1000;
    }
</style>