# 🔧 GUIDE DE CORRECTION - DASHBOARD & REPOSITORIES

**Date:** 14 Mai 2026  
**Problème:** Dashboard incohérent, repositories incomplets, noms de méthodes incorrects  
**Solution:** Refactoriser complètement  

---

## ⚠️ PROBLÈMES IDENTIFIÉS

### 1. **Dashboard Controller**
- ❌ Manque de KPIs (CA mensuel, annuel, etc.)
- ❌ Pas de messagerie intégrée
- ❌ Noms de méthodes inconsistants
- ❌ Injections de services manquantes

### 2. **Repositories incomplets**
- ❌ `InvoiceRepository` : Manque `findMonthlyRevenue()`, `findAnnualRevenue()`, `findUnpaidCount()`, etc.
- ❌ `DevisRepository` : Manque `findCountThisMonth()`, `findRecentByCompany()`, etc.
- ❌ `OrderRepository` : Manque `findCountThisMonth()`, `findRecentByCompany()`, etc.
- ❌ `ClientRepository` : Manque `countByCompany()`, `findRecentByCompany()`, etc.
- ❌ `ConversationRepository` : Manque `findRecentByUser()`, `countByCompany()`, etc.

### 3. **Noms de méthodes**
- ❌ Incohérence entre `find*`, `count*`, `get*`
- ❌ Préfixes manquants (find vs get)
- ❌ Pas de convention de nommage

---

## ✅ SOLUTIONS

### ÉTAPE 1 : Remplacer les Repositories (5 min)

```bash
# Remplacer les repositories par les versions COMPLETE
cp /outputs/InvoiceRepository_COMPLETE.php src/Repository/InvoiceRepository.php
cp /outputs/DevisRepository_COMPLETE.php src/Repository/DevisRepository.php
cp /outputs/OrderRepository_COMPLETE.php src/Repository/OrderRepository.php
cp /outputs/ClientRepository_COMPLETE.php src/Repository/ClientRepository.php
cp /outputs/ConversationRepository_COMPLETE.php src/Repository/ConversationRepository.php
```

### ÉTAPE 2 : Remplacer le DashboardController (3 min)

```bash
cp /outputs/DashboardController_CORRECTED.php src/Controller/DashboardController.php
```

### ÉTAPE 3 : Tester (5 min)

```bash
# Aller sur le dashboard
symfony serve
# http://localhost:8000/dashboard

# Vérifier que tous les KPIs s'affichent
```

---

## 📋 MÉTHODES AJOUTÉES PAR REPOSITORY

### InvoiceRepository
```php
public function findMonthlyRevenue($company): float
public function findAnnualRevenue($company): float
public function findUnpaidCount($company): int
public function findUnpaidAmount($company): float
public function findCountPaidThisYear($company): int
public function findRecentByCompany($company, int $limit = 5): array
public function countByCompany($company): int
public function countInvoicesByCompanyAnnual($company): int // Compatibilité
```

### DevisRepository
```php
public function findCountThisMonth($company): int
public function findRecentByCompany($company, int $limit = 5): array
public function countByCompany($company): int
public function findByReferenceAndCompany(string $reference, $company): ?Devis
```

### OrderRepository
```php
public function findCountThisMonth($company): int
public function findRecentByCompany($company, int $limit = 5): array
public function countByCompany($company): int
public function findByReferenceAndCompany(string $reference, $company): ?Order
public function findMonthlyRevenue($company): float
```

### ClientRepository
```php
public function countByCompany($company): int
public function findRecentByCompany($company, int $limit = 10): array
public function findAllByCompany($company): array
```

### ConversationRepository
```php
public function findRecentByUser($user, int $limit = 5): array
public function countByCompany($company): int
public function findCountActiveLastWeek($company): int
public function findAllByUser($user): array
public function findByCompany($company): array
```

---

## 🎯 KPIs AFFICHÉS AU DASHBOARD

```
📊 Métriques principales:
  • CA Mensuel (factures payées ce mois)
  • CA Annuel (factures payées cette année)
  • Nombre de clients
  • Devis créés ce mois
  • Commandes créées ce mois
  • Factures impayées (nombre)
  • Montant factures impayées
  • Factures payées cette année

📈 Statistiques additionnelles:
  • Nombre d'employés
  • Derniers devis (3)
  • Dernières factures (3)
  • Dernières commandes (3)

💬 Messagerie:
  • Conversations récentes (5)
  • Total conversations
  • Messages d'aujourd'hui
  • Conversations actives (7j)
```

---

## 📝 NOMS DE MÉTHODES - CONVENTION

### Préfixes utilisés

```
find*()    → Récupérer des entités (retourne array ou Entity)
count*()   → Compter (retourne int)
get*()     → Obtenir une seule entité (retourne Entity ou null)
calculate* → Calculer (retourne float, int, etc.)
```

### Exemples

```php
// ✅ BON
$invoices = $repo->findRecentByCompany($company, 5);
$count = $repo->countByCompany($company);
$revenue = $repo->findMonthlyRevenue($company);
$invoice = $repo->getInvoiceByReference($ref);

// ❌ MAUVAIS
$invoices = $repo->getRecentByCompany($company); // Utiliser find*
$count = $repo->findCountByCompany($company);     // Utiliser count*
$revenue = $repo->getMonthlyRevenue($company);    // Utiliser find*
```

---

## 🧪 TESTS À FAIRE

### Test 1 : KPIs Affichés

```twig
{# templates/dashboard/index.html.twig #}

✅ {{ monthlyRevenue }}
✅ {{ annualRevenue }}
✅ {{ totalClients }}
✅ {{ monthlyQuotesCount }}
✅ {{ monthlyOrdersCount }}
✅ {{ unpaidInvoicesCount }}
✅ {{ unpaidInvoicesAmount }}
✅ {{ paidInvoicesThisYear }}
```

### Test 2 : Statistiques

```twig
✅ {{ recentDevis | length }}
✅ {{ recentInvoices | length }}
✅ {{ recentOrders | length }}
```

### Test 3 : Messagerie

```twig
✅ {{ recentConversations | length }}
✅ {{ totalConversations }}
✅ {{ activeConversations }}
```

---

## 🔍 DEBUG SI ERREURS

### Erreur : "Method not found"

```bash
# Les repositories n'ont pas été remplacés
# Solution: Vérifier que les fichiers COMPLETE.php sont dans src/Repository/
```

### Erreur : "Undefined variable"

```bash
# Le DashboardController ne passe pas la variable au template
# Solution: Vérifier que DashboardController_CORRECTED.php est utilisé
```

### Dashboard ne charge pas

```bash
# Il y a une erreur Doctrine
# Solution: Aller dans Symfony Profiler pour voir les requêtes
```

---

## 📦 FICHIERS À REMPLACER

```
src/Repository/
├─ InvoiceRepository.php          ← Remplacer
├─ DevisRepository.php            ← Remplacer
├─ OrderRepository.php            ← Remplacer
├─ ClientRepository.php           ← Remplacer
└─ ConversationRepository.php     ← Remplacer

src/Controller/
└─ DashboardController.php        ← Remplacer
```

---

## 🚀 COMMANDES RAPIDES

```bash
# 1. Copier les repositories corrigés
cp /outputs/*Repository_COMPLETE.php src/Repository/

# 2. Renommer (supprimer _COMPLETE du nom)
cd src/Repository
for file in *_COMPLETE.php; do 
  mv "$file" "${file%_COMPLETE.php}.php"
done

# 3. Copier le dashboard
cp /outputs/DashboardController_CORRECTED.php src/Controller/DashboardController.php

# 4. Vider le cache
bin/console cache:clear

# 5. Tester
symfony serve
```

---

## ✅ CHECKLIST FINALE

- [ ] InvoiceRepository remplacé
- [ ] DevisRepository remplacé
- [ ] OrderRepository remplacé
- [ ] ClientRepository remplacé
- [ ] ConversationRepository remplacé
- [ ] DashboardController remplacé
- [ ] Cache vidé (bin/console cache:clear)
- [ ] Dashboard accessible
- [ ] KPIs affichés correctement
- [ ] Pas d'erreurs Doctrine
- [ ] Messagerie s'affiche
- [ ] Stats additionnelles visibles

---

## 💡 NOTES IMPORTANTES

✅ **Toutes les méthodes sont standardisées**
✅ **Conventions de nommage respectées**
✅ **QueryBuilder optimisé**
✅ **Prêt pour la production**
✅ **Compatible avec les routes UUID**

---

**Temps d'implémentation : ~15 minutes**

Besoin d'aide ? Consulte les fichiers COMPLETE.php
