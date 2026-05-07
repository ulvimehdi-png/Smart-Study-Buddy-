<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

final class ProfileImageStorage
{
    private string $uploadDir;
    private string $publicPrefix = '/uploads/avatars/';
    private string $projectDir;
    private SluggerInterface $slugger;

    public function __construct(KernelInterface $kernel, SluggerInterface $slugger)
    {
        $this->projectDir = $kernel->getProjectDir();
        $this->slugger = $slugger;
        $this->uploadDir = $this->projectDir.'/public/uploads/avatars';
    }

    public function publicUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        return $this->publicPrefix.ltrim($path, '/');
    }

    public function store(User $user, UploadedFile $file): string
    {
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0775, true);
        }

        $this->remove($user->getProfileImagePath());

        $baseName = $this->slugger->slug($user->getUsername() ?: 'user')->lower()->toString();
        $fileName = sprintf('%s-%s.webp', $baseName, bin2hex(random_bytes(8)));

        if ($this->canResizeWithGd() && $this->storeResizedWebp($file, $this->uploadDir.'/'.$fileName)) {
            return $fileName;
        }

        $extension = $file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'bin';
        $fallbackName = sprintf('%s-%s.%s', $baseName, bin2hex(random_bytes(8)), strtolower($extension));
        $file->move($this->uploadDir, $fallbackName);

        return $fallbackName;
    }

    public function remove(?string $path): void
    {
        if (!$path) {
            return;
        }

        $absolutePath = $this->uploadDir.'/'.basename($path);

        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }
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

        if (false === $contents) {
            return false;
        }

        $source = @imagecreatefromstring($contents);

        if (false === $source) {
            return false;
        }

        $width = imagesx($source);
        $height = imagesy($source);

        if ($width <= 0 || $height <= 0) {
            imagedestroy($source);
            return false;
        }

        $size = min($width, $height);
        $srcX = (int) floor(($width - $size) / 2);
        $srcY = (int) floor(($height - $size) / 2);
        $targetSize = min(512, $size);

        $target = imagecreatetruecolor($targetSize, $targetSize);

        imagealphablending($target, true);
        imagesavealpha($target, true);

        if (!imagecopyresampled(
            $target,
            $source,
            0,
            0,
            $srcX,
            $srcY,
            $targetSize,
            $targetSize,
            $size,
            $size
        )) {
            imagedestroy($source);
            imagedestroy($target);
            return false;
        }

        $written = imagewebp($target, $targetPath, 82);

        imagedestroy($source);
        imagedestroy($target);

        return false !== $written;
    }
}
