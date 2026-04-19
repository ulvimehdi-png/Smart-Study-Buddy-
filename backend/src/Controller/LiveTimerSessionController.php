<?php

namespace App\Controller;

use App\Cache\LiveTimerSessionStore;
use App\Dto\LiveTimer\CreateLiveTimerSessionRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/live-timer-sessions')]
final class LiveTimerSessionController extends AbstractController
{
    #[Route('', methods: ['POST'], format: 'json')]
    public function create(
        #[MapRequestPayload] CreateLiveTimerSessionRequest $request,
        LiveTimerSessionStore $store,
    ): JsonResponse {
        $state = $store->create([
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

        return $this->json($this->projection($state), Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function getOne(string $id, LiveTimerSessionStore $store): JsonResponse
    {
        $state = $store->get($id);

        if (!$state) {
            return $this->json(['error' => 'Live timer session not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($this->projection($state));
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(string $id, LiveTimerSessionStore $store): JsonResponse
    {
        $store->delete($id);

        return $this->json(['ok' => true]);
    }

    private function projection(array $state): array
    {
        return [
            'id' => $state['id'],
            'topic' => sprintf('http://localhost/live-timer-sessions/%s', $state['id']),
            'state' => $state,
        ];
    }
}
