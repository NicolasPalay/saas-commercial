<?php

namespace App\Services;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class uploadService extends AbstractController
{
    public function upload($file, $company): string
    {
        $filename =$file->getClientOriginalName();
                $filename = pathinfo($filename, PATHINFO_FILENAME);
                $extension = $file->guessExtension();
                $filename = $filename.'-'.$company->getId().'.'.$extension;
                $file->move(
                    $this->getParameter('kernel.project_dir').'/public/uploads/'.$company->getId().'/',
                    $filename
                );

        return $filename;
    }

    public function delete($filename, $company): void
    {
        $filePath = $this->getParameter('kernel.project_dir').'/public/uploads/'.$company->getId().'/'.$filename;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}