<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\TestCase;
use App\Controller\DashboardController;
use App\Controller\NotesController;
use App\Controller\TasksController;
use App\Controller\ReportController;
use App\Controller\AchievementController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Service\SubjectService;
use App\Service\AchievementService;
use Symfony\Component\DependencyInjection\Container;

class ControllerUnitTest extends TestCase
{
    public function testControllersReturn401WhenNotLoggedIn(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $request = new Request();
        $subjectService = $this->createMock(SubjectService::class);
        $achievementService = $this->createMock(AchievementService::class);
        
        // This blank container prevents the "$this->json()" crash
        $container = new Container();

        $dashboard = new DashboardController();
        $dashboard->setContainer($container);
        $this->assertSame(401, $dashboard->getDashboard(null, $em)->getStatusCode());

        $notes = new NotesController();
        $notes->setContainer($container);
        $this->assertSame(401, $notes->getNotes(null, $em)->getStatusCode());
        $this->assertSame(401, $notes->createNote($request, null, $em, $subjectService)->getStatusCode());
        $this->assertSame(401, $notes->deleteNote(1, null, $em)->getStatusCode());

        $tasks = new TasksController();
        $tasks->setContainer($container);
        $this->assertSame(401, $tasks->getTasks(null, $em)->getStatusCode());
        $this->assertSame(401, $tasks->createTask($request, null, $em, $subjectService)->getStatusCode());
        $this->assertSame(401, $tasks->toggleTask(1, null, $em, $achievementService)->getStatusCode());
        $this->assertSame(401, $tasks->deleteTask(1, null, $em)->getStatusCode());

        $reports = new ReportController();
        $reports->setContainer($container);
        $this->assertSame(401, $reports->getReports($request, null, $em)->getStatusCode());

        $achievements = new AchievementController();
        $achievements->setContainer($container);
        $this->assertSame(401, $achievements->getMyAchievements(null, $em)->getStatusCode());
    }
}