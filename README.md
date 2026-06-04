# 🚀 SASS SYMFONY - GESTION COMMERCIALE AVANCÉE

**Version:** 2.0  
**Date:** 14 Mai 2026  
**Status:** ✅ Production Ready  

---

## 📋 CONTENU DU PROJET

Ce projet Symfony complet inclut :

### ✨ Fonctionnalités principales
- ✅ Dashboard avec KPIs (CA, clients, factures impayées)
- ✅ Gestion des Devis, Commandes, Factures
- ✅ Routes dynamiques avec UUID de la compagnie
- ✅ Export PDF (A4 avec/sans prix)
- ✅ Envoi par email
- ✅ Rappels de paiement automatiques (CRON)
- ✅ Filtrage avancé (5 critères)
- ✅ Modèles de documents (CRUD)
- ✅ Messagerie refactorisée (Shoreline Haze)
- ✅ Mode sombre + Responsive mobile

### 🎨 Design moderne
- ✅ Shoreline Haze (palette océanique cohérente)
- ✅ Typo : Cormorant Garamond + Raleway
- ✅ Dark mode automatique
- ✅ Responsive (375px, 768px, 1024px, 1440px)
- ✅ Animations fluides

### 🔒 Sécurité
- ✅ Voters (5 : Company, Devis, Invoice, Order, Product)
- ✅ Multi-tenant (séparation par Company)
- ✅ CSRF protection
- ✅ Validation entités (Assert)

---

## 🛠️ INSTALLATION

### 1. Prérequis
```bash
PHP 8.1+
MySQL 8.0+
Composer
Node.js + npm (optionnel pour assets)
```

### 2. Installation du projet

```bash
# Cloner/Extraire le projet
cd sass-symfony

# Installer les dépendances PHP
composer install

# Créer le fichier .env.local
cp .env .env.local

# Configurer la base de données dans .env.local
DATABASE_URL="mysql://user:password@localhost:3306/sass_db"

# Créer la base de données
bin/console doctrine:database:create

# Exécuter les migrations
bin/console doctrine:migrations:migrate

# Créer un utilisateur de test (optionnel)
bin/console make:user
```

### 3. Démarrer le serveur

```bash
# Mode développement
symfony serve

# Ou avec PHP natif
php -S 127.0.0.1:8000 -t public

# Accéder à l'app
http://localhost:8000
```

---

## 📚 DOCUMENTATION INCLUSE

### Pour commencer (dans `/docs/`)
- `LIRE_MOI_D_ABORD.md` - Point de départ
- `SYNTHESE_COMPLETE_PROJET.md` - Vue d'ensemble
- `INDEX.md` - Navigation

### Pour implémenter
- `GUIDE_IMPLEMENTATION.md` - 6 phases
- `CHECKLIST_INSTALLATION.txt` - 16 tests
- `PHASE_5_AVANCEE_COMPLET.md` - Routes UUID, PDF export

### Pour les fonctionnalités
- `RAPPORT_LIVRAISON.md` - Rapport officiel
- `MESSAGERIE_GUIDE_COMPLET.md` - Messagerie
- `MESSAGERIE_RESUME.md` - Résumé messagerie

---

## 🚀 FONCTIONNALITÉS CLÉS

### 1. Routes dynamiques avec UUID

**Pattern:** `/devis/{companyUuid}/{devisReference}`

```
GET    /devis/{companyUuid}                      → Liste devis
GET    /devis/{companyUuid}/{devisReference}     → Voir devis
POST   /devis/{companyUuid}/new                  → Créer
GET    /devis/{companyUuid}/{devisRef}/edit      → Éditer
POST   /devis/{companyUuid}/{devisRef}/delete    → Supprimer
GET    /devis/{companyUuid}/{devisRef}/export-pdf           → PDF
GET    /devis/{companyUuid}/{devisRef}/export-pdf-no-prices → PDF sans prix
POST   /devis/{companyUuid}/{devisRef}/send-email           → Email
```

**Même pattern pour:**
- `/invoice/{companyUuid}/{invoiceReference}`
- `/order/{companyUuid}/{orderReference}`
- `/products/{companyUuid}`

### 2. Export PDF + Email

```php
// Contrôleur
$service = new DocumentExportService($twig, $mailer);

// Générer PDF (avec prix)
$pdfPath = $service->generateDevisPDF($devis, withPrices: true);

// Générer PDF sans prix
$pdfPath = $service->generateDevisPDF($devis, withPrices: false);

// Envoyer par email
$service->sendDevisByEmail($devis, 'client@example.com', withPrices: true);
```

### 3. Suppression en cascade

```php
// Deleting Devis → Supprime automatiquement DevisDetails
// Deleting Order → Supprime automatiquement OrderDetails
// Deleting Invoice → Supprime automatiquement InvoiceDetails
// Products ne sont JAMAIS supprimés
```

### 4. Dashboard avec KPIs

```
📊 Métriques affichées:
• CA Mensuel
• CA Annuel
• Nombre de clients
• Devis ce mois
• Commandes ce mois
• Factures impayées
• Widget messagerie (5 conversations)
```

### 5. Rappels de paiement auto

```bash
# Lancer manuellement
bin/console app:payment:send-reminders

# Planifier en CRON (tous les jours à 9h)
0 9 * * * cd /app && /usr/bin/php bin/console app:payment:send-reminders
```

---

## 📊 STRUCTURE DU PROJET

```
sass-symfony/
├── bin/                          # Exécutables
├── config/                       # Configuration Symfony
│   ├── packages/                 # Config des bundles
│   └── routes.yaml               # Routes
├── migrations/                   # Migrations Doctrine
├── public/                       # Assets publics
│   ├── assets/
│   │   ├── styles/              # CSS (Shoreline Haze)
│   │   └── js/                  # JavaScript
│   └── index.php                # Entrée point
├── src/
│   ├── Command/                 # Commandes console
│   ├── Controller/              # Contrôleurs (DevisController, etc.)
│   ├── Entity/                  # Entités (Devis, Invoice, Order, etc.)
│   ├── Form/                    # Types de formulaires
│   ├── Repository/              # Repositories
│   ├── Security/                # Voters
│   ├── Services/                # Services (DocumentExportService, etc.)
│   └── Twig/                    # Extensions Twig
├── templates/                    # Templates Twig
│   ├── base.html.twig           # Layout principal
│   ├── devis/                   # Templates devis
│   ├── invoice/                 # Templates factures
│   ├── order/                   # Templates commandes
│   ├── dashboard/               # Templates dashboard
│   ├── exports/                 # Templates PDF
│   └── emails/                  # Templates emails
├── var/
│   ├── cache/                   # Cache
│   └── log/                     # Logs
├── .env                         # Configuration (à ne pas committer)
├── .env.local                   # Configuration locale
├── composer.json                # Dépendances PHP
├── composer.lock                # Lock file
├── symfony.lock                 # Symfony lock
└── README.md                    # Ce fichier
```

---

## 🗄️ BASE DE DONNÉES

### Entités principales
```
Company
├── User (1:N)
├── Client (1:N)
├── Product (1:N)
├── Devis (1:N)
│   └── DevisDetails (1:N)
├── Invoice (1:N)
│   └── InvoiceDetails (1:N)
├── Order (1:N)
│   └── OrderDetails (1:N)
├── Conversation (1:N)
├── DocumentTemplate (1:N)
└── Message (User -> Conversation)
```

### UUID
- **Company.uuid** (Uuid) - Généré automatiquement au création
- Utilisé dans les routes pour sécurité

---

## 🎨 DESIGN & CSS

### Variables CSS (Shoreline Haze)
```css
--deep-marine: #1a2f36        /* Headers *)
--cloudy-ocean: #4a7a8a       /* Accents *)
--sky-shell: #6b9aaa          /* Badges *)
--sunlit-sand: #7a7060        /* Texte 2ndaire *)
--cream: #faf5f0              /* Backgrounds *)
```

### Responsive
```
< 768px   : Mobile (1 colonne)
768-1024px : Tablette (2 colonnes)
> 1024px   : Desktop (3-4 colonnes)
```

### Mode sombre
```css
@media (prefers-color-scheme: dark) {
    /* Couleurs inversées auto-appliquées */
}
```

---

## 🔐 SÉCURITÉ

### Authentification
- Login/Register standard
- Mot de passe hashé (bcrypt)
- Sessions sécurisées

### Autorisation
- **CompanyResourceVoter** : Multi-tenant (vérifier company)
- **DevisVoter** : Voir/Éditer/Supprimer devis
- **InvoiceVoter** : Voir/Éditer (non payée)/Supprimer (non payée)
- **OrderVoter** : Voir/Éditer/Supprimer commandes
- **ProductVoter** : Voir/Éditer/Supprimer produits

### CSRF
- Protection automatique sur tous les formulaires
- Tokens générés et vérifiés

---

## 📧 CONFIGURATION EMAIL

### .env.local
```env
MAILER_DSN=smtp://username:password@smtp.gmail.com:587
# Ou
MAILER_DSN=sendmail://default
```

### Templates emails
```
templates/emails/
├── payment_reminder.html.twig       # Rappel paiement
├── devis_email.html.twig            # Envoi devis
├── invoice_email.html.twig          # Envoi facture
└── order_email.html.twig            # Envoi commande
```

---

## 🐛 TROUBLESHOOTING

### "Base de données non trouvée"
```bash
bin/console doctrine:database:create
bin/console doctrine:migrations:migrate
```

### "Classe non trouvée"
```bash
composer dump-autoload
```

### "Permission refusée" (Devis/Invoice)
```php
// Utiliser les Voters correctement
$this->denyAccessUnlessGranted('DEVIS_VIEW', $devis);
```

### "UUID invalide dans la route"
```php
// Vérifier que Company.uuid existe en DB
bin/console doctrine:migrations:migrate
```

### "PDF ne s'affiche pas"
```bash
# Installer Dompdf
composer require dompdf/dompdf
```

---

## 📞 SUPPORT

Tous les fichiers de documentation sont inclus dans le ZIP :
- `/docs/` - Toute la documentation
- `README.md` - Ce fichier

**Besoin d'aide ?**
1. Consulte le document correspondant dans `/docs/`
2. Vérifie la CHECKLIST_INSTALLATION.txt
3. Regarde TROUBLESHOOTING plus haut

---

## 🚢 DÉPLOIEMENT PRODUCTION

### Optimisations
```bash
# Mode production
APP_ENV=prod

# Dump autoload
composer dump-autoload --optimize

# Réchauffer le cache
bin/console cache:warmup --env=prod

# Minifier CSS/JS (optionnel)
npm run build
```

### Sécurité
```bash
# Mettre à jour les dépendances
composer update

# Vérifier les vulnérabilités
composer audit
```

### CRON
```bash
# Ajouter la tâche de rappels
0 9 * * * cd /var/www/html/app && /usr/bin/php bin/console app:payment:send-reminders >> logs/reminder.log 2>&1
```

---

## 📈 ROADMAP FUTURES

- [ ] Portail client (consultation devis/factures)
- [ ] API REST (pour tiers-intégrations)
- [ ] Analytics avancées (graphiques revenue)
- [ ] Synchronisation bancaire
- [ ] Notifications real-time (Mercure)
- [ ] Import/Export CSV

---

## 📄 LICENCE

Propriétaire - Tous droits réservés

---

## 👤 AUTEUR

**Nicolas Palay** - Symfony Developer  
*Projet généré le 14 Mai 2026*

---

## ✅ CHECKLIST POST-INSTALLATION

- [ ] Base de données créée et migrée
- [ ] Utilisateur de test créé
- [ ] Dashboard accessible
- [ ] KPIs affichés
- [ ] Routes UUID fonctionnelles
- [ ] PDF export fonctionnel
- [ ] Email configuration testée
- [ ] Mode sombre actif
- [ ] Mobile responsive
- [ ] Rappels CRON planifiés

---

**Prêt pour la production ! 🚀**
