<?php

declare(strict_types=1);

namespace App\SharedKernel\Infrastructure\FileSystem\Manager;

use App\SharedKernel\Domain\Exception\PictureAlreadyExistsException;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\ImageManager;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PictureUploadManager
{
    /** @var array<string, string> */
    private array $extensions = [
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
    ];

    public function __construct(private readonly FilesystemOperator $pictureStorage)
    {
    }

    /**
     * @return list<string>
     */
    public function getAllowedMimeTypes(): array
    {
        return array_values($this->extensions);
    }

    /**
     * @throws FilesystemException
     * @throws PictureAlreadyExistsException
     */
    public function upload(UploadedFile $file, string $directory): string
    {
        $extension = $this->getExtension($file->getMimeType() ?? 'image/png');
        $filename = $this->sanitizeFilename($file->getClientOriginalName()) . '.' . $extension;
        $path = $directory . '/' . $filename;

        if ($this->pictureStorage->fileExists($path)) {
            throw new PictureAlreadyExistsException($filename);
        }

        if ($extension === 'gif') {
            // Kept as-is (no resize/re-encode) to preserve animated GIFs used for badges.
            $contents = (string) $file->getContent();
        } else {
            $manager = ImageManager::gd();
            $image = $manager->read($file->getContent());
            $image->scaleDown(width: 1200, height: 1200);

            $encoder = match ($extension) {
                'jpg' => new JpegEncoder(quality: 90),
                default => new PngEncoder(),
            };
            $contents = (string) $image->encode($encoder);
        }

        $this->pictureStorage->write($path, $contents);

        return $filename;
    }

    private function getExtension(string $mimeType): string
    {
        $types = array_flip($this->extensions);
        return $types[$mimeType] ?? 'png';
    }

    private function sanitizeFilename(string $originalName): string
    {
        $name = pathinfo($originalName, PATHINFO_FILENAME);
        $name = preg_replace('/[^A-Za-z0-9_-]+/', '-', $name) ?? '';
        $name = trim($name, '-');

        return $name !== '' ? $name : 'picture';
    }
}
