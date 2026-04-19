async function parseJson(response) {
    const text = await response.text();
    const data = text ? JSON.parse(text) : null;

    if (!response.ok) {
        throw new Error(data?.error || text || 'Request failed');
    }

    return data;
}

async function api(path, options = {}) {
    const response = await fetch(path, {
        credentials: 'include',
        ...options,
        headers: {
            ...(options.body ? { 'Content-Type': 'application/json' } : {}),
            ...(options.headers || {}),
        },
    });

    return parseJson(response);
}

export function createLiveTimerSession(payload) {
    return api('/api/live-timer-sessions', {
        method: 'POST',
        body: JSON.stringify(payload),
    });
}

export function getLiveTimerSession(id) {
    return api(`/api/live-timer-sessions/${id}`);
}

export function deleteLiveTimerSession(id) {
    return api(`/api/live-timer-sessions/${id}`, {
        method: 'DELETE',
    });
}