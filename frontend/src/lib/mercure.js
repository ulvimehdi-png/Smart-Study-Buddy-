const DEFAULT_HUB_URL = import.meta.env.VITE_MERCURE_PUBLIC_URL;

function normalizeTopics(topics) {
    const list = Array.isArray(topics) ? topics : [topics];

    return [...new Set(
        list
            .filter((topic) => typeof topic === 'string')
            .map((topic) => topic.trim())
            .filter(Boolean)
    )];
}

export function buildMercureUrl(
    topics,
    { hubUrl = DEFAULT_HUB_URL, origin = window.location.origin } = {}
) {
    if (!hubUrl) {
        throw new Error('Missing VITE_MERCURE_PUBLIC_URL');
    }

    const normalizedTopics = normalizeTopics(topics);

    if (normalizedTopics.length === 0) {
        throw new Error('At least one Mercure topic is required');
    }

    const url = new URL(hubUrl, origin);

    for (const topic of normalizedTopics) {
        url.searchParams.append('topic', topic);
    }

    return url.toString();
}

export function subscribeMercure({
                                     topics,
                                     hubUrl = DEFAULT_HUB_URL,
                                     withCredentials = false,
                                     onOpen,
                                     onMessage,
                                     onError,
                                 }) {
    const url = buildMercureUrl(topics, { hubUrl });
    const eventSource = new EventSource(url, { withCredentials });

    eventSource.onopen = (event) => {
        onOpen?.(event);
    };

    eventSource.onmessage = (event) => {
        onMessage?.(event.data, event);
    };

    eventSource.onerror = (event) => {
        onError?.(event);
    };

    return {
        url,
        eventSource,
        close() {
            eventSource.close();
        },
    };
}

export function subscribeMercureJson({
                                         topics,
                                         hubUrl = DEFAULT_HUB_URL,
                                         withCredentials = false,
                                         onOpen,
                                         onMessage,
                                         onError,
                                         onParseError,
                                     }) {
    return subscribeMercure({
        topics,
        hubUrl,
        withCredentials,
        onOpen,
        onError,
        onMessage: (raw, event) => {
            try {
                const data = JSON.parse(raw);
                onMessage?.(data, event);
            } catch (error) {
                onParseError?.(error, raw, event);
            }
        },
    });
}