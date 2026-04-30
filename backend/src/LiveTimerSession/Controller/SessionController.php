<?php

namespace App\LiveTimerSession\Controller;

use App\LiveTimerSession\DTO\Command\CreateSession;
use App\LiveTimerSession\Enum\SessionPhase;
use App\LiveTimerSession\Enum\SessionStatus;
use App\LiveTimerSession\Service\Projector;
use App\LiveTimerSession\Service\Publisher;
use App\LiveTimerSession\Service\State;
use App\LiveTimerSession\Service\Store;
use App\LiveTimerSession\Service\TimeService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/public/api/live-timer-sessions')]
final class SessionController extends AbstractSessionController
{
    #[Route('', methods: ['POST'], format: 'json')]
    public function create(
        #[MapRequestPayload] CreateSession $request,
        Store                              $store,
        Projector                          $projector,
        Publisher                          $publisher,
        TimeService                        $time
    ): JsonResponse
    {
        /*
        $state = $store->create([
            'ownerUserId' => $this->requireCurrentUserId(),
            'work' => [
                'minutes' => $request->work->minutes,
                'seconds' => $request->work->seconds,
            ],
            'break' => [
                'minutes' => $request->break->minutes,
                'seconds' => $request->break->seconds,
            ],
            'subjectId' => $request->subjectId,
            'taskId' => $request->taskId,
        ]);
        */

        $id = bin2hex(random_bytes(16));

        $state = State::create(
            id: $id,
            ownerUserId: $this->requireCurrentUserId(),
            workDurationMs: $time->durationToMs([
                'minutes' => $request->work->minutes,
                'seconds' => $request->work->seconds,
            ]),
            breakDurationMs: $time->durationToMs([
                'minutes' => $request->break->minutes,
                'seconds' => $request->break->seconds,
            ]),
            time: $time,
            phase: SessionPhase::from($request->phase),
            status: SessionStatus::from($request->status),
            remainingMs: $request->remainingMs,
            subjectId: $request->subjectId,
            taskId: $request->taskId,
        );

        $store->save($state);

        return $this->jsonProjection($state, $projector, $publisher, Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function getOne(
        string    $id,
        Store     $store,
        Projector $projector,
    ): JsonResponse
    {
        $state = $this->requireState($id, $store);

        return $this->jsonProjection($state, $projector);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(
        string    $id,
        Store     $store,
        Publisher $publisher,
    ): JsonResponse
    {
        $this->requireOwnedState($id, $store);

        $publisher->publishDeleted($id);
        $store->delete($id);

        return $this->json(['ok' => true]);
    }
}
