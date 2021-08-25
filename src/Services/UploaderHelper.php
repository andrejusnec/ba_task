<?php

namespace App\Services;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Asset\Context\RequestStackContext;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploaderHelper
{
    const ADDRESS_BOOK_IMAGE = 'contact_image';
    private Filesystem $fileSystem;
    private RequestStackContext $requestStackContext;
    private LoggerInterface $logger;

    /**
     * @param Filesystem $publicUploadsFilesystem
     * @param RequestStackContext $requestStackContext
     * @param LoggerInterface $logger
     */
    public function __construct(Filesystem          $publicUploadsFilesystem,
                                RequestStackContext $requestStackContext,
                                LoggerInterface     $logger)
    {
        $this->fileSystem = $publicUploadsFilesystem;
        $this->requestStackContext = $requestStackContext;
        $this->logger = $logger;
    }

    /**
     * @throws FilesystemException
     */
    public function uploadAddressBookImage(UploadedFile $uploadedFile, ?string $existingFileName): string
    {
        //$destination = $this->uploadsPath.'/'.self::ADDRESS_BOOK_IMAGE;

        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $newFilename = $originalFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();

        $stream = fopen($uploadedFile->getPathName(), 'r');
        $this->fileSystem->writeStream(
            self::ADDRESS_BOOK_IMAGE . '/' . $newFilename,
            $stream
        );
        if (is_resource($stream)) {
            fclose($stream);
        }
        if ($existingFileName) {
            try {
                $this->fileSystem->delete(self::ADDRESS_BOOK_IMAGE . '/' . $existingFileName);
            } catch (FileNotFoundException $e) {
                $this->logger->alert(sprintf('Old Uploaded file %s was missing when trying to delete.', $existingFileName));
            }

        }
        return $newFilename;
    }

    public function getPublicPath(string $path): string
    {
        return $this->requestStackContext->getBasePath() . '/uploads/' . $path;
    }
}