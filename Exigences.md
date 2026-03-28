# 📌 Mini-Projet Web Design – Exigences 

# 🎯 1. Objectif principal

    Réaliser un site web **professionnel et optimisé**, avec :
    * une bonne structure HTML
    * un bon référencement (SEO)
    * une architecture propre
    * un projet déployable (Docker)

---

# 🌐 2. URL Rewriting (OBLIGATOIRE)

    ## But
    Avoir des URLs propres et lisibles (SEO + UX)

    ## Exemple attendu
    ❌ Mauvais :
    ```id="w1q9t0"
    /article.php?id=5
    ```

    ✅ Bon :
    ```id="f9k2lm"
    /article/5/titre-de-l-article
    ```

    ## À faire
    * Configurer `.htaccess` (Apache)
    * Activer `mod_rewrite`
    * Gérer les routes côté backend


# 🧱 3. Structure HTML (SEO)

  ## Règles importantes
    ### Titres
    * 1 seul `<h1>` par page
    * Utiliser `<h2>`, `<h3>`, etc. pour structurer

    ### Exemple
    ```html id="y6k2pz"
    <h1>Titre principal</h1>
    <h2>Section</h2>
    <h3>Sous-section</h3>
    ```

    ## Objectif
    * Aider Google à comprendre le contenu
    * Améliorer l’accessibilité

---

# 🏷️ 4. Balises META (OBLIGATOIRE)

  ## À ajouter dans chaque page
  ```html id="r8x4mq"
  <meta name="description" content="Description de la page">
  <meta name="keywords" content="actualité, iran, guerre">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  ```

  ## Importance
  * SEO
  * Affichage correct sur mobile

---

# 🖼️ 5. Images (Accessibilité + SEO)

  ## Règle obligatoire
  Chaque image doit avoir un attribut `alt`

  ### Exemple
  ```html id="a7p3dn"
  <img src="image.jpg" alt="Explosion dans une ville en Iran">
  ```

  ## Pourquoi
  * SEO (Google Images)
  * Accessibilité (lecteurs d’écran)

  ---

# 📱 6. Test Lighthouse (OBLIGATOIRE)

  ## Outil
  * Lighthouse (Chrome)

  ## Tests à faire
  * Mobile 📱
  * Desktop 💻

  ## Critères
  * Performance
  * SEO
  * Accessibilité
  * Best practices

  ## Objectif
  Avoir de bons scores (idéalement > 70)

---

# ⚡ 7. Optimisation performance

  ## À faire

  ### Cache fichiers statiques
  Configurer `.htaccess` pour :
  * CSS
  * JS
  * Images

  ### Exemple
  ```apache id="p0z8xr"
  <IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType text/css "access plus 1 week"
  </IfModule>
  ```

  ## Minification
  * CSS
  * JS

  ## Objectif
  * Charger rapidement le site
  * Améliorer Lighthouse

  ---

# 🐳 8. Docker (OBLIGATOIRE)

  ## À livrer
  * Projet fonctionnel dans Docker

  ## Conteneurs minimum
  * Serveur web (Apache / Nginx)
  * Base de données

  ## Exemple structure
  ```id="k2x9bv"
  /project
    /app
    docker-compose.yml
  ```

  ## Objectif
  * Lancer le projet facilement :
  ```bash id="u9k2zn"
  docker-compose up
  ---