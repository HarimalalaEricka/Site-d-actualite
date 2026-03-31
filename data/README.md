# Système de Comptage des Vues

Ce système utilise un fichier JSON pour stocker les vues d'articles au lieu de surcharger la base de données à chaque consultation.

## Architecture

```
Article View (HTTP Request)
    ↓
article-view.php
    ↓
ViewCounterController::incrementViewCount()
    ↓
data/view_counts.json (Increment in-memory)
    ↓
Display with current view count (from JSON)

[Chaque heure via Cron Job]
    ↓
sync_views.php
    ↓
ViewCounterController::syncViewsToDatabase()
    ↓
UPDATE Article SET nbr_vues = nbr_vues + count
    ↓
Reset JSON counters
```

## Configuration

### Option 1: Cron Job (Recommandé)

Ajouter cette ligne à votre crontab (`crontab -e`) :

```bash
# Synchronise les vues toutes les heures
0 * * * * php /var/www/html/data/sync_views.php
```

Pour synchroniser **toutes les 30 minutes** :
```bash
*/30 * * * * php /var/www/html/data/sync_views.php
```

### Option 2: Endpoint HTTP (Optionnel)

Appelez cet endpoint via une requête HTTP :

```bash
curl "http://localhost/sync-views.php?token=YOUR_SYNC_TOKEN"
```

**Important** : Définissez la variable `SYNC_TOKEN` dans un fichier `.env` ou modifiez le token par défaut dans `public/sync-views.php`.

## Fichiers

| Fichier | Localisation | Rôle |
|---------|--------------|------|
| `ViewCounterController.php` | `app/Controllers/Front/` | Gestion des vues (JSON + BD) |
| `view_counts.json` | `data/` | Stockage en-mémoire des vues (temporaire) |
| `sync_views.php` | `data/` | Script CLI pour sync (cron job) |
| `article-view.php` | `public/` | Entrée des articles (incrémente vues) |
| `sync-views.php` | `public/` | Endpoint HTTP pour sync manuelle (optionnel) |

## Avantages

✅ **Zéro charge BD** pendant les pics de trafic  
✅ **Scalabilité** - Peut gérer des millions de vues  
✅ **Performance** - Affichage instantané depuis le JSON  
✅ **Flexibilité** - Sync horaire ou à la demande  

## Flux détaillé

### 1. Visite d'un article

1. User accède : `/fr/categorie/article/2026/03/31/123-titre.html`
2. `article-view.php` reçoit la requête
3. `ViewCounterController::incrementViewCount(123)` incrémente le JSON
4. L'article est affiché avec le compteur depuis le JSON (stats actives)

### 2. Synchronisation horaire

1. Cron job exécute `sync_views.php`
2. `syncViewsToDatabase()` lit les compteurs du JSON
3. Pour chaque article : `UPDATE Article SET nbr_vues = nbr_vues + count`
4. Reset des compteurs JSON à 0
5. Prochaine heure : nouveau cycle

## Exemple de JSON

```json
{
  "1": {
    "count": 156,
    "last_sync": "2026-03-31 14:00:00"
  },
  "2": {
    "count": 42,
    "last_sync": "2026-03-31 14:00:00"
  },
  "5": {
    "count": 203,
    "last_sync": "2026-03-31 14:00:00"
  }
}
```

## Dépannage

### Le JSON ne se crée pas ?
→ Vérifiez les permissions sur le dossier `data/`

### Les vues ne se synchronisent pas ?
→ Vérifiez que le cron job s'exécute ou appelez manuellement `/sync-views.php?token=...`

### Vues disparues après sync ?
→ Normal ! Les compteurs sont remis à 0 après chaque sync. Les vues finales sont dans la BD.
