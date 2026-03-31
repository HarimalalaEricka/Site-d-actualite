USE site_actus;

-- Roles
INSERT INTO Role (role) VALUES ('admin'), ('journaliste');

-- Utilisateurs
INSERT INTO User_ (email, nom, prenom, mdp, numero_tel, adresse, Id_Role) VALUES
('alice@example.com', 'Durand', 'Alice', 'mdp1', '0600000001', '1 rue de Paris', 2),
('bob@example.com', 'Martin', 'Bob', 'mdp2', '0600000002', '2 rue de Lyon', 2),
('carol@example.com', 'Bernard', 'Carol', 'mdp3', '0600000003', '3 rue de Lille', 2),
('admin@example.com', 'Admin', 'Super', 'adminpass', '0600000000', '4 rue de Bordeaux', 1);

-- Categories
INSERT INTO Categorie (categorie, description) VALUES
('Politique', 'Actualite politique'),
('Economie', 'Actualite economique'),
('Culture', 'Culture et loisirs'),
('Sport', 'Actualite sportive');

-- Types de media
INSERT INTO type_media (type) VALUES ('image'), ('video'), ('audio');

-- Tags
INSERT INTO tag (nom) VALUES ('France'), ('Europe'), ('Elections'), ('Covid'), ('Climat'), ('Iran');

-- Statuts d'article
INSERT INTO status_article (status) VALUES ('brouillon'), ('publie'), ('archive');

-- Articles (5 exemples)
INSERT INTO Article (titre, date_publication, contenu, nbr_vues, Id_User_principal, Id_status_article, Id_Categorie, lang) VALUES
('Offensive majeure a Teheran', '2026-03-25 14:00:00', '<p>Les forces alliees lancent une offensive sur la capitale iranienne. Les combats font rage dans plusieurs quartiers.</p>', 34, 2, 2, 1, 'fr'),
('Bombardements dans le sud de l''Iran', '2026-03-24 18:00:00', '<p>Des frappes aeriennes ont touche la region de Shiraz, provoquant de nombreux deplacements de population.</p>', 21, 3, 2, 1, 'fr'),
('Crise humanitaire : temoignages de refugies', '2026-03-23 09:30:00', '<p>Des milliers d''Iraniens fuient les zones de combat. Les ONG alertent sur la situation sanitaire.</p>', 17, 1, 2, 1, 'fr'),
('Sanctions internationales renforcees', '2026-03-22 11:00:00', '<p>Les etats-Unis et l''Union Europeenne annoncent de nouvelles sanctions contre l''Iran.</p>', 15, 2, 2, 2, 'fr'),
('Negociations de paix a Geneve', '2026-03-21 16:00:00', '<p>Des pourparlers de paix debutent en Suisse sous l''egide de l''ONU, mais les combats continuent sur le terrain.</p>', 12, 3, 2, 1, 'fr'),
('Situation des enfants en Iran', '2026-03-20 10:00:00', '<p>Les enfants sont les premieres victimes du conflit. Reportage dans un camp de refugies pres de la frontiere turque.</p>', 9, 1, 2, 1, 'fr'),
('Cyberattaques sur les infrastructures iraniennes', '2026-03-19 08:00:00', '<p>Des attaques informatiques massives paralysent les reseaux electriques et de communication.</p>', 8, 2, 2, 2, 'fr');

INSERT INTO Media (url, description, priorite, Id_type_media, Id_Article) VALUES
('/uploads/articles/images/iran1.jpg', 'Combats à Téhéran', 1, 1, 1),
('/uploads/articles/images/iran2.jpg', 'Bombardements dans le sud', 1, 1, 2),
('/uploads/articles/images/iran3.jpg', 'Réfugiés iraniens', 1, 1, 3),
('/uploads/articles/images/iran4.jpg', 'Sanctions internationales', 1, 1, 4),
('/uploads/articles/images/iran5.jpg', 'Négociations à Genève', 1, 1, 5),
('/uploads/articles/images/iran6.jpg', 'Enfants réfugiés', 1, 1, 6),
('/uploads/articles/images/iran7.jpg', 'Cyberattaques', 1, 1, 7);

-- Collaborations (exemple)
INSERT INTO collaboration (Id_User, Id_Article) VALUES (2, 1), (3, 2), (1, 3), (2, 4), (3, 5), (1, 6), (2, 7);

-- Historique de statuts
INSERT INTO histo_status (Id_Article, Id_status_article, date_) VALUES
(1, 2, '2026-03-25 14:00:00'),
(2, 2, '2026-03-24 18:00:00'),
(3, 2, '2026-03-23 09:30:00'),
(4, 2, '2026-03-22 11:00:00'),
(5, 2, '2026-03-21 16:00:00'),
(6, 2, '2026-03-20 10:00:00'),
(7, 2, '2026-03-19 08:00:00');

-- Tags pour articles
INSERT INTO article_tag (Id_Article, Id_tag) VALUES
(1, 1), (1, 3), (2, 1), (2, 4), (3, 1), (3, 5), (4, 2), (4, 5), (5, 1), (5, 3), (6, 4), (7, 5);

-- Hito publication (exemple)
INSERT INTO hito_publication (date_, action, Id_Article, Id_User) VALUES
('2026-03-25 13:00:00', 'creation', 1, 2),
('2026-03-24 17:00:00', 'creation', 2, 3),
('2026-03-23 08:30:00', 'creation', 3, 1),
('2026-03-22 10:00:00', 'creation', 4, 2),
('2026-03-21 15:00:00', 'creation', 5, 3),
('2026-03-20 09:00:00', 'creation', 6, 1),
('2026-03-19 07:00:00', 'creation', 7, 2);
