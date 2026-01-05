-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : dim. 14 déc. 2025 à 15:55
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `sparkup`
--

-- --------------------------------------------------------

--
-- Structure de la table `articles`
--

DROP TABLE IF EXISTS `articles`;
CREATE TABLE IF NOT EXISTS `articles` (
  `id_art` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `quantite` int NOT NULL,
  `prix` float NOT NULL,
  `url_photo` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `id_stripe` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_art`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `articles`
--

INSERT INTO `articles` (`id_art`, `nom`, `quantite`, `prix`, `url_photo`, `description`, `id_stripe`) VALUES
(1, 'Dragon Rouge', 487, 15, '../images/dragonrouge.jpg', 'Gerbes rouges étincelantes aux éclats dorés durent environ quarante-cinq secondes et se distinguent par une intensité forte, idéale pour un spectacle', 'prod_TNb7qOFcj7j0Eo'),
(2, 'Tornade Bleue', 694, 10, '../images/tornadebleue.jpeg', 'Spirales lumineuses bleues accompagnées d’une pluie argentée offrent un effet d’environ soixante secondes avec une intensité moyenne', 'prod_TNb9zr0yN1soax'),
(3, 'Tonnerre Noir', 242, 5, '../images/tonnerrenoir.jpeg', 'Feu impressionnant qi libère des gerbes intenses et puissantes aux nuances sombres et éclatantes, offrant un spectacle d’une intensité forte qui capte immédiatement l’attention et crée une ambiance spectaculaire.', 'prod_TNbAQml6qsY8di'),
(4, 'Fontaine Colorée', 286, 12, '../images/fontainecoloree.jpg', 'Fontaine lumineuse projettant un jaillissement éclatant de couleurs vives et variées, durant de longues secondes avec une intensité équilibrée, idéale pour émerveiller le public et apporter une touche festive à toute célébration.', 'prod_TNbAm1nlYMTINr'),
(5, 'Étoile Filante', 184, 7, '../images/etoilefilante.jpg', 'Jaillissement lumineux qui trace dans le ciel des traînées scintillantes d’argent et de bleu, durant près d’une minute et offrant un spectacle élégant et captivant.', 'prod_TNbBGonis1yvHY'),
(6, 'Tempête Stellaire', 284, 22, '../images/tempetestellaire.jpg', 'Spectacle cosmique aux gerbes argentées et bleues, créant un tourbillon d\'étoiles scintillantes avec des crépitements lumineux durant 60 secondes', 'prod_TNbCs5xPx80vZx'),
(7, 'Phénix d\'Or', 308, 18, '../images/phenixdor.jpg', 'Bouquets flamboyants aux éclats cuivrés, une montée lente suivie d\'une explosion en plumes dorées retombant comme une pluie d\'étincelles.', 'prod_TNbF7QqdJPIGDl'),
(8, 'Comète Émeraude', 413, 20, '../images/cometeemeraude.jpg', 'Traînées vert émeraude zébrant le ciel, se terminant par des gerbes scintillantes qui captivent le regard.', 'prod_TNbHBZf821Wb78'),
(9, 'Brume Violette', 284, 22, '../images/brumeviolette.jpg', 'Nuages violets denses suivis d\'éclats argentés, pour un effet mystique et romantique idéal lors des soirées élégantes.', 'prod_TNbHN8CCDoBgZZ'),
(10, 'Tempête d’Or', 0, 22, '../images/tempete_dor.jpg', 'Gerbes dorées crépitantes se terminant par un bouquet scintillant.', 'prod_TPQeHWF72dQPRq'),
(11, 'Ciel d’Azur', 407, 18, '../images/ciel_azur.jpg', 'Étoiles bleues profondes avec un effet de pluie argentée.', 'prod_TPQgavVKqa2HWx'),
(12, 'Explosion Impériale', 269, 30, '../images/explosion_imperiale.jpg', 'Puissant feu d’artifice combinant rouge, or et vert dans un final spectaculaire.', 'prod_TPQjbWVxaq26oC');

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `article_ratings`
-- (Voir ci-dessous la vue réelle)
--
DROP VIEW IF EXISTS `article_ratings`;
CREATE TABLE IF NOT EXISTS `article_ratings` (
`etoiles_1` decimal(23,0)
,`etoiles_2` decimal(23,0)
,`etoiles_3` decimal(23,0)
,`etoiles_4` decimal(23,0)
,`etoiles_5` decimal(23,0)
,`id_art` int
,`nom` varchar(100)
,`nombre_commentaires` bigint
,`note_moyenne` decimal(14,4)
);

-- --------------------------------------------------------

--
-- Structure de la table `client`
--

DROP TABLE IF EXISTS `client`;
CREATE TABLE IF NOT EXISTS `client` (
  `id_client` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `adresse` varchar(255) NOT NULL,
  `numero` varchar(20) NOT NULL,
  `mail` varchar(150) NOT NULL,
  `mdp` varchar(255) NOT NULL,
  `id_stripe` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_client`)
) ENGINE=MyISAM AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `client`
--

INSERT INTO `client` (`id_client`, `nom`, `prenom`, `adresse`, `numero`, `mail`, `mdp`, `id_stripe`) VALUES
(34, 'client', 'client', '45, clientstreet', '12345678', 'client@gmail.com', '$2y$10$JGzgMzQf0bj2k/tqw.dv/OiIay03iy.kE2nyx1Tek09f.imoQ6uoK', 'cus_TbUC236SeI0BqF'),
(7, 'piccio', 'picci', '45, rue des piccilis', '78596545', 'picci.piccio@piccilis', '$2y$10$4AuTk/wM3jFeiekKNcGkou4olLPurwsPKxwg6iOHL4viA86rzc49e', NULL),
(8, 'pirotti', 'pirotto', '568, av prittrotti', '47865526', 'pirottini@gmail.com', '$2y$10$VvfnpjFX1C/9MWapdTc7MOWnc5iBTd3/djaiz/l6hBDuOnrzOKgtS', NULL),
(9, 'Carminati', 'Lea', '8, via locatelli', '375238854', 'lea@gmail.com', '$2y$10$jFTblHuIWOzH/vwTRlJ0D.WDtP/X0/twFZVA.AXJwirozwQFa7jdu', NULL),
(10, 'puzzino', 'puzzo', '89, via loffa', '45896587', 'puzzo.puzz@pf.pr', '$2y$10$R0cBZZxkiuzOGYUhB5i8leP/D2oPYmJPqeGKrH5hD/qsUDr0i86L2', NULL),
(11, 'Bourgeron', 'Mona', '4, avenue georges clemenceau', '05785684', 'mona@gmail.com', '$2y$10$LaRlyu1qlBtLiUO38SXO8u9hTPHGyLvYzhyisuXN1jJtKmuXCPWLq', NULL),
(12, 'mammo', 'mammi', 'mammi street', '02565856', 'mammo@gmail.com', '$2y$10$TPTzr1zaSs8BPLAYbD/OF.8GjhjYwooKbUXWFiPHKVtNPvotQV9Xi', NULL),
(13, 'Madaio', 'Marco', 'marco', '14565885', 'marco@gmail.com', '$2y$10$JgNlI3lGBiP.GMrvV70VTukoGfvqPuaN.X2MLM5JCihNWkZ0J/OeK', NULL),
(14, 'Lea', 'Carminati', 'rue proust 45', '45657895', 'lea1@gmail.com', '$2y$10$szwN3AjcQl0R9pyGglAB3ulzDw95XEzUWf3FDn2eIGNep5iev8q6y', NULL),
(15, 'Madaio', 'Marco', 'madaiala', '5785888', 'madaio@marco.it', '$2y$10$scyCXlwP9QMNTqxlI12QZuy2HSGAMfyaSz9u..c6E4JcyFnb40q9u', NULL),
(16, 'westra', 'lotte', '4568 rue non lo so', '256978954', 'lotte@gmail.com', '$2y$10$CoHBwqP28rQm5s9D.o6RDuHKlRABIFPLar4oj/EbG1m46oOiM8O4a', NULL),
(17, 'Lastar', 'Lana', '45, rue stars, Montpellier', '12568965', 'lana@lastar.com', '$2y$10$krcV4nlnDpdEaMifw/SEFeBeVtghMggb1fNEHcJDCgiO3u1FNHvgy', 'cus_TNbKNafXgZz90G'),
(18, 'Sorel', 'Elke', 'via Bergamo 456', '45678956', 'elke@gmail.com', '$2y$10$GS106DSmvs/1P5/tgez9muz9MuD/l2uruT8SOnGMndLdV7x36Orp.', 'cus_TPSagbLq8k7IAs'),
(19, 'jam', 'jam', 'jamjamjam', '15489651223', 'jam@jam.com', '$2y$10$dFTrb3aQ7phCr8HgMObGzOer8fqbsR1F7LHksp6DPH.ztk2BjdJCC', 'cus_TRpVDft7AZnRBC'),
(20, 'sof', 'sof', 'sofsofsof', '45645689', 'sof@sof.com', '$2y$10$F/l57lkTE0NmaO/9bCTve.oysVDAuKoPBGmAToMfo0wNSO51pEIDi', 'cus_TSX7GzGuLLayuL'),
(21, 'Pattinson', 'Mona', '4 avenue Georges Clemenceau', '0783664935', 'monaboubou06@gmail.com', '$2y$10$4pc3tuTG/8UZ2Kabipe4FOeJ2T8/LcyGuGr6JDqDwTEr/Lc5N/oGe', 'cus_TSomeCuvGsnmAb'),
(22, 'clo', 'cloti', 'cloti 45', '152356645', 'clo@gmail.com', '$2y$10$WvzVV5Zo.FugBWV.UL.w3.birXXM7ural0doW09S0w6YFmPrh/tce', 'cus_TSqH4NyQcusS5N'),
(23, 'Madaia', 'Marcella', 'via delle puzzette 456', '5689657845', 'marcella@gmail.com', '$2y$10$h58MhvITprxspM7tac4cK.U6tMaAx38Brab7OaPlNKFwl.fmOlIkW', 'cus_TYCmiUNcPfk7Re'),
(24, 'finazzi', 'sofia', 'via leo da vinci 45', '456895445', 'sofia@gmail.com', '$2y$10$1D/vEOhMYJiZ75VkiwXJVOCgumJrmHX7k4KWbD.5AjwjqGF9nHZbm', 'cus_Tb2UxABAMw51js'),
(25, 'rossi', 'mario', 'mario rossi 123', '123456789', 'mario.rossi@gmail.com', '$2y$10$7/TeZzVa7AipwCj2dsiPmunch2UFvIZ58RwKerURZSeA2zRyGCr6i', 'cus_Tb36GWJ8p5tnel'),
(26, 'rossi', 'maria', 'maria rossi 123', '123456789', 'maria.rossi@gmail.com', '$2y$10$sZbGV5nO3M/MnJojUW9bGutwpAQ.Z5xyeKCFJboWwhJTEGBa1LrU6', 'cus_Tb3kRFzhh6VtX9'),
(27, 'mamma', 'mammi', 'mammalucchii 123', '123456789', 'mamma.mammi@gmail.com', '$2y$10$0k1Rz8hZjSSj9T4hGjxa5.3GdokOgfNe0qEr2d5onC52.GtMQ8Wi.', 'cus_Tb3yfNoUGnGfrG'),
(28, 'lea', 'lea', 'lea', '123456789', 'lealealea@gmail.com', '$2y$10$SWfo.wsB3I2UlDkXX8kMI.v0uqjcb9zv1cWcLR5IkS.lEhzBOt05a', 'cus_Tb49nzyfdrDa0N'),
(29, 'lea', '12', 'eklss', '123456789', 'slklsk@gjds.djf', '$2y$10$AKPd6pnn1rUO5fa9ycdpKOgMknsN0Nb.JhSaHXNlVs2pgnCVRzMF6', 'cus_Tb4CVz2zhL83OL'),
(30, 'bulo', 'bulli', 'via dei bulli', '12345678', 'bulo@gmail.com', '$2y$10$9ygjiNRC64.H4I9/z/J0uOdew2RZrns49pVt.wu.dqzw2CffnjDlK', 'cus_Tb74rAsIDNfrXU'),
(31, 'ultimo', 'cliente', 'via degli sfigati', '12345678', 'ultimo@gmail.com', '$2y$10$p/7MBFchs.6ARyBXrG/uRuzScnVvUWN4GOYNI2RKpBCzZJnpvbWAq', 'cus_Tb7KEfW3pbC3DA');

-- --------------------------------------------------------

--
-- Structure de la table `commandes`
--

DROP TABLE IF EXISTS `commandes`;
CREATE TABLE IF NOT EXISTS `commandes` (
  `id_commande` int NOT NULL AUTO_INCREMENT,
  `id_art` int NOT NULL,
  `id_client` int NOT NULL,
  `quantite` int NOT NULL,
  `envoi` tinyint(1) NOT NULL DEFAULT '0',
  `stripe_session_id` varchar(255) DEFAULT NULL,
  `stripe_payment_intent` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_commande`),
  KEY `id_art` (`id_art`),
  KEY `id_client` (`id_client`)
) ENGINE=MyISAM AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `commandes`
--

INSERT INTO `commandes` (`id_commande`, `id_art`, `id_client`, `quantite`, `envoi`, `stripe_session_id`, `stripe_payment_intent`) VALUES
(1, 5, 9, 1, 0, NULL, NULL),
(2, 3, 9, 4, 0, NULL, NULL),
(3, 5, 10, 1, 0, NULL, NULL),
(4, 1, 10, 1, 0, NULL, NULL),
(5, 2, 10, 2, 0, NULL, NULL),
(6, 3, 11, 1, 0, NULL, NULL),
(7, 4, 11, 1, 0, NULL, NULL),
(8, 6, 11, 1, 0, NULL, NULL),
(9, 7, 11, 1, 0, NULL, NULL),
(10, 1, 9, 1, 0, NULL, NULL),
(11, 1, 15, 7, 0, NULL, NULL),
(12, 6, 16, 3, 0, NULL, NULL),
(13, 2, 16, 1, 0, NULL, NULL),
(14, 3, 16, 1, 0, NULL, NULL),
(15, 5, 16, 4, 0, NULL, NULL),
(16, 6, 9, 1, 0, NULL, NULL),
(17, 4, 9, 2, 0, NULL, NULL),
(18, 7, 16, 29, 0, NULL, NULL),
(19, 9, 16, 285, 0, NULL, NULL),
(20, 1, 16, 2, 0, NULL, NULL),
(21, 6, 16, 1, 0, NULL, NULL),
(22, 7, 18, 1, 0, 'cs_test_a1RI1PrFbQuOWYxITVtv7p3n8mSM06ICVvS9xdwuEh9YIe7lK2UiYUoIsC', 'pi_3SSz1HGUI4BBz0XE0m8pHmzL'),
(23, 8, 18, 3, 0, 'cs_test_a1UdO6VshpIWN5em0deOU0dJ88548dWyrjtpdhlTMxP6QuC1R6nv66HnLe', 'pi_3SSzEiGUI4BBz0XE3ESKHuP1'),
(24, 9, 17, 6, 0, 'cs_test_a1x26a9CaZ6QGZbSGcCQOaSDkwofPyI0ngk6kuwPvtxBjAneJ3M6tX3tcE', 'pi_3ST5xoGUI4BBz0XE1DuDClFi'),
(25, 9, 18, 6, 0, 'cs_test_a1LzXFtwOF1ruktXR7yeoJ1rdJ5p0wOhCXHWxypl2On6u1aBIMhXoKk5S7', 'pi_3SUvi1GUI4BBz0XE1c9OpYwO'),
(26, 10, 17, 1, 0, 'cs_test_a1O0gzYKJZhP9qM07YFZTmrSv1ZQ0JE83TZC5v5RXTPMKjEYUPrvjZQCRm', 'pi_3SVDkjGUI4BBz0XE0H4VHc95'),
(27, 9, 20, 3, 0, 'cs_test_a1SW9rT4kaCyogixf8A6KgNlg2Xw9DKPI4kSxmdGcNpeDrlZ05eza613OT', 'pi_3SVc4YGUI4BBz0XE2882YQKW'),
(28, 4, 20, 3, 0, 'cs_test_a1tfouA2WFnPbhet5h9MFFSZHqTZES4HtckqJDVWC3nNEz2DgCynKVjfF8', 'pi_3SVsb9GUI4BBz0XE3AUx2CcG'),
(29, 1, 21, 1, 0, 'cs_test_b1OSRGB4nYpDCvyjPXbKonEYHqU8c7MBJNA5kfAEuVaiUKFn0aUCqO4zM6', 'pi_3SVtC0GUI4BBz0XE2kjtsUh4'),
(30, 2, 21, 3, 0, 'cs_test_b1OSRGB4nYpDCvyjPXbKonEYHqU8c7MBJNA5kfAEuVaiUKFn0aUCqO4zM6', 'pi_3SVtC0GUI4BBz0XE2kjtsUh4'),
(31, 3, 21, 2, 0, 'cs_test_b1OSRGB4nYpDCvyjPXbKonEYHqU8c7MBJNA5kfAEuVaiUKFn0aUCqO4zM6', 'pi_3SVtC0GUI4BBz0XE2kjtsUh4'),
(32, 4, 21, 5, 0, 'cs_test_b1OSRGB4nYpDCvyjPXbKonEYHqU8c7MBJNA5kfAEuVaiUKFn0aUCqO4zM6', 'pi_3SVtC0GUI4BBz0XE2kjtsUh4'),
(33, 5, 21, 6, 0, 'cs_test_b1OSRGB4nYpDCvyjPXbKonEYHqU8c7MBJNA5kfAEuVaiUKFn0aUCqO4zM6', 'pi_3SVtC0GUI4BBz0XE2kjtsUh4'),
(34, 6, 21, 4, 0, 'cs_test_b1OSRGB4nYpDCvyjPXbKonEYHqU8c7MBJNA5kfAEuVaiUKFn0aUCqO4zM6', 'pi_3SVtC0GUI4BBz0XE2kjtsUh4'),
(35, 7, 21, 3, 0, 'cs_test_b1OSRGB4nYpDCvyjPXbKonEYHqU8c7MBJNA5kfAEuVaiUKFn0aUCqO4zM6', 'pi_3SVtC0GUI4BBz0XE2kjtsUh4'),
(36, 8, 21, 1, 0, 'cs_test_b1OSRGB4nYpDCvyjPXbKonEYHqU8c7MBJNA5kfAEuVaiUKFn0aUCqO4zM6', 'pi_3SVtC0GUI4BBz0XE2kjtsUh4'),
(37, 9, 21, 1, 0, 'cs_test_b1OSRGB4nYpDCvyjPXbKonEYHqU8c7MBJNA5kfAEuVaiUKFn0aUCqO4zM6', 'pi_3SVtC0GUI4BBz0XE2kjtsUh4'),
(38, 10, 21, 2, 0, 'cs_test_b1OSRGB4nYpDCvyjPXbKonEYHqU8c7MBJNA5kfAEuVaiUKFn0aUCqO4zM6', 'pi_3SVtC0GUI4BBz0XE2kjtsUh4'),
(39, 11, 21, 1, 0, 'cs_test_b1OSRGB4nYpDCvyjPXbKonEYHqU8c7MBJNA5kfAEuVaiUKFn0aUCqO4zM6', 'pi_3SVtC0GUI4BBz0XE2kjtsUh4'),
(40, 12, 21, 3, 0, 'cs_test_b1OSRGB4nYpDCvyjPXbKonEYHqU8c7MBJNA5kfAEuVaiUKFn0aUCqO4zM6', 'pi_3SVtC0GUI4BBz0XE2kjtsUh4'),
(41, 8, 22, 1, 0, 'cs_test_a18bJNDpzQSOzCteVBaST9UOIE2fea36J8hV5st9sJ2E9CfZknl6WzBjmq', 'pi_3SVubdGUI4BBz0XE1cRC7Wpr'),
(42, 7, 18, 4, 0, 'cs_test_a150BS2gYT8YaaasYyhrDU396b7zv8monqpSeXDIuiAO3z7kk3yIU2YR9S', 'pi_3SVzYPGUI4BBz0XE3XSWkgx8'),
(43, 10, 18, 3, 0, 'cs_test_a1nNOBcfIclC6e9M4qUBy6ZoKAbcSl85RqfIsPWehMyQJ3ut5En4NxAch9', 'pi_3SYCSUGUI4BBz0XE2lUiHm8F'),
(44, 7, 22, 4, 0, 'cs_test_a1t7A51wTaJQCKyzDI61FdbHjiNLWmqFoCxFk8dscHT7OfM24TYQLy0IQj', 'pi_3Saer5GUI4BBz0XE2naqZO0C'),
(45, 4, 22, 1, 0, 'cs_test_a1bcXxBNvCq6WG3R77dSsI9kvbgAI8r7IA9SvZcSx4QTszDDEDxThiHLb6', 'pi_3Sb6CdGUI4BBz0XE0FlJAkGw'),
(46, 12, 23, 3, 0, 'cs_test_a1neBQkToHB74XiJTXSJufREZHFXVSnBitAY970MiJY7NBGRrRE1oAjHqF', 'pi_3Sb6PfGUI4BBz0XE0InJTkLB'),
(47, 10, 22, 1, 0, 'cs_test_a1OvhxoEKlhV6npkvbrOGY1UiXioO6kwlP34nhmde8OdvZm0UtYjM7c5BM', 'pi_3ScYPdGUI4BBz0XE3GeFKUcg'),
(48, 5, 23, 1, 0, 'cs_test_a1MKjV6TWOJ03YbGOL6P7Iq9QPztJpKuel79kc0OCGrcXrmhV4KRjRZPxN', 'pi_3ScYj1GUI4BBz0XE36Y86cjl'),
(49, 11, 29, 2, 0, 'cs_test_a1eQcwk51eUiCON6FHlrsMpfSvoNWU3y0iG2OSxVTwx5ZzZuy0EET3ygGD', 'pi_3Sds5OGUI4BBz0XE3KOrCsKW'),
(50, 5, 17, 1, 0, 'cs_test_a1On3H3RvekAyl8x2xsZEqt2pLhySULORJD9eGxS98apTn28JPDbTaWKqK', 'pi_3SdtNUGUI4BBz0XE2sorSp17'),
(51, 6, 30, 2, 0, 'cs_test_a1KtIqhvRkOE5T4KyOGXnfbdnsBBCXyQ71tFOqjuhy2MWNeWu9n3l6nH3j', 'pi_3SduuwGUI4BBz0XE36f46WNj'),
(52, 1, 31, 1, 0, 'cs_test_a1VT8r7qyuK3oflxeYqThTQWeIYizAkDdSTQd2EvjGChUpgP3q4ETUi6qm', 'pi_3Sdv6uGUI4BBz0XE0Qv5hwfe'),
(53, 4, 18, 2, 0, 'cs_test_a1CJGceKv7zcTXCSH8sznq2CnpzNqglwm4dk0TzGi4HcyTLE8jJL5d3yBB', 'pi_3SeG3PGUI4BBz0XE0n9Xp9tA'),
(54, 5, 33, 2, 0, 'cs_test_b1AJYx8jOYnJibdKqTsmlXg1w6gcpJoxejWVVaTDhLK6VGfHATgp0th4yF', 'pi_3SeGwCGUI4BBz0XE2JRrIWRa'),
(55, 6, 33, 288, 0, 'cs_test_b1AJYx8jOYnJibdKqTsmlXg1w6gcpJoxejWVVaTDhLK6VGfHATgp0th4yF', 'pi_3SeGwCGUI4BBz0XE2JRrIWRa'),
(56, 8, 34, 2, 0, 'cs_test_b1591wWoVf6CRR0tzPtKYUeP1ZSw1sddmG6OfyDtYzGvxpflzodwC0d2oa', 'pi_3SeHGmGUI4BBz0XE1JSGRCpn'),
(57, 10, 34, 313, 0, 'cs_test_b1591wWoVf6CRR0tzPtKYUeP1ZSw1sddmG6OfyDtYzGvxpflzodwC0d2oa', 'pi_3SeHGmGUI4BBz0XE1JSGRCpn');

-- --------------------------------------------------------

--
-- Structure de la table `commentaires`
--

DROP TABLE IF EXISTS `commentaires`;
CREATE TABLE IF NOT EXISTS `commentaires` (
  `id_commentaire` int NOT NULL AUTO_INCREMENT,
  `id_client` int NOT NULL,
  `id_art` int NOT NULL,
  `contenu` text NOT NULL,
  `note` int NOT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_commentaire`),
  UNIQUE KEY `unique_user_product` (`id_client`,`id_art`),
  KEY `idx_client` (`id_client`),
  KEY `idx_art` (`id_art`),
  KEY `idx_commentaires_article` (`id_art`),
  KEY `idx_commentaires_date` (`date_creation`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `commentaires`
--

INSERT INTO `commentaires` (`id_commentaire`, `id_client`, `id_art`, `contenu`, `note`, `date_creation`) VALUES
(3, 22, 10, 'adoro!!!!! bum pim pam', 5, '2025-12-09 22:27:28'),
(4, 22, 8, 'colori pazzeschi', 5, '2025-12-09 22:29:28'),
(5, 23, 5, 'bellissimo! il mio bambino é saltato in aria', 5, '2025-12-09 22:46:26'),
(7, 17, 5, 'bellissimo', 2, '2025-12-13 15:02:26'),
(8, 9, 1, 'my all time favourite', 5, '2025-12-13 16:28:10'),
(9, 30, 6, 'orribile', 2, '2025-12-13 16:40:10'),
(10, 31, 1, 'mid', 3, '2025-12-13 16:52:26');

-- --------------------------------------------------------

--
-- Structure de la table `commentaire_likes`
--

DROP TABLE IF EXISTS `commentaire_likes`;
CREATE TABLE IF NOT EXISTS `commentaire_likes` (
  `id_like` int NOT NULL AUTO_INCREMENT,
  `id_commentaire` int NOT NULL,
  `id_client` int NOT NULL,
  `date_like` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_like`),
  UNIQUE KEY `unique_user_comment` (`id_commentaire`,`id_client`),
  KEY `idx_commentaire` (`id_commentaire`),
  KEY `idx_client` (`id_client`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `commentaire_likes`
--

INSERT INTO `commentaire_likes` (`id_like`, `id_commentaire`, `id_client`, `date_like`) VALUES
(3, 5, 18, '2025-12-09 22:49:38'),
(4, 3, 22, '2025-12-12 20:40:42'),
(5, 4, 22, '2025-12-12 21:45:12'),
(7, 5, 17, '2025-12-13 15:00:09'),
(8, 8, 31, '2025-12-13 16:52:30'),
(9, 8, 17, '2025-12-14 12:24:49'),
(10, 10, 17, '2025-12-14 12:24:51');

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE IF NOT EXISTS `messages` (
  `id_message` int NOT NULL AUTO_INCREMENT,
  `id_client` int NOT NULL,
  `contenu` varchar(256) NOT NULL,
  `date_envoi` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_message`),
  KEY `idx_date_envoi` (`date_envoi`)
) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `messages`
--

INSERT INTO `messages` (`id_message`, `id_client`, `contenu`, `date_envoi`) VALUES
(87, 34, 'salut!', '2025-12-14 15:31:55'),
(88, 23, 'coucou', '2025-12-14 15:32:06');

-- --------------------------------------------------------

--
-- Structure de la table `questions`
--

DROP TABLE IF EXISTS `questions`;
CREATE TABLE IF NOT EXISTS `questions` (
  `id_question` int NOT NULL AUTO_INCREMENT,
  `id_client` int NOT NULL,
  `id_art` int NOT NULL,
  `titre` varchar(255) NOT NULL,
  `contenu` text NOT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_question`),
  KEY `idx_client` (`id_client`),
  KEY `idx_art` (`id_art`),
  KEY `idx_questions_article` (`id_art`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `questions`
--

INSERT INTO `questions` (`id_question`, `id_client`, `id_art`, `titre`, `contenu`, `date_creation`) VALUES
(7, 17, 5, 'pagamento', 'posso pagare con una carta scaduta?', '2025-12-13 15:05:15');

-- --------------------------------------------------------

--
-- Structure de la table `reponses`
--

DROP TABLE IF EXISTS `reponses`;
CREATE TABLE IF NOT EXISTS `reponses` (
  `id_reponse` int NOT NULL AUTO_INCREMENT,
  `id_question` int NOT NULL,
  `id_client` int NOT NULL,
  `id_parent` int DEFAULT NULL,
  `contenu` text NOT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_reponse`),
  KEY `idx_question` (`id_question`),
  KEY `idx_client` (`id_client`),
  KEY `idx_parent` (`id_parent`),
  KEY `idx_reponses_question` (`id_question`),
  KEY `idx_reponses_parent` (`id_parent`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `reponses`
--

INSERT INTO `reponses` (`id_reponse`, `id_question`, `id_client`, `id_parent`, `contenu`, `date_creation`) VALUES
(13, 7, 9, NULL, 'yesss', '2025-12-13 16:29:34');

-- --------------------------------------------------------

--
-- Structure de la vue `article_ratings`
--
DROP TABLE IF EXISTS `article_ratings`;

DROP VIEW IF EXISTS `article_ratings`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `article_ratings`  AS SELECT `a`.`id_art` AS `id_art`, `a`.`nom` AS `nom`, coalesce(avg(`c`.`note`),0) AS `note_moyenne`, count(`c`.`id_commentaire`) AS `nombre_commentaires`, sum((case when (`c`.`note` = 5) then 1 else 0 end)) AS `etoiles_5`, sum((case when (`c`.`note` = 4) then 1 else 0 end)) AS `etoiles_4`, sum((case when (`c`.`note` = 3) then 1 else 0 end)) AS `etoiles_3`, sum((case when (`c`.`note` = 2) then 1 else 0 end)) AS `etoiles_2`, sum((case when (`c`.`note` = 1) then 1 else 0 end)) AS `etoiles_1` FROM (`articles` `a` left join `commentaires` `c` on((`a`.`id_art` = `c`.`id_art`))) GROUP BY `a`.`id_art`, `a`.`nom` ;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `questions`
--
ALTER TABLE `questions` ADD FULLTEXT KEY `search_questions` (`titre`,`contenu`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
