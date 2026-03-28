CREATE DATABASE IF NOT EXISTS site_actus;
USE site_actus;

CREATE TABLE Role( -- admin , journaliste
   Id_Role INT AUTO_INCREMENT,
   role VARCHAR(50)  NOT NULL,
   PRIMARY KEY(Id_Role),
   UNIQUE(role)
);

CREATE TABLE User_(
   Id_User INT AUTO_INCREMENT,
   email VARCHAR(250)  NOT NULL,
   nom VARCHAR(250) ,
   prenom VARCHAR(250) ,
   mdp VARCHAR(250)  NOT NULL,
   numero_tel VARCHAR(50)  NOT NULL,
   adresse VARCHAR(250) ,
   Id_Role INT NOT NULL,
   PRIMARY KEY(Id_User),
   UNIQUE(email),
   FOREIGN KEY(Id_Role) REFERENCES Role(Id_Role)
);

CREATE TABLE Categorie(
   Id_Categorie INT AUTO_INCREMENT,
   categorie VARCHAR(50) ,
   description VARCHAR(50) ,
   PRIMARY KEY(Id_Categorie)
);

CREATE TABLE type_media( -- image, video, audio
   Id_type_media INT AUTO_INCREMENT,
   type VARCHAR(50) ,
   PRIMARY KEY(Id_type_media)
);

CREATE TABLE tag(
   Id_tag INT AUTO_INCREMENT,
   nom VARCHAR(50)  NOT NULL,
   PRIMARY KEY(Id_tag),
   UNIQUE(nom)
);

CREATE TABLE status_article(
   Id_status_article INT AUTO_INCREMENT,
   status VARCHAR(50)  NOT NULL,
   PRIMARY KEY(Id_status_article),
   UNIQUE(status)
);

CREATE TABLE Article(
   Id_Article INT AUTO_INCREMENT,
   titre VARCHAR(250) ,
   date_publication DATETIME NOT NULL,
   contenu TEXT, -- venant de TinyMCE de format HTML
   nbr_vues INT, -- incrementation d'un compteur en mémoire ou dans un petit fichier local(ex: json) et insertion dans la base toutes les heures ou meme tous les jours pour eviter de faire une requete d'incrementation a chaque vue et donc saturer la base de données
   Id_User_principal INT NOT NULL, -- auteur principal
   Id_status_article INT NOT NULL,
   Id_Categorie INT NOT NULL,
   lang ENUM('fr', 'en') NOT NULL,
   PRIMARY KEY(Id_Article),
   FOREIGN KEY(Id_User_principal) REFERENCES User_(Id_User),
   FOREIGN KEY(Id_status_article) REFERENCES status_article(Id_status_article),
   FOREIGN KEY(Id_Categorie) REFERENCES Categorie(Id_Categorie)
);

CREATE TABLE Media(
   Id_Media INT AUTO_INCREMENT,
   url VARCHAR(250) ,
   description TEXT,
   priorite BOOLEAN, -- true pour l'image principale, false pour les autres médias
   Id_type_media INT NOT NULL,
   Id_Article INT NOT NULL,
   PRIMARY KEY(Id_Media),
   FOREIGN KEY(Id_type_media) REFERENCES type_media(Id_type_media),
   FOREIGN KEY(Id_Article) REFERENCES Article(Id_Article)
);

CREATE TABLE hito_publication(
   Id_hito_publication INT AUTO_INCREMENT,
   date_ DATETIME,
   action VARCHAR(50) ENUM('creation', 'modification', 'publication', 'retrait') NOT NULL,
   Id_Article INT NOT NULL,
   Id_User INT NOT NULL,
   PRIMARY KEY(Id_hito_publication),
   FOREIGN KEY(Id_Article) REFERENCES Article(Id_Article),
   FOREIGN KEY(Id_User) REFERENCES User_(Id_User)
);

CREATE TABLE collaboration(
   Id_User INT,
   Id_Article INT,
   PRIMARY KEY(Id_User, Id_Article),
   FOREIGN KEY(Id_User) REFERENCES User_(Id_User),
   FOREIGN KEY(Id_Article) REFERENCES Article(Id_Article)
);

CREATE TABLE histo_status(
   Id_Article INT,
   Id_status_article INT,
   date_ DATETIME,
   PRIMARY KEY(Id_Article, Id_status_article),
   FOREIGN KEY(Id_Article) REFERENCES Article(Id_Article),
   FOREIGN KEY(Id_status_article) REFERENCES status_article(Id_status_article)
);

CREATE TABLE article_tag(
   Id_Article INT,
   Id_tag INT,
   PRIMARY KEY(Id_Article, Id_tag),
   FOREIGN KEY(Id_Article) REFERENCES Article(Id_Article),
   FOREIGN KEY(Id_tag) REFERENCES tag(Id_tag)
);
