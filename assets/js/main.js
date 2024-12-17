const RELOAD = 33;
var error_modal = $("#error-modal");

$(document).ready(function () {
    const $userPersonalCodeInput = $("#userPersonalCode");
    const $userPersonalCodeHelp = $("#userPersonalCodeHelp");
    const $userPasswordHelp = $("#userPasswordHelp");
    const $submitButton = $("#submitButton");
    const $passwordField = $("#password-field");
    const $passwordInput = $("#userPassword");

    // For testing purposes
    // const userPersonalCodePattern = /^\d{11}$/;

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

    $userPersonalCodeInput.on("input", function () {
        userPersonalCodeValue = $userPersonalCodeInput.val();
        const context = $userPersonalCodeInput.data('context') || 'default';
        validateUserPersonalCode(context);
    });

    $passwordInput.on("input", () => {
        $submitButton.prop("disabled", !$passwordInput.val());
    });

    // Settings page functionality
    const $emailForm = $('#emailForm');
    const $passwordForm = $('#passwordForm');
    const $settingsToast = $('#settingsToast');

    if ($emailForm.length) {
        $emailForm.on('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const newEmail = $('#newEmail').val();
            const $form = $(this);
            
            ajax('settings/updateEmail', 
                $form.serialize(),
                function(response) {
                    // Update the displayed email
                    $('#currentEmail').text(newEmail);
                    
                    // Clear the form inputs but not the displayed email
                    $form.find('input').val('');

                    // Show success toast
                    const toast = new bootstrap.Toast($settingsToast[0]);
                    $('.toast-body').text('E-posti aadress edukalt uuendatud');
                    toast.show();
                }
            );
            return false;
        });
    }

    if ($passwordForm.length) {
        console.log('Password form found, attaching handler');
        
        // Real-time password validation
        $('#newPassword, #confirmPassword').on('input', function() {
            const newPassword = $('#newPassword').val();
            const confirmPassword = $('#confirmPassword').val();
            const $confirmInput = $('#confirmPassword');
            
            // Show password match feedback
            if (confirmPassword) {
                if (newPassword !== confirmPassword) {
                    $confirmInput.addClass('is-invalid');
                } else {
                    $confirmInput.removeClass('is-invalid');
                }
            }
        });

        $passwordForm.on('submit', function(e) {
            console.log('Password form submitted');
            e.preventDefault();
            e.stopPropagation();
            const $form = $(this);
            
            const newPassword = $('#newPassword').val();
            const confirmPassword = $('#confirmPassword').val();
            const formData = $form.serialize();
            
            console.log('Validating passwords...');
            if (newPassword !== confirmPassword) {
                $('#confirmPassword').addClass('is-invalid');
                return false;
            }

            if (newPassword.length < 8) {
                $('#newPassword').addClass('is-invalid');
                show_error_modal('Viga', 'Parool peab olema vähemalt 8 tähemärki pikk');
                return false;
            }
            
            console.log('Form data:', formData);
            console.log('Sending password update request...');
            
            ajax('settings/updatePassword', 
                formData,
                function(response) {
                    console.log('Password update response:', response);
                    // Clear the form and validation states
                    $form.find('input').val('').removeClass('is-invalid');

                    // Show success toast
                    const toast = new bootstrap.Toast($settingsToast[0]);
                    $('.toast-body').text('Parool edukalt uuendatud');
                    toast.show();
                },
                function(error) {
                    console.error('Password update error:', error);
                    show_error_modal('Viga', error);
                }
            );
            return false;
        });
    } else {
        console.log('Password form not found');
    }
});


function tryToParseJSON(jsonString) {
    try {
        var o = JSON.parse(jsonString);

        // Handle non-exception-throwing cases:
        // Neither JSON.parse(false) or JSON.parse(1234) throw errors, hence the type-checking,
        // but... JSON.parse(null) returns null, and typeof null === "object",
        // so we must check for that, too. Thankfully, null is falsey, so this suffices:
        if (o && typeof o === "object") {
            return o;
        }
    } catch (e) {
    }

    return false;
}


function ajax(url, options, callback_or_redirect_url, error_callback) {
    $.post(url, options)
        .fail(function (jqXHR, textStatus, errorThrown) {
            console.error('XHR error:', jqXHR, textStatus, errorThrown);

            let errorMessage = 'An error occurred while processing your request.';
            let details = '';

            const json = tryToParseJSON(jqXHR.responseText);
            if (json) {
                if (json.message) {
                    errorMessage = json.message;
                } else if (typeof json.data !== 'undefined') {
                    errorMessage = json.data;
                } else {
                    details = `<pre>${JSON.stringify(json, null, 2)}</pre>`;
                }
            } else {
                details = jqXHR.responseText || errorThrown;
            }

            // Fallback for HTTP status codes
            if (jqXHR.status) {
                const statusCode = jqXHR.status;
                const statusText = jqXHR.statusText || '';
                errorMessage = `${statusCode}: ${statusText}`;
            }

            // Execute error callback or show error modal
            if (typeof error_callback === 'function') {
                error_callback(errorMessage, details);
            } else {
                show_error_modal(errorMessage, details);
            }
        })
        .done(function (response) {
            let parsedResponse = typeof response === 'object' && response !== null ? response : tryToParseJSON(response);

            if (!parsedResponse) {
                console.error('Invalid JSON response:', response);

                // Send error report for invalid JSON
                $.post('email/send_error_report', {
                    javascript_received_json_payload_that_caused_the_error: response,
                });

                show_error_modal('Invalid response format', response);
                return false;
            }

            // Handle server-side errors
            if (parsedResponse.status && parsedResponse.status.toString()[0] !== '2') {
                if (typeof error_callback === 'function') {
                    error_callback(parsedResponse.data || 'An error occurred.', parsedResponse);
                } else {
                    show_error_modal(parsedResponse.data || 'An error occurred.', parsedResponse);
                }
                return false;
            }

            // Success logic
            if (typeof callback_or_redirect_url === 'function') {
                callback_or_redirect_url(parsedResponse);
            } else if (typeof callback_or_redirect_url === 'string') {
                location.href = callback_or_redirect_url;
            } else if (callback_or_redirect_url === RELOAD) {
                location.reload();
            }
        });
}

function validateControlNumber(code) {
    const multipliers1 = [1, 2, 3, 4, 5, 6, 7, 8, 9, 1];
    const multipliers2 = [3, 4, 5, 6, 7, 8, 9, 1, 2, 3];
    let sum = 0;
    for (let i = 0; i < 10; i++) {
        sum += parseInt(code[i]) * multipliers1[i];
    }
    let mod = sum % 11;

    if (mod === 10) {
        sum = 0;
        for (let i = 0; i < 10; i++) {
            sum += parseInt(code[i]) * multipliers2[i];
        }

        mod = sum % 11;

        if (mod === 10) {
            mod = 0;
        }
    }
    // for testing purposes
    // return true;

    return mod === parseInt(code[10]);
}

$('table.clickable-rows tr').on('click', function () {
    window.location = $(this).data('href');
});

function show_error_modal(error, title = false) {
    error_modal.modal({
        title: title ? title : '<?= __(\'Oops...\') ?>',
        content: error,  // Assuming you want to display the error here
        classContent: 'centered',
        class: 'small'
    }).modal('show');
}
