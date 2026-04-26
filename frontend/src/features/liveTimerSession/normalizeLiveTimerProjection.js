export function normalizeLiveTimerProjection(projection, nowMs = Date.now()) {
    if (!projection?.state) {
        return null;
    }

    const state = projection.state;

    const workMs = Number(state.workDurationMs ?? 0);
    const breakMs = Number(state.breakDurationMs ?? 0);

    let phase = state.phase ?? 'focus';
    const status = state.status ?? 'idle';

    let displayMsLeft = Number(
        state.remainingMs ??
        (phase === 'break' ? breakMs : workMs)
    );

    if (status === 'running' && state.phaseEndsAt) {
        const endAt = new Date(state.phaseEndsAt).getTime();

        if (nowMs < endAt) {
            displayMsLeft = endAt - nowMs;
        } else {
            let overflowMs = nowMs - endAt;

            while (true) {
                phase = phase === 'focus' ? 'break' : 'focus';
                const durationMs = phase === 'break' ? breakMs : workMs;

                if (overflowMs < durationMs) {
                    displayMsLeft = durationMs - overflowMs;
                    break;
                }

                overflowMs -= durationMs;
            }
        }
    }

    return {
        id: projection.id,
        topic: projection.topic,
        status,
        phase,
        workMs,
        breakMs,
        displayMsLeft: Math.max(0, displayMsLeft),
        raw: projection,
    };
}