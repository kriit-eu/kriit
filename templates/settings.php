<div class="container mt-5">
    <h2>Account Settings</h2>
    
    <div class="card mb-4">
        <div class="card-header">
            <h4>Change Email</h4>
        </div>
        <div class="card-body">
            <form id="emailForm">
                <div class="form-group">
                    <label for="email">New Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="emailPassword">Current Password</label>
                    <input type="password" class="form-control" id="emailPassword" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Update Email</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4>Change Password</h4>
        </div>
        <div class="card-body">
            <form id="passwordForm">
                <div class="form-group">
                    <label for="currentPassword">Current Password</label>
                    <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                </div>
                <div class="form-group">
                    <label for="newPassword">New Password</label>
                    <input type="password" class="form-control" id="newPassword" name="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Update Password</button>
            </form>
        </div>
    </div>
</div> 