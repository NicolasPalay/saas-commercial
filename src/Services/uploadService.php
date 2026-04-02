<?php

// src/Services/UploadService.php
namespace App\Services;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Uid\Uuid;

class uploadService
{
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'application/pdf',
    ];

    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'];

    private const MAX_SIZE = 5 * 1024 * 1024; // 5 Mo

    public function __construct(
        private string $projectDir
    ) {}

    public function upload(UploadedFile $file, $company): string
    {
        // 1. Taille maximale
        if ($file->getSize() > self::MAX_SIZE) {
            throw new \RuntimeException('Fichier trop volumineux (max 5 Mo).');
        }

        // 2. Extension whitelist
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw new \RuntimeException('Extension non autorisée.');
        }

        // 3. Validation MIME réelle via magic bytes (pas ce que le client déclare)
        $realMime = mime_content_type($file->getPathname());
        if (!in_array($realMime, self::ALLOWED_MIME_TYPES, true)) {
            throw new \RuntimeException('Type de fichier non autorisé.');
        }

        // 4. Double vérification : extension cohérente avec le MIME réel
        $guesser = \Symfony\Component\Mime\MimeTypes::getDefault();
        $expectedExtensions = $guesser->getExtensions($realMime);
        if (!in_array($extension, $expectedExtensions, true)) {
            throw new \RuntimeException('Extension incohérente avec le contenu du fichier.');
        }

        // 5. Nom UUID — jamais le nom d'origine
        $filename = Uuid::v4()->toRfc4122() . '.' . $extension;

        // 6. Dépôt HORS de public/ (non accessible directement en HTTP)
        $uploadDir = $this->projectDir . '/var/uploads/' . $company->getId() . '/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0750, true);
        }

        $file->move($uploadDir, $filename);

        return $filename;
    }

    public function delete(string $filename, $company): void
    {
        // Sécurité : empêcher path traversal
        $filename = basename($filename);

        $filePath = $this->projectDir . '/var/uploads/' . $company->getId() . '/' . $filename;

        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    /**
     * Sert le fichier via le contrôleur (puisqu'il est hors public/)
     */
    public function getFilePath(string $filename, $company): string
    {
        $filename = basename($filename);
        return $this->projectDir . '/var/uploads/' . $company->getId() . '/' . $filename;
    }
}