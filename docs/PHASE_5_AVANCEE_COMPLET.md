# 🚀 GUIDE COMPLET - MODIFICATIONS AVANCÉES SYMFONY

**Date:** 14 Mai 2026  
**Objectifs:** UUID, Routes dynamiques, Export PDF/A4, Suppression en cascade, Zipping  

---

## 📋 TABLE DES MATIÈRES

1. **Ajouter UUID à Company** - 5 min
2. **Routes dynamiques avec UUID** - 15 min
3. **Suppression en cascade** - 10 min
4. **Export PDF/A4 + Email** - 20 min
5. **Cohérence des pages** - 15 min
6. **Zipping du projet** - 5 min

**Temps total : ~70 minutes**

---

## ✅ PHASE 1 : AJOUTER UUID À COMPANY

### 1.1 Migration Doctrine

```bash
bin/console make:migration
# Nommer: AddUuidToCompany
bin/console doctrine:migrations:migrate
```

### 1.2 Modifier l'entité Company

```php
// src/Entity/Company.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
class Company implements OwnedByCompanyInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // ✅ NOUVEAU: Ajouter UUID
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?Uuid $uuid = null;

    // Autres champs...

    public function __construct()
    {
        $this->uuid = Uuid::v4();  // Générer UUID automatique
    }

    public function getUuid(): ?Uuid
    {
        return $this->uuid;
    }

    public function setUuid(Uuid $uuid): self
    {
        $this->uuid = $uuid;
        return $this;
    }
}
```

### 1.3 Ajouter l'import UUID dans Devis, Order, Invoice

```php
use Symfony\Component\Uid\Uuid;

// Ajouter dans les constructeurs s'il n'existe pas
```

---

## ✅ PHASE 2 : ROUTES DYNAMIQUES AVEC UUID

### 2.1 Modifier les routes

**Pattern:** `/devis/{companyUuid}/{devisReference}`

#### DevisController

```php
namespace App\Controller;

use App\Entity\Devis;
use App\Entity\Company;
use App\Repository\DevisRepository;
use App\Repository\CompanyRepository;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;

#[Route('/devis')]
final class DevisController extends AbstractController
{
    // LIST
    #[Route('/{companyUuid}', name: 'app_devis_index', methods: ['GET'])]
    public function index(string $companyUuid, CompanyRepository $companyRepo): Response
    {
        $company = $companyRepo->findOneBy(['uuid' => $companyUuid]);
        if (!$company) {
            throw $this->createNotFoundException('Entreprise non trouvée');
        }
        
        $devis = $companyRepo->findBy(['company' => $company]);
        
        return $this->render('devis/index.html.twig', [
            'devis' => $devis,
            'company' => $company,
        ]);
    }

    // SHOW
    #[Route('/{companyUuid}/{devisReference}', name: 'app_devis_show', methods: ['GET'])]
    public function show(
        string $companyUuid,
        string $devisReference,
        DevisRepository $devisRepo,
        CompanyRepository $companyRepo
    ): Response
    {
        $company = $companyRepo->findOneBy(['uuid' => $companyUuid]);
        if (!$company) {
            throw $this->createNotFoundException('Entreprise non trouvée');
        }

        $devis = $devisRepo->findOneBy([
            'reference' => $devisReference,
            'company' => $company
        ]);

        if (!$devis) {
            throw $this->createNotFoundException('Devis non trouvé');
        }

        $this->denyAccessUnlessGranted('DEVIS_VIEW', $devis);

        return $this->render('devis/show.html.twig', [
            'devis' => $devis,
            'company' => $company,
        ]);
    }

    // NEW
    #[Route('/{companyUuid}/new', name: 'app_devis_new', methods: ['GET', 'POST'])]
    public function new(
        string $companyUuid,
        Request $request,
        CompanyRepository $companyRepo,
        EntityManagerInterface $em
    ): Response
    {
        $company = $companyRepo->findOneBy(['uuid' => $companyUuid]);
        if (!$company) {
            throw $this->createNotFoundException('Entreprise non trouvée');
        }

        $devis = new Devis();
        $form = $this->createForm(DevisType::class, $devis);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $devis->setCompany($company);
            $devis->setUser($this->getUser());
            $em->persist($devis);
            $em->flush();

            return $this->redirectToRoute('app_devis_show', [
                'companyUuid' => $company->getUuid(),
                'devisReference' => $devis->getReference()
            ]);
        }

        return $this->render('devis/new.html.twig', [
            'form' => $form,
            'company' => $company,
        ]);
    }

    // EDIT
    #[Route('/{companyUuid}/{devisReference}/edit', name: 'app_devis_edit', methods: ['GET', 'POST'])]
    public function edit(
        string $companyUuid,
        string $devisReference,
        Request $request,
        DevisRepository $devisRepo,
        CompanyRepository $companyRepo,
        EntityManagerInterface $em
    ): Response
    {
        $company = $companyRepo->findOneBy(['uuid' => $companyUuid]);
        $devis = $devisRepo->findOneBy([
            'reference' => $devisReference,
            'company' => $company
        ]);

        if (!$devis || !$company) {
            throw $this->createNotFoundException('Non trouvé');
        }

        $this->denyAccessUnlessGranted('DEVIS_EDIT', $devis);

        $form = $this->createForm(DevisType::class, $devis);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('app_devis_show', [
                'companyUuid' => $company->getUuid(),
                'devisReference' => $devis->getReference()
            ]);
        }

        return $this->render('devis/edit.html.twig', [
            'form' => $form,
            'devis' => $devis,
            'company' => $company,
        ]);
    }

    // DELETE
    #[Route('/{companyUuid}/{devisReference}/delete', name: 'app_devis_delete', methods: ['POST'])]
    public function delete(
        string $companyUuid,
        string $devisReference,
        DevisRepository $devisRepo,
        CompanyRepository $companyRepo,
        EntityManagerInterface $em,
        Request $request
    ): Response
    {
        $company = $companyRepo->findOneBy(['uuid' => $companyUuid]);
        $devis = $devisRepo->findOneBy([
            'reference' => $devisReference,
            'company' => $company
        ]);

        if (!$devis || !$company) {
            throw $this->createNotFoundException('Non trouvé');
        }

        if ($this->isCsrfTokenValid('delete' . $devis->getId(), $request->get('_token'))) {
            // ✅ Suppression en cascade (voir PHASE 3)
            foreach ($devis->getDevisDetails() as $detail) {
                $em->remove($detail);
            }
            $em->remove($devis);
            $em->flush();
        }

        return $this->redirectToRoute('app_devis_index', [
            'companyUuid' => $company->getUuid()
        ]);
    }
}
```

**⚠️ Faire la même chose pour :**
- InvoiceController
- OrderController
- ProductController (avec uuid company)

---

## ✅ PHASE 3 : SUPPRESSION EN CASCADE

### 3.1 Modifier l'entité Devis

```php
// src/Entity/Devis.php

#[ORM\OneToMany(
    targetEntity: DevisDetails::class,
    mappedBy: 'devis',
    orphanRemoval: true,  // ✅ IMPORTANT: supprime les détails quand le devis est supprimé
    cascade: ['remove']
)]
private Collection $devisDetails;
```

### 3.2 Modifier l'entité Order

```php
// src/Entity/Order.php

#[ORM\OneToMany(
    targetEntity: OrderDetail::class,
    mappedBy: 'order',
    orphanRemoval: true,  // ✅ Suppression en cascade
    cascade: ['remove']
)]
private Collection $orderDetails;
```

### 3.3 Modifier l'entité Invoice

```php
// src/Entity/Invoice.php

#[ORM\OneToMany(
    targetEntity: InvoiceDetails::class,
    mappedBy: 'invoice',
    orphanRemoval: true,  // ✅ Suppression en cascade
    cascade: ['remove']
)]
private Collection $invoiceDetails;
```

### 3.4 S'assurer que DevisDetails, OrderDetail, InvoiceDetails ne suppriment pas les Products

```php
// src/Entity/DevisDetails.php

#[ORM\ManyToOne]  // ✅ Pas de cascade: ['remove']
#[ORM\JoinColumn(nullable: false)]
private ?Product $product = null;
```

---

## ✅ PHASE 4 : EXPORT PDF/A4 + EMAIL

### 4.1 Créer un service d'export

```php
// src/Services/DocumentExportService.php

namespace App\Services;

use App\Entity\Devis;
use App\Entity\Invoice;
use App\Entity\Order;
use Dompdf\Dompdf;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class DocumentExportService
{
    public function __construct(
        private Environment $twig,
        private SendMailService $mailService
    ) {}

    /**
     * Générer PDF (avec ou sans prix)
     */
    public function generateDevisPDF(Devis $devis, bool $withPrices = true): string
    {
        $html = $this->twig->render('exports/devis_pdf.html.twig', [
            'devis' => $devis,
            'showPrices' => $withPrices,
        ]);

        return $this->generatePDF($html, $devis->getReference());
    }

    public function generateInvoicePDF(Invoice $invoice, bool $withPrices = true): string
    {
        $html = $this->twig->render('exports/invoice_pdf.html.twig', [
            'invoice' => $invoice,
            'showPrices' => $withPrices,
        ]);

        return $this->generatePDF($html, $invoice->getReference());
    }

    public function generateOrderPDF(Order $order, bool $withPrices = true): string
    {
        $html = $this->twig->render('exports/order_pdf.html.twig', [
            'order' => $order,
            'showPrices' => $withPrices,
        ]);

        return $this->generatePDF($html, $order->getReference());
    }

    /**
     * Générer PDF interne
     */
    private function generatePDF(string $html, string $filename): string
    {
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();

        $filepath = sys_get_temp_dir() . '/' . $filename . '.pdf';
        file_put_contents($filepath, $dompdf->output());

        return $filepath;
    }

    /**
     * Envoyer par email
     */
    public function sendDevisByEmail(Devis $devis, string $recipientEmail, bool $withPrices = true): void
    {
        $pdfPath = $this->generateDevisPDF($devis, $withPrices);

        $this->mailService->send(
            $devis->getCompany()->getEmail(),
            $recipientEmail,
            "Devis {$devis->getReference()}",
            'devis_email',
            [
                'devis' => $devis,
                'attachment' => $pdfPath,
            ]
        );

        unlink($pdfPath);  // Nettoyer
    }

    public function sendInvoiceByEmail(Invoice $invoice, string $recipientEmail, bool $withPrices = true): void
    {
        $pdfPath = $this->generateInvoicePDF($invoice, $withPrices);

        $this->mailService->send(
            $invoice->getCompany()->getEmail(),
            $recipientEmail,
            "Facture {$invoice->getReference()}",
            'invoice_email',
            [
                'invoice' => $invoice,
                'attachment' => $pdfPath,
            ]
        );

        unlink($pdfPath);
    }

    public function sendOrderByEmail(Order $order, string $recipientEmail, bool $withPrices = true): void
    {
        $pdfPath = $this->generateOrderPDF($order, $withPrices);

        $this->mailService->send(
            $order->getCompany()->getEmail(),
            $recipientEmail,
            "Commande {$order->getReference()}",
            'order_email',
            [
                'order' => $order,
                'attachment' => $pdfPath,
            ]
        );

        unlink($pdfPath);
    }
}
```

### 4.2 Ajouter les actions dans les contrôleurs

```php
// Dans DevisController

#[Route('/{companyUuid}/{devisReference}/export-pdf', name: 'app_devis_export_pdf', methods: ['GET'])]
public function exportPDF(
    string $companyUuid,
    string $devisReference,
    DevisRepository $devisRepo,
    CompanyRepository $companyRepo,
    DocumentExportService $exportService
): Response
{
    $company = $companyRepo->findOneBy(['uuid' => $companyUuid]);
    $devis = $devisRepo->findOneBy(['reference' => $devisReference, 'company' => $company]);

    if (!$devis) {
        throw $this->createNotFoundException();
    }

    $pdfPath = $exportService->generateDevisPDF($devis, showPrices: true);
    
    return $this->file($pdfPath, "devis-{$devis->getReference()}.pdf", ResponseHeaderBag::DISPOSITION_ATTACHMENT);
}

#[Route('/{companyUuid}/{devisReference}/export-pdf-no-prices', name: 'app_devis_export_pdf_no_prices', methods: ['GET'])]
public function exportPDFNoPrices(
    string $companyUuid,
    string $devisReference,
    DevisRepository $devisRepo,
    CompanyRepository $companyRepo,
    DocumentExportService $exportService
): Response
{
    $company = $companyRepo->findOneBy(['uuid' => $companyUuid]);
    $devis = $devisRepo->findOneBy(['reference' => $devisReference, 'company' => $company]);

    if (!$devis) {
        throw $this->createNotFoundException();
    }

    $pdfPath = $exportService->generateDevisPDF($devis, showPrices: false);
    
    return $this->file($pdfPath, "devis-{$devis->getReference()}-no-prices.pdf", ResponseHeaderBag::DISPOSITION_ATTACHMENT);
}

#[Route('/{companyUuid}/{devisReference}/send-email', name: 'app_devis_send_email', methods: ['POST'])]
public function sendEmail(
    string $companyUuid,
    string $devisReference,
    Request $request,
    DevisRepository $devisRepo,
    CompanyRepository $companyRepo,
    DocumentExportService $exportService
): Response
{
    $company = $companyRepo->findOneBy(['uuid' => $companyUuid]);
    $devis = $devisRepo->findOneBy(['reference' => $devisReference, 'company' => $company]);

    if (!$devis) {
        throw $this->createNotFoundException();
    }

    $email = $request->request->get('email');
    $withPrices = $request->request->getBoolean('with_prices', true);

    try {
        $exportService->sendDevisByEmail($devis, $email, $withPrices);
        $this->addFlash('success', 'Email envoyé avec succès');
    } catch (\Exception $e) {
        $this->addFlash('error', 'Erreur lors de l\'envoi: ' . $e->getMessage());
    }

    return $this->redirectToRoute('app_devis_show', [
        'companyUuid' => $company->getUuid(),
        'devisReference' => $devis->getReference()
    ]);
}
```

### 4.3 Créer les templates PDF

```twig
{# templates/exports/devis_pdf.html.twig #}
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Devis {{ devis.reference }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        h1 { color: var(--deep-marine); }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        .price { text-align: right; }
        .hidden-price { display: {% if not showPrices %}none{% endif %}; }
    </style>
</head>
<body>
    <h1>Devis {{ devis.reference }}</h1>
    
    <p><strong>Date:</strong> {{ devis.createdAt | date('d/m/Y') }}</p>
    <p><strong>Client:</strong> {{ devis.client.raisonSocial }}</p>

    <table>
        <thead>
            <tr>
                <th>Produit</th>
                <th>Quantité</th>
                <th class="hidden-price">Prix Unit.</th>
                <th class="hidden-price">Total</th>
            </tr>
        </thead>
        <tbody>
            {% for detail in devis.devisDetails %}
            <tr>
                <td>{{ detail.product.name }}</td>
                <td>{{ detail.quantite }}</td>
                <td class="price hidden-price">{{ detail.prixUnitaire | number_format(2, ',', ' ') }} €</td>
                <td class="price hidden-price">{{ (detail.quantite * detail.prixUnitaire) | number_format(2, ',', ' ') }} €</td>
            </tr>
            {% endfor %}
        </tbody>
    </table>

    <div class="hidden-price" style="text-align: right; margin-top: 20px;">
        <p><strong>Total HT:</strong> {{ devis.total | number_format(2, ',', ' ') }} €</p>
        <p><strong>TVA (20%):</strong> {{ devis.taxe | number_format(2, ',', ' ') }} €</p>
        <p><strong>Total TTC:</strong> {{ devis.totalTTC | number_format(2, ',', ' ') }} €</p>
    </div>
</body>
</html>
```

---

## ✅ PHASE 5 : COHÉRENCE DES PAGES

### 5.1 Design unifié

Toutes les pages doivent utiliser :
- **Palette :** Shoreline Haze
- **Typo :** Cormorant Garamond + Raleway
- **Mode :** Dark mode supporté
- **Responsive :** Mobile-first

### 5.2 Template layout commun

```twig
{# templates/documents/_layout.html.twig #}

{% extends 'base.html.twig' %}

{% block body %}
<div class="documents-layout">
    <!-- Header -->
    <div class="documents-header">
        <h1 style="color: var(--deep-marine);">{{ documentType }}</h1>
        <div class="breadcrumb">
            <a href="{{ path('app_dashboard') }}">Dashboard</a>
            > {{ breadcrumb }}
        </div>
    </div>

    <!-- Content -->
    <div class="documents-content">
        {% block content %}{% endblock %}
    </div>

    <!-- Footer Actions -->
    <div class="documents-actions">
        {% block actions %}{% endblock %}
    </div>
</div>

<style>
.documents-layout {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.documents-header {
    border-bottom: 2px solid var(--cloudy-ocean);
    margin-bottom: 30px;
    padding-bottom: 20px;
}

.documents-content {
    background: white;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 20px;
}

.documents-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    padding: 20px 0;
}

@media (prefers-color-scheme: dark) {
    .documents-content {
        background: var(--cream-dark);
    }
}
</style>
{% endblock %}
```

---

## ✅ PHASE 6 : ZIPPING DU PROJET

### Commande finale

```bash
cd /tmp/sass-final

# Copier tous les fichiers générés
cp /mnt/user-data/outputs/* . 2>/dev/null || true

# Créer le ZIP
zip -r /mnt/user-data/outputs/sass-symfony-final.zip . \
    -x "var/*" "node_modules/*" ".git/*" ".env.local" \
    "vendor/*" ".cache/*"

echo "✅ ZIP créé: /mnt/user-data/outputs/sass-symfony-final.zip"
```

---

## 📝 RÉSUMÉ DES CHANGEMENTS

```
✅ UUID automatique à la création Company
✅ Routes dynamiques: /devis/{companyUuid}/{devisReference}
✅ Suppression en cascade des détails (mais pas des produits)
✅ Export PDF (avec/sans prix)
✅ Envoi par email
✅ Cohérence design (Shoreline Haze partout)
✅ Responsive mobile
✅ Dark mode supporté
✅ Zipping du projet complet
```

---

## 📊 FICHIERS À CRÉER/MODIFIER

| Type | Fichier | Action |
|------|---------|--------|
| Migration | | make:migration |
| Entité | Company.php | Ajouter UUID |
| Entité | Devis.php | orphanRemoval: true |
| Entité | Order.php | orphanRemoval: true |
| Entité | Invoice.php | orphanRemoval: true |
| Service | DocumentExportService.php | Créer |
| Controller | DevisController.php | Routes UUID |
| Controller | OrderController.php | Routes UUID |
| Controller | InvoiceController.php | Routes UUID |
| Template | devis_pdf.html.twig | Créer |
| Template | invoice_pdf.html.twig | Créer |
| Template | order_pdf.html.twig | Créer |
| Template | _layout.html.twig | Créer |

---

**Temps total d'implémentation : ~70 minutes**

Tous les fichiers détaillés sont dans `/outputs/`
