<?php

namespace App\Controller;

use App\Repository\NoteRepository;
use App\Repository\SessionEntryRepository;
use App\Repository\SubjectRepository;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;


#[Route('/api/dev')]
final class DevController extends AbstractController
{
    #[Route('/status', methods: ['GET'])]
    public function getStatus(
        Connection             $connection,
        UserRepository         $users,
        SubjectRepository      $subjects,
        TaskRepository         $tasks,
        NoteRepository         $notes,
        SessionEntryRepository $sessions,
        HttpClientInterface    $httpClient,
    ): JsonResponse
    {
        $dbOk = true;

        try {
            $connection->executeQuery('SELECT 1')->fetchOne();
        } catch (\Throwable $exception) {
            $dbOk = false;
        }

        $user = $this->getUser();
        $projectDir = $this->getParameter('kernel.project_dir');

        $latestUsers = $users->createQueryBuilder('u')
            ->select('u.id', 'u.username', 'u.roles')
            ->orderBy('u.id', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getArrayResult();

        $coverageIndex = $projectDir . '/public/uploads/coverage/html/index.html';

        return $this->json([
            'environment' => $_ENV['APP_ENV'] ?? 'unknown',
            'debug' => ($_ENV['APP_DEBUG'] ?? '0') === '1',

            'currentUser' => [
                'username' => method_exists($user, 'getUserIdentifier') ? $user->getUserIdentifier() : null,
                'roles' => method_exists($user, 'getRoles') ? $user->getRoles() : [],
            ],

            'database' => [
                'reachable' => $dbOk,
            ],

            'mercure' => $this->checkMercure($httpClient),

            'storage' => [
                'uploads' => $this->isWritableDirectory($projectDir . '/public/uploads'),
                'avatars' => $this->isWritableDirectory($projectDir . '/public/uploads/avatar'),
            ],

            'coverage' => [
                'available' => is_file($coverageIndex),
                'url' => '/uploads/coverage/html/index.html',
            ],

            'stats' => [
                'users' => $users->count([]),
                'subjects' => $subjects->count([]),
                'tasks' => $tasks->count([]),
                'notes' => $notes->count([]),
                'studySessions' => $sessions->count([]),
            ],

            'latestUsers' => $latestUsers,

            'serverTime' => (new \DateTimeImmutable())->format(DATE_ATOM),
        ]);
    }

    #[Route('/logs', name: 'api_dev_logs', methods: ['GET'])]
    public function logs(Request $request): JsonResponse
    {
        $env = $_ENV['APP_ENV'] ?? 'prod';
        $logFile = $this->getParameter('kernel.logs_dir') . '/' . $env . '.log';
        $errorsOnly = $request->query->getBoolean('errorsOnly', false);

        if (!is_file($logFile)) {
            return $this->json([
                'file' => basename($logFile),
                'exists' => false,
                'errorsOnly' => $errorsOnly,
                'lines' => [],
            ]);
        }

        $lines = $this->tailFile($logFile, 500);

        if ($errorsOnly) {
            $lines = array_values(array_filter($lines, static function (string $line): bool {
                return preg_match('/\b(error|critical|alert|emergency|exception|fatal)\b/i', $line) === 1;
            }));

            $lines = array_slice($lines, -100);
        } else {
            $lines = array_slice($lines, -200);
        }

        return $this->json([
            'file' => basename($logFile),
            'exists' => true,
            'errorsOnly' => $errorsOnly,
            'lineCount' => count($lines),
            'lines' => array_map([$this, 'redactSecrets'], $lines),
        ]);
    }

    private function tailFile(string $file, int $maxLines): array
    {
        $lines = @file($file, FILE_IGNORE_NEW_LINES);

        if ($lines === false) {
            return [];
        }

        return array_slice($lines, -$maxLines);
    }

    private function redactSecrets(string $line): string
    {
        $patterns = [
            '/(JWT_SECRET|MERCURE_JWT_SECRET|APP_SECRET|DATABASE_URL|PASSWORD|TOKEN|SECRET)=([^ ]+)/i',
            '/Bearer\s+[A-Za-z0-9._\-]+/i',
        ];

        return preg_replace($patterns, '$1=[REDACTED]', $line) ?? $line;
    }

    private function isWritableDirectory(string $path): array
    {
        return [
            'path' => $path,
            'exists' => is_dir($path),
            'writable' => is_dir($path) && is_writable($path),
        ];
    }

    private function checkMercure(HttpClientInterface $httpClient): array
    {
        $url = $_ENV['MERCURE_PUBLIC_URL'] ?? $_ENV['MERCURE_URL'] ?? null;

        if (!$url) {
            return [
                'configured' => false,
                'reachable' => false,
                'url' => null,
            ];
        }

        $healthUrl = rtrim(str_replace('/.well-known/mercure', '', $url), '/') . '/healthz';

        try {
            $response = $httpClient->request('GET', $healthUrl, [
                'timeout' => 2,
            ]);

            return [
                'configured' => true,
                'reachable' => $response->getStatusCode() >= 200 && $response->getStatusCode() < 400,
                'url' => $healthUrl,
                'statusCode' => $response->getStatusCode(),
            ];
        } catch (\Throwable $error) {
            return [
                'configured' => true,
                'reachable' => false,
                'url' => $healthUrl,
                'error' => $error->getMessage(),
            ];
        }
    }
}
