<?php

namespace App\Services;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\ManipulatorInterface;
use Doctrine\ORM\EntityManagerInterface;


class ImageService
{

    public function __construct(private EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function uploadFile($slug, $dossier, $file, $width, $height, $entity)
    {
        $uploadsDir = __DIR__ . '/../../public/assets/' . $dossier . '/';
        $newFilename =  $slug . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
        $tempImagePath = $file->getPathname(); // Fichier temporaire

        if (!file_exists($tempImagePath)) {
            throw new \Exception("Le fichier temporaire n'existe pas : $tempImagePath");
        }

        $imagine = new Imagine();

        // Générer une miniature
        $imagine->open($tempImagePath)
            ->thumbnail(new Box($width / 2, $height / 2), ManipulatorInterface::THUMBNAIL_OUTBOUND)
            ->save($uploadsDir . "mini_" . $newFilename, ['quality' => 70]);

        // Générer l'image principale
        $imagine->open($tempImagePath)
            ->thumbnail(new Box($width, $height), ManipulatorInterface::THUMBNAIL_OUTBOUND)
            ->save($uploadsDir . $newFilename, ['quality' => 80]); 

        // 🔥 Adapter en fonction de l'entité cible

            $entity->setImage($newFilename);
 
        $this->entityManager->flush();

        return $newFilename;
    }
}
