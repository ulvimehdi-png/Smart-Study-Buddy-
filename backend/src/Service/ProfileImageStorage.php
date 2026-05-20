<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

final class ProfileImageStorage
{
    private const AVATAR_DIR = 'avatars';
    private const MAX_AVATAR_SIZE = 512;
    private const WEBP_QUALITY = 82;

    public function __construct(
        #[Autowire('%app.upload_dir%')]
        private readonly string           $uploadRootDir,

        #[Autowire('%app.upload_public_prefix%')]
        private readonly string           $publicPrefix,

        private readonly SluggerInterface $slugger,
    )
    {
    }

    public function publicUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        return rtrim($this->publicPrefix, '/') . '/' . ltrim($path, '/');
    }

    public function store(User $user, UploadedFile $file): string
    {
        $avatarDir = $this->avatarDir();

        if (
            !is_dir($avatarDir)
            && !mkdir($avatarDir, 0755, true)
            && !is_dir($avatarDir)
        ) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created.', $avatarDir));
        }

        $this->remove($user->getProfileImagePath());

        $baseName = $this->slugger
            ->slug($user->getUsername() ?: 'user')
            ->lower()
            ->toString();

        $webpFileName = sprintf('%s-%s.webp', $baseName, bin2hex(random_bytes(8)));
        $webpRelativePath = self::AVATAR_DIR . '/' . $webpFileName;
        $webpAbsolutePath = $this->absolutePath($webpRelativePath);

        if ($this->canResizeWithGd() && $this->storeResizedWebp($file, $webpAbsolutePath)) {
            return $webpRelativePath;
        }

        $extension = $file->guessExtension()
            ?: $file->getClientOriginalExtension()
                ?: 'bin';

        $fallbackFileName = sprintf(
            '%s-%s.%s',
            $baseName,
            bin2hex(random_bytes(8)),
            strtolower($extension)
        );

        $file->move($avatarDir, $fallbackFileName);

        return self::AVATAR_DIR . '/' . $fallbackFileName;
    }

    public function remove(?string $path): void
    {
        if (!$path) {
            return;
        }

        $absolutePath = $this->absolutePath($path);

        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }
    }

    private function avatarDir(): string
    {
        return rtrim($this->uploadRootDir, '/') . '/' . self::AVATAR_DIR;
    }

    private function absolutePath(string $relativePath): string
    {
        $relativePath = ltrim($relativePath, '/');

        return rtrim($this->uploadRootDir, '/') . '/' . $relativePath;
    }

    private function canResizeWithGd(): bool
    {
        return function_exists('imagecreatefromstring')
            && function_exists('imagecreatetruecolor')
            && function_exists('imagecopyresampled')
            && function_exists('imagewebp');
    }

    private function storeResizedWebp(UploadedFile $file, string $targetPath): bool
    {
        $contents = @file_get_contents($file->getPathname());

        if ($contents === false) {
            return false;
        }

        $source = @imagecreatefromstring($contents);

        if ($source === false) {
            return false;
        }

        $width = imagesx($source);
        $height = imagesy($source);

        if ($width <= 0 || $height <= 0) {
            imagedestroy($source);
            return false;
        }

        $sourceSize = min($width, $height);
        $sourceX = (int) floor(($width - $sourceSize) / 2);
        $sourceY = (int) floor(($height - $sourceSize) / 2);
        $targetSize = min(self::MAX_AVATAR_SIZE, $sourceSize);

        $target = imagecreatetruecolor($targetSize, $targetSize);

        imagealphablending($target, true);
        imagesavealpha($target, true);

        $resampled = imagecopyresampled(
            $target,
            $source,
            0,
            0,
            $sourceX,
            $sourceY,
            $targetSize,
            $targetSize,
            $sourceSize,
            $sourceSize
        );

        if (!$resampled) {
            imagedestroy($source);
            imagedestroy($target);
            return false;
        }

        $written = imagewebp($target, $targetPath, self::WEBP_QUALITY);

        imagedestroy($source);
        imagedestroy($target);

        return $written !== false;
    }
}
