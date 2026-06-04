# 📧 GUIDE MESSAGERIE AMÉLIORÉE - SHORELINE HAZE

## 🎯 OBJECTIF

Transformer la messagerie actuelle (basée sur TailwindCSS) en design cohérent avec Shoreline Haze et l'intégrer au dashboard.

---

## ✅ CHANGEMENTS EFFECTUÉS

### 1. **Templates reformatés** (Shoreline Haze)
- ✅ `templates/conversation/index.html.twig` → Design de liste moderne
- ✅ `templates/conversation/show.html.twig` → Interface chat Shoreline Haze
- ✅ `templates/message/_list.html.twig` → Partial pour messages

### 2. **Services créés**
- ✅ `MessagingStatisticsService.php` → Stats de messagerie
- ✅ Repositories améliorés → Requêtes optimisées

### 3. **Dashboard intégré**
- ✅ Widget messagerie dans le dashboard
- ✅ Stats : conversations, messages aujourd'hui, conversations actives

---

## 🚀 ÉTAPES D'IMPLÉMENTATION

### Phase 1 : Copier les templates (5 min)

```bash
# Sauvegarder les anciens
mv templates/conversation/index.html.twig templates/conversation/index.html.twig.old
mv templates/conversation/show.html.twig templates/conversation/show.html.twig.old

# Copier les nouveaux
cp conversation_index_new.html.twig templates/conversation/index.html.twig
cp conversation_show_new.html.twig templates/conversation/show.html.twig
cp message_list_partial.html.twig templates/message/_list.html.twig
```

### Phase 2 : Créer le service de messagerie (3 min)

```bash
cp MessagingStatisticsService.php src/Services/
```

### Phase 3 : Améliorer les repositories (5 min)

```bash
# Fusionner avec vos repositories existants
# Ajouter les nouvelles méthodes à :
# src/Repository/ConversationRepository.php
# src/Repository/MessageRepository.php
```

Méthodes à ajouter au `ConversationRepository` :
```php
public function findAllByUser($user): array
public function findRecentByUser($user, int $limit = 5): array
public function countByCompany(Company $company): int
public function countActiveByCompany(Company $company, \DateTime $since): int
public function countUnreadByUser($user): int
```

Méthodes à ajouter au `MessageRepository` :
```php
public function countByCompany(Company $company): int
public function countByCompanyAndDate(Company $company, \DateTime $date): int
public function findByConversation($conversation): array
```

### Phase 4 : Mettre à jour le DashboardController (5 min)

Remplacer le `DashboardController` existant par la version avec messagerie.

**Ou fusionner manuellement :**

```php
// Dans DashboardController::index()

// Injecter les services
private MessagingStatisticsService $messagingService,

// Ajouter avant le return
$recentConversations = $messagingService->getRecentConversations($user, 5);
$messagingStats = $messagingService->getMessagingStats($company);

// Passer au render
"recentConversations" => $recentConversations,
"messagingStats" => $messagingStats,
"messagingService" => $messagingService,
```

### Phase 5 : Ajouter le widget au dashboard (3 min)

Dans `templates/dashboard/index.html.twig`, ajouter la grille :

```twig
<div class="row mt-4">
    <!-- Colonne gauche : KPIs -->
    <div class="col-md-8">
        {# KPIs existants #}
    </div>

    <!-- Colonne droite : Messagerie -->
    <div class="col-md-4">
        {% include 'dashboard/_messaging_widget.html.twig' %}
    </div>
</div>
```

### Phase 6 : Tester (10 min)

```bash
# 1. Aller sur /dashboard
# 2. Vérifier le widget messagerie s'affiche
# 3. Cliquer sur une conversation
# 4. Vérifier le design Shoreline Haze
# 5. Envoyer un message
```

---

## 🎨 FEATURES AJOUTÉES

### ✨ Nouveaux éléments UI

#### Liste des conversations
```
┌─────────────────────────────────────┐
│ 💬 Messagerie        [Voir tout]    │
├─────────────────────────────────────┤
│ [user1] [user2]                     │
│ user1: Bonjour, comment ça va...    │
│                      14/05 14:30  5  │
├─────────────────────────────────────┤
│ 15 conversations | 342 messages      │
└─────────────────────────────────────┘
```

#### Conversation (Chat)
```
┌─────────────────────────────────────┐
│ Conversation    [user1][user2]      │
├─────────────────────────────────────┤
│                                     │
│               Moi: Hello!   14:30   │
│ User1: Hi, how are you?     14:31   │
│                                     │
│ [Votre message...] [📤 Envoyer]    │
└─────────────────────────────────────┘
```

### 🎯 Design caractéristiques

- **Couleurs** : Palette Shoreline Haze (bleu/crème)
- **Typo** : Cormorant Garamond (titres) + Raleway (body)
- **Mode sombre** : Supporté via prefers-color-scheme
- **Responsive** : Mobile-friendly (tableaux cards, etc.)
- **Animations** : Smooth scrolling, slide-in messages

---

## 📊 WIDGET DASHBOARD

### Stats affichées
```
Conversations     Messages (auj)    Actives (7j)
     15                42               8
```

### Conversations récentes
- Affiche 5 dernières conversations
- Aperçu du dernier message
- Noms des participants
- Heure relative (il y a 2h, etc.)

---

## 🔧 CONFIGURATIONS OPTIONNELLES

### Ajouter un Twig Filter pour "format_ago"

Si vous n'avez pas de filtre pour "il y a X heures" :

```php
// src/Twig/FormatAgoExtension.php
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class FormatAgoExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('format_ago', [$this, 'formatAgo']),
        ];
    }

    public function formatAgo(\DateTimeInterface $date): string
    {
        $now = new \DateTime();
        $interval = $now->diff($date);

        if ($interval->y > 0) return $interval->y . 'a';
        if ($interval->m > 0) return $interval->m . 'mo';
        if ($interval->d > 0) return $interval->d . 'd';
        if ($interval->h > 0) return $interval->h . 'h';
        if ($interval->i > 0) return $interval->i . 'min';
        
        return 'now';
    }
}
```

### Ajouter un Voter pour Conversation (Sécurité)

```php
// src/Security/Voter/ConversationVoter.php
namespace App\Security\Voter;

use App\Entity\Conversation;
use App\Entity\User;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ConversationVoter extends Voter
{
    public const VIEW = 'CONVERSATION_VIEW';
    public const EDIT = 'CONVERSATION_EDIT';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT])
            && $subject instanceof Conversation;
    }

    protected function voteOnAttribute(string $attribute, mixed $conversation, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        return match ($attribute) {
            self::VIEW => $conversation->getUsers()->contains($user),
            self::EDIT => $conversation->getCompany() === $user->getCompany(),
            default => false,
        };
    }
}
```

---

## 📱 RESPONSIVE MOBILE

Le design s'adapte automatiquement :
- < 768px : Widget pleine largeur
- Messages : 90% largeur max
- Formulaire : Full-width avec padding
- Boutons : Optimisés pour touch

---

## 🔍 TESTER LES CHANGEMENTS

### Test 1 : Liste des conversations
```
1. Va sur /conversation
2. Vérifie que le design est Shoreline Haze
3. Clique sur une conversation
```

### Test 2 : Conversation (Chat)
```
1. Envoie un message
2. Vérifie le scroll auto-bottom
3. Vérifie les timestamps
```

### Test 3 : Dashboard widget
```
1. Va sur /dashboard
2. Vérifie le widget messagerie
3. Clique sur une conversation récente
```

### Test 4 : Mode sombre
```
1. Ouvre DevTools
2. Simule prefers-color-scheme: dark
3. Vérifie les couleurs
```

### Test 5 : Mobile responsive
```
1. F12 → Device toolbar → iPhone SE
2. Vérifie le layout
3. Envoie un message
```

---

## 🐛 TROUBLESHOOTING

| Problème | Solution |
|----------|----------|
| Widget ne s'affiche pas | Vérifier que `MessagingStatisticsService` est injectée |
| Messages pas scrollé au bottom | Vérifier le JS dans le template |
| Design pas Shoreline Haze | Vérifier variables CSS importées dans base.html.twig |
| Dark mode ne marche pas | Vérifier @media (prefers-color-scheme: dark) |
| Responsive ne fonctionne pas | Vérifier les media queries dans dark-mode-responsive.css |

---

## 📈 STATISTIQUES

```
Templates modifiés:     3 fichiers
Services créés:         1 (MessagingStatisticsService)
Repositories améliorés:  2 (Conversation, Message)
Dashboard modifié:      1 (+ 7 KPIs + widget)
Lignes CSS ajoutées:    150+
Lignes JS ajoutées:     50+
```

---

## 🎁 BONUS : Amélioration futures

- [ ] Notifications real-time avec Mercure
- [ ] Typage des messages (images, emojis)
- [ ] Indicateur "En train d'écrire..."
- [ ] Marquage comme "lu"
- [ ] Archivage des conversations
- [ ] Recherche dans les messages

---

**Implémentation complète en ~30 minutes ⏱️**

Besoin d'aide ? Les fichiers sont dans `/outputs/`
