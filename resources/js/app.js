import './bootstrap';

const loginForm = document.querySelector('#login-form');

if (loginForm instanceof HTMLFormElement) {
    const loginView = document.querySelector('[data-login-view]');
    const authenticatedView = document.querySelector('[data-authenticated-view]');
    const loginError = document.querySelector('#login-error');
    const logoutError = document.querySelector('#logout-error');
    const submitButton = document.querySelector('#login-submit');
    const logoutButton = document.querySelector('#logout-button');
    const tokenKey = 'laundrytrack.auth.token';

    const showError = (element, message) => {
        element.textContent = message;
        element.hidden = false;
    };

    const clearError = (element) => {
        element.textContent = '';
        element.hidden = true;
    };

    const showAuthenticated = (user) => {
        loginView.hidden = true;
        authenticatedView.hidden = false;
        authenticatedView.querySelector('[data-user-name]').textContent = user.name;
        authenticatedView.querySelector('[data-user-roles]').textContent = user.roles.join(' · ');
    };

    const readResponse = async (response) => {
        let payload;

        try {
            payload = await response.json();
        } catch {
            throw new Error('Server mengirim respons yang tidak dapat dibaca.');
        }

        if (!response.ok) {
            const validationMessage = Object.values(payload.errors ?? {})[0]?.[0];
            throw new Error(validationMessage ?? payload.message ?? 'Permintaan gagal. Coba lagi.');
        }

        return payload;
    };

    const loadSession = async () => {
        const token = sessionStorage.getItem(tokenKey);

        if (!token) {
            return;
        }

        try {
            const response = await fetch('/api/auth/me', {
                headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
            });

            if (response.status === 401) {
                sessionStorage.removeItem(tokenKey);
                return;
            }

            const payload = await readResponse(response);
            showAuthenticated(payload.user);
        } catch (error) {
            showError(loginError, error instanceof Error ? error.message : 'Sesi tidak dapat diperiksa.');
        }
    };

    loginForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearError(loginError);
        submitButton.disabled = true;
        submitButton.textContent = 'Memeriksa...';

        const formData = new FormData(loginForm);

        try {
            const response = await fetch('/api/auth/login', {
                method: 'POST',
                headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    username: formData.get('username'),
                    password: formData.get('password'),
                }),
            });
            const payload = await readResponse(response);
            sessionStorage.setItem(tokenKey, payload.token);
            showAuthenticated(payload.user);
        } catch (error) {
            showError(loginError, error instanceof Error ? error.message : 'Login gagal. Coba lagi.');
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = 'Masuk';
        }
    });

    logoutButton.addEventListener('click', async () => {
        clearError(logoutError);
        logoutButton.disabled = true;
        const token = sessionStorage.getItem(tokenKey);

        try {
            const response = await fetch('/api/auth/logout', {
                method: 'POST',
                headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
            });

            if (!response.ok && response.status !== 401) {
                await readResponse(response);
            }

            sessionStorage.removeItem(tokenKey);
            authenticatedView.hidden = true;
            loginView.hidden = false;
            loginForm.reset();
        } catch (error) {
            showError(logoutError, error instanceof Error ? error.message : 'Logout gagal. Coba lagi.');
        } finally {
            logoutButton.disabled = false;
        }
    });

    loadSession();
}
