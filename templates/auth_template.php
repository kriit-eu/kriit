<!DOCTYPE html>
<html lang="en">
<?php require 'templates/partials/master_header.php'; ?>
<body>
<style>
    body {
        padding-top: 50px;
    }

    .form-signin .form-signin-heading,
    .form-signin .checkbox {
        margin-bottom: 10px;
    }

    .form-signin .checkbox {
        font-weight: normal;
    }

    .form-signin .form-control {
        position: relative;
        font-size: 16px;
        height: auto;
        padding: 10px;
        -webkit-box-sizing: border-box;
        -moz-box-sizing: border-box;
        box-sizing: border-box;
    }

    .form-signin .form-control:focus {
        z-index: 2;
    }

    .modal-input input[type="text"] {
        margin-bottom: -1px;
        border-bottom-left-radius: 0;
        border-bottom-right-radius: 0;
    }

    .modal-input input[type="password"] {
        margin-bottom: 10px;
        border-top-left-radius: 0;
        border-top-right-radius: 0;
    }

</style>

<div class="container">
    <h2 class="ui header">Tere tulemast Viljandi Kutseõppekeskuse</h2>

    <?php if (isset($errors)) {
        foreach ($errors as $error): ?>
            <div class="alert alert-danger" role="alert">
                <?= $error ?>
            </div>
        <?php endforeach;
    } ?>

    <form method="post">
        <div class="mb-3">
            <label for="userPersonalCode" class="form-label">Isikukood</label>
            <input type="text" name="userPersonalCode" class="form-control" id="userPersonalCode"
                   aria-describedby="userPersonalCode">
            <div id="userPersonalCodeHelp" class="form-text">Sisesta enda isikukood</div>
        </div>

        <div class="mb-3" id="password-field" style="display: none">
            <label for="userPassword" class="form-label">Parool</label>
            <input type="password" name="userPassword" class="form-control" id="userPassword">
            <div id="userPasswordHelp" class="form-text">Sisesta teie parool</div>

        </div>
        <button type="submit" id="submitButton" class="btn btn-primary" disabled>Logi sisse</button>
    </form>
</div>
<script src="assets/js/main.js?<?= COMMIT_HASH ?>"></script>
<script>
$(document).ready(function () {
    const $userPersonalCodeInput = $("#userPersonalCode");
    const $userPersonalCodeHelp = $("#userPersonalCodeHelp");
    const $userPasswordHelp = $("#userPasswordHelp");
    const $submitButton = $("#submitButton");
    const $passwordField = $("#password-field");
    const $passwordInput = $("#userPassword");

    const userPersonalCodePattern = /^[1-9]\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])\d{4}$/;

    let userPersonalCodeValue = "";

    function resetPasswordHelp() {
        $userPasswordHelp.text("Sisesta parool").removeClass("text-danger text-success");
    }

    function showError(message) {
        $userPersonalCodeHelp.text(message).addClass("text-danger").removeClass("text-success");
        $passwordField.hide();
        resetPasswordHelp();
        $submitButton.prop("disabled", true);
    }

    function showSuccess(userHelpElement, message) {
        userHelpElement.text(message).addClass("text-success").removeClass("text-danger");
    }

    function handleAjaxResponse(response, context) {
        if (!response || !response.data || !response.data.user) {
            const message = "Selle isikukoodiga isik ei ole registreeritud.";
            if (context === 'applicant' || context === 'admin') {
                showSuccess($userPersonalCodeHelp, message);
            } else {
                showError(message);
            }
            return;
        }
        if (response.data.user.userIsAdmin || response.data.user.userIsTeacher || response.data.user.groupId) {
            if (!response.data.user.isPasswordSet) {
                showSuccess($userPasswordHelp, "Isikukood on õige. Palun sisesta uus parool.");
            }
            $passwordField.show();
        } else {
            $submitButton.prop("disabled", false);
        }
    }

    function validateUserPersonalCode(context) {
        if (userPersonalCodeValue.length > 11) {
            showError("Isikukood ei vasta mustrile");
        } else if (userPersonalCodeValue.length === 11) {
            if (userPersonalCodePattern.test(userPersonalCodeValue) && validateControlNumber(userPersonalCodeValue)) {
                ajax("users/check", {userPersonalCode: userPersonalCodeValue}, function (response) {
                    handleAjaxResponse(response, context);
                });
            } else {
                $userPersonalCodeHelp.text("Isikukood ei vasta mustrile")
                    .addClass("text-danger").removeClass("text-success");
                $passwordField.hide();
                $submitButton.prop("disabled", true);
            }
        } else {
            const defaultText = context === 'admin' ? "Sisesta kasutaja isikukood" :
                (context === 'applicant' ? "Sisesta kandidaadi isikukood" : "Sisesta enda isikukood");
            $userPersonalCodeHelp.text(defaultText).removeClass("text-danger text-success");
            $passwordField.hide();
            resetPasswordHelp();
            $submitButton.prop("disabled", true);
        }
    }

    function validateControlNumber(code) {
        if (code.length !== 11) return false;
        
        let sum = 0;
        // First stage
        for (let i = 0; i < 10; i++) {
            sum += parseInt(code.charAt(i)) * ((i % 9) + 1);
        }
        let controlNumber = sum % 11;
        
        // Second stage if control number is 10
        if (controlNumber === 10) {
            sum = 0;
            for (let i = 0; i < 10; i++) {
                sum += parseInt(code.charAt(i)) * ((i % 8) + 3);
            }
            controlNumber = sum % 11;
            if (controlNumber === 10) controlNumber = 0;
        }
        
        return controlNumber === parseInt(code.charAt(10));
    }

    $userPersonalCodeInput.on("input", function () {
        userPersonalCodeValue = $userPersonalCodeInput.val();
        const context = $userPersonalCodeInput.data('context') || 'default';
        validateUserPersonalCode(context);
    });

    $passwordInput.on("input", () => {
        $submitButton.prop("disabled", !$passwordInput.val());
    });
});
</script>
</body>
</html>
