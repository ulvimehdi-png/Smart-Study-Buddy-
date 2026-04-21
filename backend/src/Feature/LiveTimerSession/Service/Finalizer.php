<?php

namespace App\Feature\LiveTimerSession\Service;

use App\Feature\LiveTimerSession\Entity\Session;
use App\Feature\LiveTimerSession\Entity\SessionEntry;
use App\Feature\LiveTimerSession\Enum\SessionStatus;
use App\Feature\LiveTimerSession\Enum\SessionType;
use App\Feature\Subject\Entity\Subject;
use App\Feature\Task\Entity\Task;
use App\Feature\User\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

final class Finalizer
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly Projector              $projector,
        private readonly TimeService            $time,
    )
    {
    }

    public function finalize(
        State         $rawState,
        User          $user,
        SessionStatus $status,
        array         $options = []
    ): Session
    {
        $resolved = $this->projector->resolve($rawState);

        $sessionNote = $options['sessionNote'] ?? null;
        $distractionCount = (int)($options['distractionCount'] ?? 0);

        [$focusMs, $breakMs] = $resolved->getTotalDurations($this->time);

        $startedAt = $resolved->startedAt
            ? new DateTimeImmutable($resolved->startedAt)
            : new DateTimeImmutable($resolved->updatedAt);

        $endedAt = new DateTimeImmutable();

        $session = new Session();
        $session->setUser($user);
        $session->setStatus($status);
        $session->setSessionType(SessionType::POMODORO);
        $session->setStartedAt($startedAt);
        $session->setEndedAt($endedAt);
        $session->setPlannedMinutes((int)floor($resolved->workDurationMs / 60000));
        $session->setActualFocusMinutes((int)floor($focusMs / 60000));
        $session->setBreakMinutes((int)floor($breakMs / 60000));
        $session->setDistractionCount($distractionCount);
        $session->setSessionNote($sessionNote);
        $session->setCreatedAt($endedAt);
        $session->setUpdatedAt($endedAt);

        $this->em->persist($session);

        $subject = $resolved->subjectId
            ? $this->em->getRepository(Subject::class)->find($resolved->subjectId)
            : null;

        $task = $resolved->taskId
            ? $this->em->getRepository(Task::class)->find($resolved->taskId)
            : null;

        if ($subject || $task) {
            $entry = new SessionEntry();
            $entry->setSession($session);
            $entry->setPlannedMinutes((int)floor($resolved->workDurationMs / 60000));
            $entry->setActualFocusMinutes((int)floor($focusMs / 60000));
            $entry->setEntryStartedAt($startedAt);
            $entry->setEntryEndedAt($endedAt);
            $entry->setCreatedAt($endedAt);
            $entry->setUpdatedAt($endedAt);

            if ($subject) {
                $entry->setSubject($subject);
                $entry->setSubjectNameSnapshot($subject->getName());
            }

            if ($task) {
                $entry->setTask($task);
                $entry->setTaskTitleSnapshot($task->getTitle());
            }

            $this->em->persist($entry);
        }

        $this->em->flush();

        return $session;
    }
}
