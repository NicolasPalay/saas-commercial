# 🔧 GUIDE D'INSTALLATION DES MODIFICATIONS - SASS SYMFONY

**Date:** 14 Mai 2026  
**Objectif:** Implémenter UUID, Routes dynamiques, PDF export, Suppression en cascade  

---

## 📋 MODIFICATIONS À EFFECTUER

### ÉTAPE 1 : UUID à Company (10 min)

#### 1.1 Ajouter UUID à l'entité Company

Modifier `src/Entity/Company.php` :

```php
use Symfony\Component\Uid\Uuid;

class Company
{
    // ... champs existants ...

    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $uuid;

    public function __construct()
    {
        $this->uuid = Uuid::v4();  // ← Généré automatiquement
        // ... reste du constructeur ...
    }

    public function getUuid(): Uuid
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

#### 1.2 Générer la migration

```bash
bin/console make:migration
# Nommer: AddUuidToCompany
bin/console doctrine:migrations:migrate
```

---

### ÉTAPE 2 : Suppression en cascade (5 min)

#### 2.1 Modifier Devis.php

```php
#[ORM\OneToMany(
    targetEntity: DevisDetails::class,
    mappedBy: 'devis',
    orphanRemoval: true,  // ← IMPORTANT
    cascade: ['remove']
)]
private Collection $devisDetails;
```

#### 2.2 Modifier Order.php

```php
#[ORM\OneToMany(
    targetEntity: OrderDetail::class,
    mappedBy: 'order',
    orphanRemoval: true,  // ← IMPORTANT
    cascade: ['remove']
)]
private Collection $orderDetails;
```

#### 2.3 Modifier Invoice.php

```php
#[ORM\OneToMany(
    targetEntity: InvoiceDetails::class,
    mappedBy: 'invoice',
    orphanRemoval: true,  // ← IMPORTANT
    cascade: ['remove']
)]
private Collection $invoiceDetails;
```

#### 2.4 S'assurer que DevisDetails, OrderDetail, InvoiceDetails ne suppriment PAS les Products

```php
// Dans DevisDetails.php
#[ORM\ManyToOne]  // ← Pas de cascade: ['remove']
#[ORM\JoinColumn(nullable: false)]
private ?Product $product = null;

// Même chose pour OrderDetail.php et InvoiceDetails.php
```

---

### ÉTAPE 3 : Routes dynamiques UUID (15 min)

#### 3.1 Copier le nouveau DevisController

```bash
# Remplacer le DevisController existant par DevisController_new.php
cp src/Controller/DevisController_new.php src/Controller/DevisController.php
```

#### 3.2 Faire la même chose pour InvoiceController et OrderController

**Pattern pour InvoiceController :**

```php
#[Route('/invoice')]
final class InvoiceController extends AbstractController
{
    #[Route('/{companyUuid}', name: 'app_invoice_index')]
    #[Route('/{companyUuid}/{invoiceReference}', name: 'app_invoice_show')]
    #[Route('/{companyUuid}/new', name: 'app_invoice_new')]
    #[Route('/{companyUuid}/{invoiceReference}/edit', name: 'app_invoice_edit')]
    #[Route('/{companyUuid}/{invoiceReference}/delete', name: 'app_invoice_delete')]
    #[Route('/{companyUuid}/{invoiceReference}/export-pdf', name: 'app_invoice_export_pdf')]
    #[Route('/{companyUuid}/{invoiceReference}/export-pdf-no-prices', name: 'app_invoice_export_pdf_no_prices')]
    #[Route('/{companyUuid}/{invoiceReference}/send-email', name: 'app_invoice_send_email')]
}
```

**Pattern pour OrderController :**

```php
#[Route('/order')]
final class OrderController extends AbstractController
{
    #[Route('/{companyUuid}', name: 'app_order_index')]
    #[Route('/{companyUuid}/{orderReference}', name: 'app_order_show')]
    #[Route('/{companyUuid}/new', name: 'app_order_new')]
    #[Route('/{companyUuid}/{orderReference}/edit', name: 'app_order_edit')]
    #[Route('/{companyUuid}/{orderReference}/delete', name: 'app_order_delete')]
    #[Route('/{companyUuid}/{orderReference}/export-pdf', name: 'app_order_export_pdf')]
    #[Route('/{companyUuid}/{orderReference}/export-pdf-no-prices', name: 'app_order_export_pdf_no_prices')]
    #[Route('/{companyUuid}/{orderReference}/send-email', name: 'app_order_send_email')]
}
```

#### 3.3 Mettre à jour les templates pour les nouvelles routes

Dans les templates Twig, remplacer les routes anciennes :

```twig
{# ANCIEN #}
<a href="{{ path('app_devis_show', {'id': devis.id}) }}">

{# NOUVEAU #}
<a href="{{ path('app_devis_show', {'companyUuid': company.uuid, 'devisReference': devis.reference}) }}">
```

---

### ÉTAPE 4 : Créer le DocumentExportService (10 min)

#### 4.1 Copier le service

```bash
cp src/Services/DocumentExportService.php src/Services/
```

#### 4.2 Installer Dompdf (si pas déjà installé)

```bash
composer require dompdf/dompdf
```

#### 4.3 Créer les templates PDF

```bash
mkdir -p templates/exports

# Copier les templates
cp templates/exports/devis_pdf.html.twig templates/exports/
cp templates/exports/invoice_pdf.html.twig templates/exports/
cp templates/exports/order_pdf.html.twig templates/exports/
```

---

### ÉTAPE 5 : Créer les templates pour les formulaires d'export (5 min)

#### 5.1 Ajouter des boutons dans les templates show

Dans `templates/devis/show.html.twig` :

```twig
<div class="export-actions">
    <a href="{{ path('app_devis_export_pdf', {
        'companyUuid': company.uuid,
        'devisReference': devis.reference
    }) }}" class="btn btn-primary">
        📥 Télécharger PDF
    </a>

    <a href="{{ path('app_devis_export_pdf_no_prices', {
        'companyUuid': company.uuid,
        'devisReference': devis.reference
    }) }}" class="btn btn-secondary">
        📥 PDF (sans prix)
    </a>

    <form method="post" action="{{ path('app_devis_send_email', {
        'companyUuid': company.uuid,
        'devisReference': devis.reference
    }) }}" style="display: inline;">
        <input type="email" name="email" placeholder="Email du client" required>
        <input type="checkbox" name="with_prices" value="1" checked> Avec prix
        <button type="submit">📧 Envoyer</button>
        <input type="hidden" name="_token" value="{{ csrf_token('') }}">
    </form>
</div>
```

---

### ÉTAPE 6 : Tests (10 min)

#### 6.1 Tester la génération de la migration

```bash
bin/console doctrine:migrations:migrate
# Devrait ajouter la colonne UUID à la table company
```

#### 6.2 Tester les routes

```bash
bin/console debug:router | grep devis
# Devrait afficher les routes avec {companyUuid}
```

#### 6.3 Tester l'export PDF

```bash
# 1. Aller sur /devis/{companyUuid}
# 2. Ouvrir un devis
# 3. Cliquer sur "Télécharger PDF"
# 4. Vérifier que le PDF se télécharge
```

#### 6.4 Tester l'envoi par email

```bash
# 1. Sur la page du devis
# 2. Entrer un email
# 3. Cliquer "Envoyer"
# 4. Vérifier que l'email est envoyé
```

---

### ÉTAPE 7 : Cohérence des pages (10 min)

Toutes les pages devis/invoice/order doivent :

✅ Utiliser la même palette Shoreline Haze
✅ Avoir les mêmes boutons (Export, Email, Delete)
✅ Afficher les mêmes informations
✅ Supporter le mode sombre
✅ Être responsive mobile

**Template layout commun :**

```twig
{# templates/documents/_layout.html.twig #}

{% extends 'base.html.twig' %}

{% block body %}
<div class="documents-layout">
    <div class="documents-header">
        <h1 style="color: var(--deep-marine);">{{ documentType }}</h1>
        <div class="breadcrumb">
            <a href="{{ path('app_dashboard') }}">Dashboard</a>
            > {{ breadcrumb }}
        </div>
    </div>

    <div class="documents-content">
        {% block content %}{% endblock %}
    </div>

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

## 🗂️ FICHIERS À CRÉER/MODIFIER

```
CRÉER:
├── migrations/Version20260514AddUuidToCompany.php
├── src/Services/DocumentExportService.php
├── templates/exports/devis_pdf.html.twig
├── templates/exports/invoice_pdf.html.twig
├── templates/exports/order_pdf.html.twig
└── templates/documents/_layout.html.twig

MODIFIER:
├── src/Entity/Company.php          (+UUID)
├── src/Entity/Devis.php            (+orphanRemoval)
├── src/Entity/Order.php            (+orphanRemoval)
├── src/Entity/Invoice.php          (+orphanRemoval)
├── src/Entity/DevisDetails.php     (sans cascade remove)
├── src/Entity/OrderDetail.php      (sans cascade remove)
├── src/Entity/InvoiceDetails.php   (sans cascade remove)
├── src/Controller/DevisController.php      (routes UUID)
├── src/Controller/OrderController.php      (routes UUID)
├── src/Controller/InvoiceController.php    (routes UUID)
└── templates/devis/show.html.twig         (+ export buttons)
```

---

## ✅ CHECKLIST FINALE

- [ ] UUID généré à la création Company
- [ ] Migration exécutée
- [ ] Routes avec companyUuid fonctionnelles
- [ ] Suppression en cascade fonctionne
- [ ] Products ne sont pas supprimés
- [ ] Export PDF fonctionne
- [ ] Export PDF sans prix fonctionne
- [ ] Email d'envoi fonctionne
- [ ] Tous les contrôleurs ont les mêmes routes
- [ ] Templates cohérents (même design)
- [ ] Mode sombre supporté
- [ ] Responsive mobile fonctionne

---

## 🚀 PROCHAINES ÉTAPES

1. Appliquer toutes les modifications
2. Générer et exécuter les migrations
3. Tester toutes les fonctionnalités
4. Zipper le projet complet
5. Déployer en production

---

**Temps total : ~70 minutes**

Besoin d'aide ? Consulte PHASE_5_AVANCEE_COMPLET.md
