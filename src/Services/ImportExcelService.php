<?php


namespace App\Services;

use App\Entity\Company;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Entity\Product;
use App\Entity\Taxe;
use Doctrine\ORM\EntityManagerInterface;

class ImportExcelService
{
    public function importProducts(string $filePath, EntityManagerInterface $em, Company $company, Taxe $taxe): int
    {
       
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();

        $count = 0;

        foreach ($sheet->getRowIterator() as $rowIndex => $row) {

            // skip header
            if ($rowIndex === 1) continue;

            $data = $sheet->rangeToArray("A{$rowIndex}:F{$rowIndex}")[0];

            $reference = $data[0];
            $name = $data[1];
            $price = $data[2];
            $stock = $data[3];
            $costPrice = $data[4];
            $barcode = $data[5];
            $isActive = true;
            $isService = false;

            if (!$name || !is_numeric($price)) {
                continue; // ignore lignes invalides
            }

            $product = new Product();
            $product->setReference((string)$reference);
            $product->setName($name);
            $product->setPrice((float)$price);
            $product->setStock((int)$stock);
            $product->setCostPrice((float)$costPrice);
            $product->setBarcode($barcode);
            $product->setCompany($company);
            $product->setIsActive($isActive);
            $product->setTaxe($taxe);
            $product->setIsService($isService);

            




            $em->persist($product);
            $count++;

            // 🔥 optimisation mémoire
            if ($count % 50 === 0) {
                $em->flush();
                $em->clear();
            }
        }

        $em->flush();

        return $count;
    }
}