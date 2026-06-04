# 🚀 GUIDE D'IMPLÉMENTATION RAPIDE

## Phase 1 : Préparation (5 min)

### 1. Copier les fichiers créés

```bash
# Depuis le dossier du projet
cp -r /chemin/extraction/src/* src/
cp -r /chemin/extraction/public/assets/* public/assets/
cp -r /chemin/extraction/templates/* templates/
```

### 2. Vérifier les dépendances PHP

```bash
composer require symfony/mailer
composer require symfony/console
```

---

## Phase 2 : Migrations Doctrine (10 min)

### 1. Créer les migrations pour les nouvelles entités

```bash
bin/console make:migration
```

Cela créera :
- `DocumentTemplate` (nouvelle entité)
- `Invoice.reminderSentAt` (nouveau champ)

### 2. Exécuter les migrations

```bash
bin/console doctrine:migrations:migrate
```

### 3. Vérifier le schéma

```bash
bin/console doctrine:schema:validate
```

---

## Phase 3 : CSS & JS (2 min)

### Mettre à jour `templates/base.html.twig`

```html
<!DOCTYPE html>
<html lang="fr">
<head>
    <!-- ... autres meta ... -->
    
    {# === NOUVEAU : Mode Sombre + Responsive === #}
    <link rel="stylesheet" href="{{ asset('assets/styles/dark-mode-responsive.css') }}">
</head>
<body>
    {# Navigation, contenu, etc. #}
    
    {# === NOUVEAU : Bouton toggle mode sombre === #}
    <button class="dark-mode-toggle" title="Toggle dark mode" 
            style="position: fixed; top: 20px; right: 20px; z-index: 999;">
        🌙
    </button>

    {# === Scripts #}
    <script src="{{ asset('assets/js/dark-mode-responsive.js') }}"></script>
</body>
</html>
```

---

## Phase 4 : Dashboard (5 min)

### Mettre à jour `DashboardController` (DÉJÀ FAIT ✓)

Si vous copiez le fichier, il inclut :
- Service `DashboardStatisticsService`
- Injection des repositories
- Variables Twig pour KPIs

### Afficher les KPIs dans `templates/dashboard/index.html.twig`

```html
<div class="kpis-grid">
    <div class="kpi-card">
        <h3>CA Mensuel</h3>
        <span class="amount">{{ monthlyRevenue|number_format(2, ',', ' ') }} €</span>
    </div>
    
    <div class="kpi-card">
        <h3>CA Annuel</h3>
        <span class="amount">{{ annualRevenue|number_format(2, ',', ' ') }} €</span>
    </div>
    
    <div class="kpi-card">
        <h3>Clients</h3>
        <span class="number">{{ totalClients }}</span>
    </div>
    
    <div class="kpi-card">
        <h3>Factures Impayées</h3>
        <span class="amount">{{ unpaidInvoicesAmount|number_format(2, ',', ' ') }} €</span>
        <small>{{ unpaidInvoicesCount }} factures</small>
    </div>
</div>
```

---

## Phase 5 : Rappels de Paiement (5 min)

### 1. Vérifier la config email

```yaml
# config/packages/mailer.yaml
framework:
    mailer:
        dsn: sendmail://default
        # Ou un vrai SMTP pour production
```

### 2. Tester la commande

```bash
bin/console app:payment:send-reminders
```

Sortie :
```
✓ Rappel envoyé - Facture #42
✓ Rappel envoyé - Facture #43
✗ Erreur - Facture #44: Email invalide

Total: 2
Envoyés: 2
Erreurs: 0
```

### 3. Planifier en Cron (Linux/Mac)

```bash
crontab -e
```

Ajouter :
```cron
# Tous les jours à 9h
0 9 * * * cd /var/www/html/saas && /usr/bin/php bin/console app:payment:send-reminders >> logs/reminder.log 2>&1
```

---

## Phase 6 : Filtrage Avancé (5 min)

### Intégrer dans DevisController

```php
// Dans la méthode index()
$form = $this->createForm(AdvancedFilterType::class);
$form->handleRequest($request);

$qb = $devisRepository->createQueryBuilder('d')
    ->where('d.company = :company')
    ->setParameter('company', $company);

if ($form->isSubmitted() && $form->isValid()) {
    $data = $form->getData();
    
    // Utiliser les filters
    $this->addCreatedAtFilter($qb, 'd', $data['startDate'] ?? null, $data['endDate'] ?? null);
    $this->addStatusFilter($qb, 'd', $data['status'] ?? null);
    $this->addClientFilter($qb, 'd', $data['client']?->getId());
    $this->addAmountFilter($qb, 'd', $data['minAmount'] ?? null, $data['maxAmount'] ?? null);
}

$devis = $qb->getQuery()->getResult();

return $this->render('devis/index.html.twig', [
    'form' => $form->createView(),
    'devis' => $devis,
]);
```

**À faire pour chaque contrôleur :** InvoiceController, OrderController, etc.

---

## Phase 7 : Templates Documents (10 min)

### Routes disponibles

```
GET  /templates-documents/              → Liste
GET  /templates-documents/new           → Créer
GET  /templates-documents/{id}/edit     → Éditer
POST /templates-documents/{id}          → Supprimer
```

### Utiliser un template dans PdfController

```php
// Dans PdfController.php
public function generateFromTemplate(Devis $devis, DocumentTemplateRepository $tpl)
{
    $template = $tpl->getDefaultTemplate($devis->getCompany(), 'devis');
    
    if (!$template) {
        // Fallback sur le template par défaut
        $htmlContent = $this->renderView('pdf/devis_template.html.twig', ['devis' => $devis]);
    } else {
        // Utiliser le template sauvegardé
        $htmlContent = $this->renderTemplate($template->getHtmlContent(), ['devis' => $devis]);
    }
    
    // Générer PDF avec PdfGeneratorService
    return $this->pdfService->generate($htmlContent, 'devis.pdf');
}
```

---

## ⚡ TESTS RAPIDES

### Test 1 : Mode Sombre
```
1. Ouvrir l'app
2. Cliquer sur le bouton 🌙
3. Les couleurs doivent s'inverser
4. Rafraîchir la page → mode conservé
```

### Test 2 : Responsive Mobile
```
1. F12 (DevTools)
2. Cliquer sur "Toggle device toolbar" 
3. Vérifier sur iPhone SE (375px)
4. Tableaux convertis en "cards"
5. Boutons full-width
```

### Test 3 : Dashboard KPIs
```
1. Aller à /dashboard
2. Vérifier CA Mensuel > 0
3. Vérifier Factures Impayées affichées
```

### Test 4 : Rappels Paiement
```
1. bin/console app:payment:send-reminders
2. Vérifier les logs
3. Chercher un email dans le dossier de mail de test
```

### Test 5 : Templates
```
1. Aller à /templates-documents/
2. Créer un nouveau modèle
3. Ajouter du HTML/CSS
4. Mettre en défaut
```

---

## 🔒 SÉCURITÉ

### Vérifier les Voters

Les contrôleurs doivent utiliser les voters pour les opérations sensibles :

```php
// Vérifier les permissions
$this->denyAccessUnlessGranted('INVOICE_EDIT', $invoice);
$this->denyAccessUnlessGranted('ORDER_DELETE', $order);
$this->denyAccessUnlessGranted('PRODUCT_VIEW', $product);
```

---

## 📊 MONITORING

### Activer les logs

```yaml
# config/packages/monolog.yaml
monolog:
    handlers:
        reminder:
            type: stream
            path: '%kernel.logs_dir%/reminder.log'
            level: info
```

### Vérifier les logs

```bash
tail -f var/log/reminder.log
```

---

## 🆘 TROUBLESHOOTING

### Les migrations échouent
```bash
bin/console doctrine:migrations:migrate --no-interaction
# Ou clear cache
rm -rf var/cache/*
```

### Mode sombre ne marche pas
- Vérifier que le fichier CSS est chargé
- Vérifier que le JS est chargé
- Ouvrir DevTools → Application → localStorage

### Rappels ne s'envoient pas
- Vérifier `MAILER_DSN` dans `.env`
- Tester : `bin/console debug:container mailer`
- Vérifier les logs

### Templates ne s'affichent pas
- Vérifier que `DocumentTemplate` est créée en base
- `bin/console doctrine:schema:validate`

---

## ✅ CHECKLIST FINALE

- [ ] Migrations exécutées
- [ ] CSS/JS importés dans base.html.twig
- [ ] KPIs affichés sur le dashboard
- [ ] Commande rappels testée
- [ ] Cron job planifié
- [ ] Filtrage implémenté dans contrôleurs
- [ ] Templates documents accessibles
- [ ] Mode sombre fonctionnel
- [ ] Responsive mobile validé
- [ ] Tests unitaires en place

---

**Temps total estimé : 30 min ⏱️**

Besoin d'aide ? Consulte RAPPORT_LIVRAISON.md pour plus de détails 📖
