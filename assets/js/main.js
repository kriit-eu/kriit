const RELOAD = 33;
var error_modal = $("#error-modal");

$(document).ready(function () {
    const $userPersonalCodeInput = $("#userPersonalCode");
    const $userPersonalCodeHelp = $("#userPersonalCodeHelp");
    const $userPasswordHelp = $("#userPasswordHelp");
    const $userPasswordLabel = $("label[for='userPassword']");
    const $submitButton = $("#submitButton");
    const $passwordField = $("#password-field");
    const $passwordInput = $("#userPassword");

    // For testing purposes
    // const userPersonalCodePattern = /^\d{11}$/;

    const userPersonalCodePattern = /^[1-9]\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])\d{4}$/;

    let userPersonalCodeValue = "";

    function resetPasswordHelp() {
        $userPasswordHelp.text("Sisestage oma parool").removeClass("text-danger text-success");
        $userPasswordLabel.text("Parool");
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
                // Update UI for new password setup
                $userPasswordLabel.text("Uus parool");
                showSuccess($userPasswordHelp, "Isikukood on õige. Palun määra endale uus parool.");
            } else {
                // Update UI for existing password entry
                $userPasswordLabel.text("Parool");
                $userPasswordHelp.text("Sisestage oma parool").removeClass("text-danger text-success");
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
        const password = $passwordInput.val();
        const isSettingNewPassword = $userPasswordLabel.text() === "Uus parool";
        
        if (isSettingNewPassword) {
            if (password.length === 0) {
                // Password field is empty - show original message
                $userPasswordHelp.text("Isikukood on õige. Palun määra endale uus parool.")
                    .removeClass("text-danger").addClass("text-success");
                $submitButton.prop("disabled", true);
            } else if (password.length > 0 && password.length < 8) {
                // Password too short
                $userPasswordHelp.text("Parool peab olema vähemalt 8 tähemärki")
                    .removeClass("text-success").addClass("text-danger");
                $submitButton.prop("disabled", true);
            } else if (password.length >= 8) {
                // Check password strength with zxcvbn
                if (typeof zxcvbn !== 'undefined') {
                    const result = zxcvbn(password);
                    
                    // Score: 0-4 (0=väga nõrk, 1=nõrk, 2=hea, 3=tugev, 4=väga tugev)
                    if (result.score < 2) {
                        // Weak password - show why it's weak
                        let message = "Parool on liiga nõrk";
                        if (result.feedback.warning) {
                            // Translate common warnings to Estonian
                            const warnings = {
                                "This is a top-10 common password": "See on üks levinumaid paroole",
                                "This is a top-100 common password": "See on väga levinud parool",
                                "This is a very common password": "See on väga levinud parool",
                                "This is similar to a commonly used password": "See sarnaneb levinud parooliga",
                                "A word by itself is easy to guess": "Üksik sõna on lihtne ära arvata",
                                "Names and surnames by themselves are easy to guess": "Nimed on lihtsad ära arvata",
                                "Dates are often easy to guess": "Kuupäevad on lihtsad ära arvata",
                                "Sequences like abc or 6543 are easy to guess": "Järjestused nagu abc või 123 on lihtsad ära arvata",
                                "Repeats like \"aaa\" are easy to guess": "Kordused nagu \"aaa\" on lihtsad ära arvata",
                                "Repeats like \"abcabcabc\" are only slightly harder to guess than \"abc\"": "Kordused on lihtsad ära arvata"
                            };
                            
                            // Check for specific patterns
                            if (password === '12345678' || password.match(/^[0-9]+$/)) {
                                message = "Ainult numbrid ei ole turvaline parool";
                            } else if (password.toLowerCase() === 'password' || password.toLowerCase() === 'parool') {
                                message = "See on liiga ilmselge parool";
                            } else if (warnings[result.feedback.warning]) {
                                message = warnings[result.feedback.warning];
                            } else if (result.feedback.warning) {
                                message = "Parool on liiga nõrk: " + result.feedback.warning;
                            }
                        }
                        
                        $userPasswordHelp.text(message)
                            .removeClass("text-success").addClass("text-danger");
                        $submitButton.prop("disabled", true);
                    } else {
                        // Strong enough password
                        const strengthText = result.score === 2 ? "Parool on piisavalt tugev" : 
                                            result.score === 3 ? "Parool on tugev" : 
                                            "Parool on väga tugev";
                        $userPasswordHelp.text(strengthText)
                            .removeClass("text-danger").addClass("text-success");
                        $submitButton.prop("disabled", false);
                    }
                } else {
                    // Fallback if zxcvbn not loaded - just check length
                    $userPasswordHelp.text("Parool sobib")
                        .removeClass("text-danger").addClass("text-success");
                    $submitButton.prop("disabled", false);
                }
            }
        } else {
            // For existing password, just check if not empty
            $submitButton.prop("disabled", !password);
        }
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

            // Check if this is a network error (offline)
            if (jqXHR.status === 0 && textStatus === 'error') {
                const networkError = 'Network connection lost. Please check your internet connection.';
                if (typeof error_callback === 'function') {
                    error_callback(networkError);
                } else {
                    show_error_modal(networkError, 'Network Error');
                }
                return;
            }

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
            let parsedResponse = (typeof response === 'object' && response !== null) ? response : tryToParseJSON(response);

            if (parsedResponse === false) {
                show_error_modal(response);
                return false;
            }

            if (parsedResponse.status.toString()[0] !== '2') {
                if (typeof error_callback === 'function') {
                    error_callback(parsedResponse);
                } else {
                    show_error_modal(parsedResponse.data);
                }
                return false;
            }

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

// OFFLINE MODE: Add offline status indicator
$(document).ready(function () {
    // Create offline status indicator
    function createOfflineIndicator() {
        if ($('#offline-indicator').length === 0) {
            $('body').prepend(`
                <div id="offline-indicator" class="alert alert-warning" style="display: none; position: fixed; top: 0; left: 0; right: 0; z-index: 9999; margin: 0; border-radius: 0; text-align: center;">
                    <i class="fas fa-wifi" style="opacity: 0.5;"></i>
                    Application is running in offline mode. Some features may be limited.
                </div>
            `);
        }
    }

    // Update offline status
    function updateOfflineStatus() {
        createOfflineIndicator();
        const isOffline = !navigator.onLine || (typeof OFFLINE_MODE !== 'undefined' && OFFLINE_MODE);

        if (isOffline) {
            $('#offline-indicator').show();
        } else {
            $('#offline-indicator').hide();
        }
    }

    // Listen for online/offline events
    window.addEventListener('online', updateOfflineStatus);
    window.addEventListener('offline', updateOfflineStatus);

    // Initial check
    updateOfflineStatus();

    // For development: Force offline mode if configured
    if (typeof OFFLINE_MODE !== 'undefined' && OFFLINE_MODE) {
        $('#offline-indicator').show();
    }
});
