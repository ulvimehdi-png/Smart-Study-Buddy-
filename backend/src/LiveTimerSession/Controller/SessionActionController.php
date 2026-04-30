<?php

namespace App\LiveTimerSession\Controller;

use App\LiveTimerSession\DTO\Command\ChangeConfig;
use App\LiveTimerSession\DTO\Command\EmptyCommand;
use App\LiveTimerSession\DTO\Command\StopSession;
use App\LiveTimerSession\Enum\SessionStatus;
use App\LiveTimerSession\Service\Finalizer;
use App\LiveTimerSession\Service\Manager;
use App\LiveTimerSession\Service\Projector;
use App\LiveTimerSession\Service\Publisher;
use App\LiveTimerSession\Service\Store;
use App\LiveTimerSession\Service\TimeService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/public/api/live-timer-sessions/{id}')]
final class SessionActionController extends AbstractSessionController
{
    #[Route('/start', methods: ['POST'], format: 'json')]
    public function start(
        string                            $id,
        #[MapRequestPayload] EmptyCommand $request,
        Store                             $store,
        Publisher                         $publisher,
        Projector                         $projector,
        TimeService                       $time,
    ): JsonResponse
    {
        return $this->applyCommand(
            $id,
            $store,
            fn($state) => $state->start($time),
            $projector,
            $publisher
        );
    }

    #[Route('/pause', methods: ['POST'], format: 'json')]
    public function pause(
        string                            $id,
        #[MapRequestPayload] EmptyCommand $request,
        Store                             $store,
        Publisher                         $publisher,
        Projector                         $projector,
        TimeService                       $time,
    ): JsonResponse
    {
        return $this->applyCommand(
            $id,
            $store,
            fn($state) => $state->pause($time),
            $projector,
            $publisher
        );
    }

    #[Route('/resume', methods: ['POST'], format: 'json')]
    public function resume(
        string                            $id,
        #[MapRequestPayload] EmptyCommand $request,
        Store                             $store,
        Publisher                         $publisher,
        Projector                         $projector,
        TimeService                       $time,
    ): JsonResponse
    {
        return $this->applyCommand(
            $id,
            $store,
            fn($state) => $state->resume($time),
            $projector,
            $publisher
        );
    }

    #[Route('/skip-phase', methods: ['POST'], format: 'json')]
    public function skipPhase(
        string                            $id,
        #[MapRequestPayload] EmptyCommand $request,
        Store                             $store,
        Publisher                         $publisher,
        Projector                         $projector,
        TimeService                       $time,
    ): JsonResponse
    {
        return $this->applyCommand(
            $id,
            $store,
            fn($state) => $state->skipPhase($time),
            $projector,
            $publisher
        );
    }

    #[Route('/reset-phase', methods: ['POST'], format: 'json')]
    public function resetPhase(
        string                            $id,
        #[MapRequestPayload] EmptyCommand $request,
        Store                             $store,
        Publisher                         $publisher,
        Projector                         $projector,
        TimeService                       $time,
    ): JsonResponse
    {
        return $this->applyCommand(
            $id,
            $store,
            fn($state) => $state->resetPhase($time),
            $projector,
            $publisher
        );
    }

    #[Route('/change-config', methods: ['POST'], format: 'json')]
    public function changeConfig(
        string                            $id,
        #[MapRequestPayload] ChangeConfig $request,
        Store                             $store,
        Publisher                         $publisher,
        Projector                         $projector,
        TimeService                       $time,
    ): JsonResponse
    {
        return $this->applyCommand(
            $id,
            $store,
            fn($state) => $state->changeConfig($time, $request->work, $request->break),
            $projector,
            $publisher
        );
    }

    #[Route('/stop-and-save', methods: ['POST'], format: 'json')]
    public function stopAndSave(
        string                            $id,
        #[MapRequestPayload] StopSession $request,
        Store                             $store,
        Publisher                         $publisher,
        Finalizer                         $finalizer,
        TimeService                       $time,
    ): JsonResponse
    {
        $state = $this->requireOwnedState($id, $store);

        $session = $finalizer->finalize(
            $state,
            $this->getUser(),
            SessionStatus::from($request->finalStatus),
            [
                'sessionNote' => $request->sessionNote,
                'distractionCount' => $request->distractionCount,
            ]
        );

        $publisher->publishDeleted($id);
        $store->delete($id);

        return $this->json([
            'ok' => true,
            'savedSessionId' => $session->getId(),
        ]);
    }
}
