<?php

declare(strict_types=1);

namespace App\SharedKernel\Infrastructure\FileSystem\Manager;

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AvatarManager
{
    /** @var array<string, string> */
    private array $extensions = [
        'png' => 'image/png',
        'jpg' => 'image/jpeg'
    ];

    private FilesystemOperator $pictureStorage;
    private string $projectDir;

    public function __construct(FilesystemOperator $pictureStorage, string $projectDir)
    {
        $this->pictureStorage = $pictureStorage;
        $this->projectDir = $projectDir;
    }

    /**
     * @throws FilesystemException
     */
    public function write(string $path, string $contents): void
    {
        $this->pictureStorage->write($path, $contents);
    }


    /**
     * @param string|null $path
     * @return StreamedResponse
     * @throws FilesystemException
     */
    public function read(?string $path): StreamedResponse
    {
        if ($path && $this->pictureStorage->fileExists($path)) {
            $stream = $this->pictureStorage->readStream($path);
            return new StreamedResponse(function () use ($stream) {
                fpassthru($stream);
                exit();
            }, 200, ['Content-Type' => $this->getMimeType($path)]);
        }

        // Return default avatar if user avatar doesn't exist
        $defaultAvatarPath = $this->projectDir . '/assets/img/default/avatar.png';
        return new StreamedResponse(function () use ($defaultAvatarPath) {
            $handle = fopen($defaultAvatarPath, 'rb');
            if ($handle !== false) {
                fpassthru($handle);
                fclose($handle);
            }
            exit();
        }, 200, ['Content-Type' => 'image/png']);
    }


    /**
     * @return list<string>
     */
    public function getAllowedMimeType(): array
    {
        return array_values($this->extensions);
    }

    public function getExtension(string $mimeType): string
    {
        $types = array_flip($this->extensions);
        return $types[$mimeType] ?? 'png';
    }

    private function getMimeType(string $file): string
    {
        $infos = pathinfo($file);
        $extension = $infos['extension'] ?? 'png';
        return $this->extensions[$extension] ?? 'image/png';
    }
}
