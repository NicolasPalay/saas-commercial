<?php


namespace App\Controller;

use App\Services\UploadService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Http\Attribute\IsGranted as AttributeIsGranted;

class FileController extends AbstractController
{   
    #[Route('/uploads/{companyId}/{filename}', name: 'app_file_serve')]
    #[AttributeIsGranted('ROLE_USER')]
    public function serve(
        int $companyId,
        string $filename,
        UploadService $uploadService
    ): Response {
        $user = $this->getUser();

        // Vérifier que la company demandée est bien celle de l'utilisateur
        if ($user->getCompany()->getId() !== $companyId) {
            throw $this->createAccessDeniedException();
        }

        $path = $uploadService->getFilePath($filename, $user->getCompany());

        if (!file_exists($path)) {
            throw $this->createNotFoundException();
        }

        return new BinaryFileResponse($path);
    }
}