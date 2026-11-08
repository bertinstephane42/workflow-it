/**
 * Connexion asynchrone en deux étapes.
 *
 * - la vérification des identifiants ne crée aucune session authentifiée ;
 * - une modale de confirmation permet de rejoindre la simulation ou d'annuler ;
 * - les erreurs restent sur la page, le champ de mot de passe est effacé et le
 *   message disparaît automatiquement après cinq secondes.
 */
(function () {
    'use strict';

    const form = document.getElementById('login-form');
    if (!form) {
        return;
    }

    const errorBox = document.getElementById('login-error');
    const loginInput = document.getElementById('login');
    const dobInput = document.getElementById('dob');
    const loginButton = document.getElementById('login-submit');
    const modal = document.getElementById('login-confirm-modal');
    const modalForm = document.getElementById('login-confirm-form');
    const pendingInput = document.getElementById('login-pending');
    const confirmAccount = document.getElementById('login-confirm-account');
    const confirmButton = document.getElementById('login-confirm-submit');
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrf = (csrfMeta && csrfMeta.getAttribute('content'))
        || (form.elements.csrf ? form.elements.csrf.value : '');

    let pendingToken = pendingInput ? pendingInput.value : '';
    let busy = false;
    let errorTimer = null;
    let hideTimer = null;

    function setLoginBusy(active) {
        busy = active;
        form.setAttribute('aria-busy', active ? 'true' : 'false');
        loginButton.disabled = active;
        loginButton.textContent = active ? 'Connexion…' : 'Se connecter';
    }

    function setConfirmBusy(active) {
        busy = active;
        confirmButton.disabled = active;
        confirmButton.textContent = active ? 'Connexion…' : 'Rejoindre la partie';
    }

    function clearError() {
        if (!errorBox) {
            return;
        }
        window.clearTimeout(errorTimer);
        window.clearTimeout(hideTimer);
        errorBox.classList.remove('is-hiding');
        errorBox.hidden = true;
        errorBox.textContent = '';
    }

    function scheduleHideError() {
        if (!errorBox || errorBox.hidden) {
            return;
        }
        window.clearTimeout(errorTimer);
        window.clearTimeout(hideTimer);
        errorTimer = window.setTimeout(function () {
            errorBox.classList.add('is-hiding');
            hideTimer = window.setTimeout(function () {
                errorBox.hidden = true;
                errorBox.classList.remove('is-hiding');
            }, 250);
        }, 5000);
    }

    function showError(message) {
        if (!errorBox) {
            return;
        }
        window.clearTimeout(errorTimer);
        window.clearTimeout(hideTimer);
        errorBox.classList.remove('is-hiding');
        errorBox.textContent = message;
        errorBox.hidden = false;
        scheduleHideError();
    }

    function hideModal() {
        if (modal) {
            modal.hidden = true;
        }
        pendingToken = '';
        if (pendingInput) {
            pendingInput.value = '';
        }
    }

    function showModal(data) {
        if (!modal || !pendingInput) {
            return;
        }
        pendingToken = typeof data.pending === 'string' ? data.pending : '';
        pendingInput.value = pendingToken;
        if (confirmAccount) {
            confirmAccount.textContent = typeof data.login === 'string' ? data.login : '';
        }
        modal.hidden = false;
        setLoginBusy(false);
        setConfirmBusy(false);
        confirmButton.focus();
    }

    async function callApi(action, payload) {
        let response;
        try {
            response = await fetch('api.php?action=' + encodeURIComponent(action), {
                method: 'POST',
                credentials: 'same-origin',
                cache: 'no-store',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrf
                },
                body: JSON.stringify(Object.assign({ csrf: csrf }, payload || {}))
            });
        } catch (exception) {
            throw new Error('Connexion impossible. Vérifiez votre réseau et réessayez.');
        }

        let data = null;
        try {
            data = await response.json();
        } catch (exception) {
            data = null;
        }

        if (!response.ok || !data || data.ok !== true) {
            const message = data && typeof data.error === 'string'
                ? data.error
                : 'Connexion impossible. Vérifiez votre réseau et réessayez.';
            throw new Error(message);
        }
        return data;
    }

    async function cancelPending() {
        if (busy) {
            return;
        }
        setConfirmBusy(true);
        try {
            if (pendingToken) {
                await callApi('login_cancel', { pending: pendingToken });
            }
        } catch (exception) {
            // L'annulation distante peut échouer (réseau) : la connexion en
            // attente n'accorde toujours aucun accès et expirera côté serveur.
        } finally {
            hideModal();
            setConfirmBusy(false);
            if (loginInput) {
                loginInput.focus();
            }
        }
    }

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        if (busy) {
            return;
        }

        clearError();
        const login = loginInput ? loginInput.value.trim() : '';
        const dob = dobInput ? dobInput.value.trim() : '';

        if (login === '' || dob === '') {
            showError('Renseignez votre identifiant et votre mot de passe.');
            if (login === '' && loginInput) {
                loginInput.focus();
            } else if (dobInput) {
                dobInput.focus();
            }
            return;
        }

        setLoginBusy(true);
        try {
            const data = await callApi('login', { login: login, dob: dob });
            showModal(data);
        } catch (exception) {
            setLoginBusy(false);
            if (dobInput) {
                dobInput.value = '';
            }
            if (loginInput) {
                loginInput.value = login;
            }
            showError(exception.message);
            if (dobInput) {
                dobInput.focus();
            }
        }
    });

    if (modalForm) {
        modalForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            if (busy) {
                return;
            }

            const action = event.submitter && event.submitter.value
                ? event.submitter.value
                : 'login_confirm';

            if (action === 'login_cancel') {
                await cancelPending();
                return;
            }

            if (!pendingToken) {
                hideModal();
                showError('La confirmation a expiré. Recommencez la connexion.');
                return;
            }

            setConfirmBusy(true);
            try {
                await callApi('login_confirm', { pending: pendingToken });
                window.location.assign('workflow.php');
            } catch (exception) {
                hideModal();
                setConfirmBusy(false);
                if (dobInput) {
                    dobInput.value = '';
                }
                showError(exception.message);
                if (loginInput) {
                    loginInput.focus();
                }
            }
        });
    }

    if (modal) {
        modal.querySelectorAll('[data-login-cancel]').forEach(function (element) {
            element.addEventListener('click', function (event) {
                event.preventDefault();
                cancelPending();
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !modal.hidden) {
                event.preventDefault();
                cancelPending();
            }
        });
    }

    if (errorBox && !errorBox.hidden) {
        scheduleHideError();
    }
    if (modal && !modal.hidden && confirmButton) {
        confirmButton.focus();
    }
})();
