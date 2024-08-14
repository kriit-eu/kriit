<div class="d-flex justify-content-end">
    <button type="button" class="btn btn-warning admin-btn" onclick="redirectToApplicants()">Admin</button>
</div>

<script>
    const redirectToApplicants = () => {
        window.location.href = 'applicants';
    }
</script>

<style>
    .admin-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 1000;
    }
</style>