export async function apiRequest(url, options = {}) {
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    const response = await fetch(url, {
        ...options,
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            ...options.headers,
        },
    });

    const payload = await response.json().catch(() => ({}));

    if (!response.ok) {
        const error = new Error(payload.message || 'Telemetry request failed.');
        error.status = response.status;
        error.payload = payload;
        throw error;
    }

    return payload;
}
