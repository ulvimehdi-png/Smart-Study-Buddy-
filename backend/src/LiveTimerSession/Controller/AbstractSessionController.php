<?php

namespace App\LiveTimerSession\Controller;

use App\LiveTimerSession\Service\Projector;
use App\LiveTimerSession\Service\Publisher;
use App\LiveTimerSession\Service\State;
use App\LiveTimerSession\Service\Store;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

abstract class AbstractSessionController extends AbstractController
{
    protected function requireCurrentUserId(): int
    {
        $user = $this->getUser();

        if (!$user || !method_exists($user, 'getId') || !$user->getId()) {
            throw new AccessDeniedException('Authentication required');
        }

        return (int)$user->getId();
    }

    protected function requireOwnedState(
        string $id,
        Store  $store
    ): State
    {
        $state = $store->get($id);

        if (!$state) {
            throw $this->createNotFoundException('Live timer session not found');
        }

        if ((int)($state->ownerUserId ?? 0) !== $this->requireCurrentUserId()) {
            throw new AccessDeniedException('You do not own this live timer session');
        }

        return $state;
    }

    protected function jsonProjection(
        State      $state,
        Projector  $projector,
        ?Publisher $publisher = null,
        int        $status = Response::HTTP_OK,
    ): JsonResponse
    {
        $projection = $projector->project($state);

        $publisher?->publishProjection($projection);

        return $this->json($projection, $status);
    }

    protected function applyCommand(
        string    $id,
        Store     $store,
        callable  $operation,
        Projector $projector,
        Publisher $publisher,
    ): JsonResponse
    {
        $state = $this->requireOwnedState($id, $store);
        $state = $projector->resolve($state);
        $operation($state);
        $store->save($state);

        return $this->jsonProjection($state, $projector, $publisher);
    }
}
