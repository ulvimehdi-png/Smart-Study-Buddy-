function toNonNegativeInt(value, fallback = 0) {
    const number = Number(value);
    return Number.isFinite(number) ? Math.max(0, Math.trunc(number)) : fallback;
}

function parseIsoToMs(value) {
    if (!value) {
        return null;
    }

    const ms = new Date(value).getTime();
    return Number.isFinite(ms) ? ms : null;
}

function getPhaseDurationMs(phase, workMs, breakMs) {
    return phase === 'break' ? breakMs : workMs;
}

export function deriveView(projection, nowMs = Date.now()) {
    if (!projection?.state) {
        return null;
    }

    const state = projection.state;

    const status = typeof state.status === 'string' ? state.status : 'idle';
    let phase = state.phase === 'break' ? 'break' : 'focus';

    const workMs = toNonNegativeInt(state.workDurationMs);
    const breakMs = toNonNegativeInt(state.breakDurationMs);

    let remainingMs = toNonNegativeInt(
        state.remainingMs ?? getPhaseDurationMs(phase, workMs, breakMs)
    );

    const phaseEndsAtMs = parseIsoToMs(state.phaseEndsAt);

    if (status === 'running' && phaseEndsAtMs !== null) {
        if (nowMs < phaseEndsAtMs) {
            remainingMs = phaseEndsAtMs - nowMs;
        } else {
            let overflowMs = nowMs - phaseEndsAtMs;

            while (true) {
                phase = phase === 'focus' ? 'break' : 'focus';
                const durationMs = getPhaseDurationMs(phase, workMs, breakMs);

                if (durationMs <= 0) {
                    remainingMs = 0;
                    break;
                }

                if (overflowMs < durationMs) {
                    remainingMs = durationMs - overflowMs;
                    break;
                }

                overflowMs -= durationMs;
            }
        }
    }

    const phaseDurationMs = getPhaseDurationMs(phase, workMs, breakMs);
    const elapsedMs = Math.max(0, phaseDurationMs - remainingMs);
    const progress =
        phaseDurationMs > 0
            ? Math.min(1, Math.max(0, elapsedMs / phaseDurationMs))
            : 0;

    return {
        id: projection.id ?? null,
        topic: projection.topic ?? null,

        status,
        phase,

        workMs,
        breakMs,
        remainingMs: Math.max(0, remainingMs),
        phaseDurationMs,
        elapsedMs,
        progress,

        phaseEndsAt: state.phaseEndsAt ?? null,
        phaseEndsAtMs,

        startedAt: state.startedAt ?? null,
        updatedAt: state.updatedAt ?? null,

        accumulatedFocusMs: toNonNegativeInt(state.accumulatedFocusMs),
        accumulatedBreakMs: toNonNegativeInt(state.accumulatedBreakMs),

        subjectId: state.subjectId ?? null,
        taskId: state.taskId ?? null,

        isIdle: status === 'idle',
        isPaused: status === 'paused',
        isRunning: status === 'running',

        raw: projection,
    };
}