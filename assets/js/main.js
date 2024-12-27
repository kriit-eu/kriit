const RELOAD = 33;


/**
 * Displays a Bootstrap 5.3 modal with the given title and body.
 * Assumes the HTML has an element #errorModal with child elements
 * #errorModalLabel (for title) and #errorModalBody (for the message).
 */
function showModal(title, body) {
    const error_modal = document.getElementById("error-modal");

    if(!body){
        body = '<i>Serveri vastust ei saanud tuvastada. Palun proovige uuesti.</i>';
    }

    error_modal.querySelector('.modal-body').innerHTML = body;

    if (title) {
        error_modal.querySelector('.modal-title').innerHTML = title;
    }

    const bootstrapModal = new bootstrap.Modal(error_modal);
    bootstrapModal.show();
}
  

/**
 * Sends a POST request to a PHP server expecting JSON response
 * with a "status" field.
 * @param {string}   url                 - Server endpoint (e.g. 'ajax.php').
 * @param {Object}   payload             - Data to send (will be JSON-stringified).
 * @param {Function|string} onSuccessOrRedirect
 *        If a function, it will be called on success with the parsed JSON.
 *        If a string, the page will be redirected to that URL on success.
 * @param {Function} [onError]           - Optional callback for errors.
 */
async function ajax(url, payload, onSuccessOrRedirect, onError) {
    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
            body: JSON.stringify(payload)
        });

        const text = await res.text();
        if (!text.trim()) {
            showModal('Error', 'Server returned an empty response');
            return;
        }

        let json;
        try {
            json = JSON.parse(text);
        } catch {
            showModal(res.ok ? 'Error' : `Error ${res.status}`, text);
            return;
        }

        const {status, data} = json;

        if (+status >= 200 && +status < 300) {
            if (typeof onSuccessOrRedirect === 'function') onSuccessOrRedirect(json);
            else if (onSuccessOrRedirect === RELOAD) location.reload();
            else if (typeof onSuccessOrRedirect === 'string') location.href = onSuccessOrRedirect;
        } else onError?.(json) || showModal(res.ok ? 'Error' : `Error ${res.status}`, data || 'Unknown error');

    } catch (err) {
        showModal('Error', `Network error: ${err.message}`);
        onError?.(err);
    }
}


// Clickable table rows functionality
document.querySelectorAll('table.clickable-rows tr').forEach(row => {
    row.addEventListener('click', () => {
        window.location = row.dataset.href;
    });
});
