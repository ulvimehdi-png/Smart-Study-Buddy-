<?php

namespace App\LiveTimerSession\Service;

use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

final class Publisher
{
    public function __construct(
        private HubInterface $hub
    ){}

    public static function topicForId(string $id): string
    {
        return Projector::topicForId($id);
    }

    public function publishProjection(array $projection): void
    {
        $update = new Update(
            $projection['topic'],
            json_encode($projection, JSON_THROW_ON_ERROR)
        );

        $this->hub->publish($update);
    }

    public function publishDeleted(string $id): void
    {
        $projection = [
            'id' => $id,
            'topic' => self::topicForId($id),
            'deleted' => true,
            'state' => null,
        ];

        $update = new Update(
            $projection['topic'],
            json_encode($projection, JSON_THROW_ON_ERROR)
        );

        $this->hub->publish($update);
    }
}
