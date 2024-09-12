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
    const userPersonalCodePattern = /^\d{11}$/;

    // const userPersonalCodePattern = /^[1-6]\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])\d{4}$/;

    let userPersonalCodeValue = "";

    function resetPasswordHelp() {
        $userPasswordHelp.text("Sisesta oma parool").removeClass("text-danger text-success");
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
                showSuccess($userPasswordHelp, "Isikukood on õige. Palun sisesta oma uus parool.");
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
            // Устанавливаем текст в зависимости от контекста
            const defaultText = context === 'admin' ? "Sisesta administraatori isikukood" :
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
            console.log('Xhr error: ', jqXHR, textStatus, errorThrown);
            let error;
            let json = tryToParseJSON(jqXHR.responseText);
            if (json === false) {
                error = jqXHR.responseText;
            } else {
                if (typeof json.data === 'undefined') {
                    error = `<pre>${JSON.stringify(json, null, 2)}</pre>`;
                } else {
                    error = json.data;
                }
            }

            if (typeof error_callback === 'function') {
                error_callback(error);
            } else {
                show_error_modal(error, errorThrown);
            }

        })
        .done(function (response) {
            let json = tryToParseJSON(response);

            console.log('.done');
            if (json === false) {

                // Send error report
                $.post('email/send_error_report', {
                    javascript_received_json_payload_that_caused_the_error: response
                });

                show_error_modal(response);

                return false;


            } else if (json.status === 500) {

                // Send error report
                $.post('email/send_error_report', {
                    javascript_received_json_payload_that_caused_the_error: json
                });


                if (typeof error_callback === 'function') {
                    error_callback(json);
                } else {
                    show_error_modal(json.data);
                }

                return false;


            } else if (json.status.toString()[0] !== '2') {

                if (typeof error_callback === 'function') {
                    error_callback(json);
                } else {
                    show_error_modal(json.data);
                }

            } else {

                if (typeof callback_or_redirect_url === 'function') {
                    callback_or_redirect_url(json);
                } else if (typeof callback_or_redirect_url === 'string') {
                    location.href = callback_or_redirect_url;
                } else if (callback_or_redirect_url === RELOAD) {
                    location.reload();
                }
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
    return true;

    // return mod === parseInt(code[10]);
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
