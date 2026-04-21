<?php

namespace App\Feature\LiveTimerSession\Controller;

use App\Feature\LiveTimerSession\Dto\Command\ChangeConfig;
use App\Feature\LiveTimerSession\Dto\Command\EmptyCommand;
use App\Feature\LiveTimerSession\Dto\Command\StopSession;
use App\Feature\LiveTimerSession\Enum\SessionStatus;
use App\Feature\LiveTimerSession\Service\Finalizer;
use App\Feature\LiveTimerSession\Service\Projector;
use App\Feature\LiveTimerSession\Service\Publisher;
use App\Feature\LiveTimerSession\Service\Store;
use App\Feature\LiveTimerSession\Service\TimeService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/live-timer-sessions/{id}')]
final class SessionActionController extends AbstractSessionController
{
    /** @noinspection PhpUnusedParameterInspection */
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

    /** @noinspection PhpUnusedParameterInspection */
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

    /** @noinspection PhpUnusedParameterInspection */
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

    /** @noinspection PhpUnusedParameterInspection */
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

    /** @noinspection PhpUnusedParameterInspection */
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

    /** @noinspection PhpUnusedParameterInspection */
    #[Route('/stop-and-save', methods: ['POST'], format: 'json')]
    public function stopAndSave(
        string                           $id,
        #[MapRequestPayload] StopSession $request,
        Store                            $store,
        Publisher                        $publisher,
        Finalizer                        $finalizer,
        TimeService                      $time,
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
