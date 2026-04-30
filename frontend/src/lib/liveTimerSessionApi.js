async function parseResponse(response) {
    const text = await response.text();

    let data = null;

    if (text) {
        try {
            data = JSON.parse(text);
        } catch {
            data = text;
        }
    }

    if (!response.ok) {
        const message =
            typeof data === 'object' && data !== null
                ? data.error || data.message || JSON.stringify(data)
                : data || `Request failed with status ${response.status}`;

        throw new Error(message);
    }

    return data;
}

async function api(path, { method = 'GET', body, headers, ...rest } = {}) {
    const response = await fetch(path, {
        method,
        credentials: 'include',
        ...rest,
        headers: {
            Accept: 'application/json',
            ...(body !== undefined ? { 'Content-Type': 'application/json' } : {}),
            ...(headers || {}),
        },
        body: body === undefined ? undefined : JSON.stringify(body),
    });

    return parseResponse(response);
}

function postAction(id, action, payload = {}) {
    return api(`/api/live-timer-sessions/${id}/${action}`, {
        method: 'POST',
        body: payload,
    });
}

export function durationPayload(minutes = 0, seconds = 0) {
    return {
        minutes: Math.max(0, Number(minutes) || 0),
        seconds: Math.max(0, Number(seconds) || 0),
    };
}

export function createLiveTimerSession(payload) {
    return api('/api/live-timer-sessions', {
        method: 'POST',
        body: payload,
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

export function startLiveTimerSession(id) {
    return postAction(id, 'start');
}

export function pauseLiveTimerSession(id) {
    return postAction(id, 'pause');
}

export function skipPhaseLiveTimerSession(id) {
    return postAction(id, 'skip-phase');
}

export function resetPhaseLiveTimerSession(id) {
    return postAction(id, 'reset-phase');
}

export function changeConfigLiveTimerSession(id, payload) {
    return postAction(id, 'change-config', payload);
}

export function buildLiveTimerJoinUrl(id, currentUrl = window.location.href) {
    const url = new URL(currentUrl);
    url.searchParams.set('session', id);
    return url.toString();
}
