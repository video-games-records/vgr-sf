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
    public const int MAX_FILE_SIZE = 5_000_000;

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
     * Validates size/mime-type constraints outside of a Symfony Form (e.g. for an ajax endpoint).
     * Returns an error message, or null if the file is valid.
     */
    public function validate(UploadedFile $file): ?string
    {
        if (!$file->isValid()) {
            return 'Invalid upload.';
        }

        $size = $file->getSize();
        if ($size === false || $size > self::MAX_FILE_SIZE) {
            return 'File is too large (5MB max).';
        }

        if (!in_array($file->getMimeType(), $this->getAllowedMimeTypes(), true)) {
            return 'Invalid file type.';
        }

        return null;
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
