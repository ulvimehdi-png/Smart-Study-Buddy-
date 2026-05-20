<?php

namespace App\Tests\LiveTimerSession\Service;

use App\LiveTimerSession\Service\Projector;
use App\LiveTimerSession\Service\Publisher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

final class PublisherTest extends TestCase
{
    public function testTopicForIdDelegatesToProjectorTopic(): void
    {
        self::assertSame(
            Projector::topicForId('timer1'),
            Publisher::topicForId('timer1')
        );
    }

    public function testPublishProjectionPublishesMercureUpdate(): void
    {
        $projection = [
            'id' => 'timer1',
            'topic' => Projector::topicForId('timer1'),
            'state' => [
                'id' => 'timer1',
                'status' => 'running',
            ],
        ];

        $hub = $this->createMock(HubInterface::class);
        $hub
            ->expects(self::once())
            ->method('publish')
            ->with(self::callback(function (Update $update) use ($projection): bool {
                if (method_exists($update, 'getData')) {
                    self::assertSame(
                        json_encode($projection, JSON_THROW_ON_ERROR),
                        $update->getData()
                    );
                }

                return true;
            }))
            ->willReturn('mercure-update-id');

        $publisher = new Publisher($hub);

        $publisher->publishProjection($projection);
    }

    public function testPublishDeletedPublishesDeletedProjection(): void
    {
        $hub = $this->createMock(HubInterface::class);
        $hub
            ->expects(self::once())
            ->method('publish')
            ->with(self::callback(function (Update $update): bool {
                if (method_exists($update, 'getData')) {
                    $data = json_decode($update->getData(), true, 512, JSON_THROW_ON_ERROR);

                    self::assertSame('timer1', $data['id']);
                    self::assertSame(Projector::topicForId('timer1'), $data['topic']);
                    self::assertTrue($data['deleted']);
                    self::assertNull($data['state']);
                }

                return true;
            }))
            ->willReturn('mercure-update-id');

        $publisher = new Publisher($hub);

        $publisher->publishDeleted('timer1');
    }
}
