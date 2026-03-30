-- ROLES
INSERT INTO Role (role) VALUES
('admin'),
('journaliste');

-- USERS
INSERT INTO User_ (email, nom, prenom, mdp, numero_tel, adresse, Id_Role) VALUES
('admin@test.com', 'Admin', 'Root', 'admin123', '0340000000', 'Antananarivo', 1),
('journaliste1@test.com', 'Dupont', 'Jean', 'test123', '0341111111', 'Paris', 2),
('journaliste2@test.com', 'Smith', 'Anna', 'test123', '0342222222', 'London', 2);

-- CATEGORIES
INSERT INTO Categorie (categorie, description) VALUES
('Politique', 'Actualités politiques'),
('Conflit', 'Conflits armés'),
('International', 'Relations internationales');

-- STATUS
INSERT INTO status_article (status) VALUES
('brouillon'),
('publie'),
('rejete'),
('retire');

-- TAGS
INSERT INTO tag (nom) VALUES
('Iran'),
('Guerre'),
('USA'),
('Missile'),
('Diplomatie');

-- TYPE MEDIA
INSERT INTO type_media (type) VALUES
('image'),
('video');

-- ARTICLES (IMPORTANT)
INSERT INTO Article (titre, date_publication, contenu, nbr_vues, Id_User_principal, Id_status_article, Id_Categorie, lang) VALUES

-- ARTICLE 1
('Tensions croissantes entre l’Iran et les États-Unis', NOW(),
'<h1>Tensions croissantes entre l’Iran et les États-Unis</h1>

<p>Les tensions entre l’Iran et les États-Unis ont atteint un niveau critique ces derniers jours. Plusieurs incidents militaires ont été signalés dans le golfe Persique.</p>

<h2>Une escalade inquiétante</h2>
<p>Selon les autorités, des missiles auraient été détectés à proximité de bases stratégiques. Les experts craignent une escalade rapide du conflit.</p>

<img src=\"/images/iran1.jpg\" alt=\"Missile iranien testé\" />

<h2>Réactions internationales</h2>
<p>La communauté internationale appelle au calme. Plusieurs pays européens demandent une désescalade immédiate.</p>

<p><strong>Conclusion :</strong> La situation reste instable et pourrait évoluer rapidement dans les prochains jours.</p>
', 120, 2, 2, 2, 'fr'),

-- ARTICLE 2
('Iran conflict: rising tensions in Middle East', NOW(),
'<h1>Iran conflict: rising tensions in Middle East</h1>

<p>The situation in the Middle East is becoming increasingly unstable as tensions rise between Iran and Western countries.</p>

<h2>Military movements detected</h2>
<p>Satellite images reveal increased military activity in strategic areas.</p>

<img src=\"/images/iran2.jpg\" alt=\"Military base in Iran\" />

<h2>Global reactions</h2>
<p>World leaders urge both sides to avoid further escalation.</p>

<p><em>The coming days will be crucial in determining the outcome of this crisis.</em></p>
', 85, 3, 2, 3, 'en'),

-- ARTICLE 3
('Négociations diplomatiques en cours', NOW(),
'<h1>Négociations diplomatiques en cours</h1>

<p>Des discussions diplomatiques sont actuellement en cours pour tenter d’éviter un conflit ouvert.</p>

<h2>Une solution pacifique possible</h2>
<p>Les diplomates espèrent trouver un terrain d’entente pour apaiser les tensions.</p>

<img src=\"/images/diplomatie.jpg\" alt=\"Réunion diplomatique\" />

<p><strong>Un accord pourrait être signé dans les prochains jours.</strong></p>
', 45, 2, 1, 1, 'fr');

-- MEDIA
INSERT INTO Media (url, description, priorite, Id_type_media, Id_Article) VALUES
('/images/iran1.jpg', 'Missile iranien', TRUE, 1, 1),
('/images/iran2.jpg', 'Base militaire', TRUE, 1, 2),
('/images/diplomatie.jpg', 'Réunion diplomatique', TRUE, 1, 3);

-- ARTICLE TAG
INSERT INTO article_tag (Id_Article, Id_tag) VALUES
(1, 1),
(1, 2),
(1, 4),
(2, 1),
(2, 3),
(3, 5);

-- COLLABORATION
INSERT INTO collaboration (Id_User, Id_Article) VALUES
(2, 1),
(3, 2),
(2, 3);

-- HISTORIQUE PUBLICATION
INSERT INTO hito_publication (date_, action, Id_Article, Id_User) VALUES
(NOW(), 'creation', 1, 2),
(NOW(), 'publication', 1, 1),
(NOW(), 'creation', 2, 3);

-- HISTO STATUS
INSERT INTO histo_status (Id_Article, Id_status_article, date_) VALUES
(1, 2, NOW()),
(2, 2, NOW()),
(3, 1, NOW());