<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/public/api/dev/mercure')]
final class DevMercureController extends AbstractController
{
    #[Route('/ping', methods: ['POST'])]
    public function ping(HubInterface $hub): JsonResponse
    {
        $topic = 'http://localhost/timer/test';
        $payload = [
            'message' => 'Hello from symfony DevMercureController',
            'sentAt' => (new \DateTimeImmutable()->format(DATE_ATOM))
        ];

        $update = new Update(
            $topic,
            json_encode($payload, JSON_THROW_ON_ERROR)
        );

        $hub->publish($update);

        return $this->json([
            'ok' => true,
            'topic' => $topic,
            'payload' => $payload,
        ]);
    }
}
