-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : dim. 05 oct. 2025 à 20:55
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `healthytest`
--

-- --------------------------------------------------------

--
-- Structure de la table `accord_aromatique`
--

CREATE TABLE `accord_aromatique` (
  `id` int(11) NOT NULL,
  `plante_id` int(11) NOT NULL,
  `ingredient_id` int(11) DEFAULT NULL,
  `ingredient_type` varchar(50) DEFAULT NULL,
  `score` double NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `accord_aromatique`
--

INSERT INTO `accord_aromatique` (`id`, `plante_id`, `ingredient_id`, `ingredient_type`, `score`) VALUES
(15, 101, 106, NULL, 0.9),
(16, 101, 112, NULL, 0.9),
(17, 101, 109, NULL, 0.9),
(18, 101, 143, NULL, 0.9),
(19, 101, NULL, 'Viande', 0.9),
(20, 101, NULL, 'Légume', 0.9),
(21, 101, NULL, 'Céréale', 0.9),
(22, 99, 193, NULL, 0.85),
(23, 99, 138, NULL, 0.85),
(24, 99, 178, NULL, 0.85),
(25, 99, NULL, 'Viande', 0.85),
(26, 99, NULL, 'Céréale', 0.85),
(27, 99, NULL, 'Laitier', 0.85),
(28, 92, 105, NULL, 1),
(29, 92, 136, NULL, 1),
(30, 92, 100, NULL, 1),
(31, 92, 117, NULL, 1),
(32, 92, NULL, 'Sucré', 1),
(33, 92, NULL, 'Légume', 1),
(34, 92, NULL, 'Boisson', 1),
(35, 92, NULL, 'Laitier', 1),
(36, 91, 106, NULL, 0.8),
(37, 91, 124, NULL, 0.8),
(38, 91, 132, NULL, 0.8),
(39, 91, NULL, 'Laitier', 0.8),
(40, 91, NULL, 'Sucre', 0.8),
(41, 91, NULL, 'Fruit', 0.8),
(42, 93, 105, NULL, 0.9),
(43, 93, 122, NULL, 0.9),
(44, 93, 123, NULL, 0.9),
(45, 93, NULL, 'Fruit', 0.9),
(46, 93, NULL, 'Boisson', 0.9),
(47, 93, NULL, 'Sucré', 0.9),
(48, 96, 122, NULL, 1.1),
(49, 96, 123, NULL, 1.1),
(50, 96, 105, NULL, 1.1),
(51, 96, 182, NULL, 1.1),
(52, 96, NULL, 'Boisson', 1.1),
(53, 96, NULL, 'Sucré', 1.1),
(54, 96, NULL, 'Fruit', 1.1),
(55, 100, 106, NULL, 0.8),
(56, 100, 117, NULL, 0.8),
(57, 100, 153, NULL, 0.8),
(58, 100, NULL, 'Laitier', 0.8),
(59, 100, NULL, 'Sucré', 0.8),
(60, 100, NULL, 'Fruit', 0.8),
(61, 102, 143, NULL, 1),
(62, 102, 195, NULL, 1),
(63, 102, 109, NULL, 1),
(64, 102, NULL, 'Viande', 1),
(65, 102, NULL, 'Légume', 1),
(66, 102, NULL, 'Céréale', 1),
(67, 94, 106, NULL, 0.75),
(68, 94, 124, NULL, 0.75),
(69, 94, NULL, 'Sucre', 0.75),
(70, 94, NULL, 'Fruit', 0.75),
(71, 94, NULL, 'Boisson', 0.75),
(72, 95, 113, NULL, 0.9),
(73, 95, 197, NULL, 0.9),
(74, 95, 152, NULL, 0.9),
(75, 95, NULL, 'Poisson', 0.9),
(76, 95, NULL, 'Légume', 0.9),
(77, 95, NULL, 'Fruit', 0.9),
(78, 98, 112, NULL, 0.9),
(79, 98, 111, NULL, 0.9),
(80, 98, 199, NULL, 0.9),
(81, 98, NULL, 'Viande', 0.9),
(82, 98, NULL, 'Céréale', 0.9),
(83, 98, NULL, 'Poisson', 0.9);

-- --------------------------------------------------------

--
-- Structure de la table `article`
--

CREATE TABLE `article` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `contenu` longtext NOT NULL,
  `date` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `image` varchar(255) DEFAULT NULL,
  `validation` tinyint(1) NOT NULL DEFAULT 0,
  `categorie` varchar(255) NOT NULL,
  `source` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `article`
--

INSERT INTO `article` (`id`, `utilisateur_id`, `titre`, `contenu`, `date`, `image`, `validation`, `categorie`, `source`) VALUES
(47, 21, 'Les bases d’une alimentation anti-inflammatoire', 'Réduire les aliments ultra-transformés et privilégier fruits, légumes, oméga-3 et fibres. Cet article détaille des repères concrets et des exemples de menus.', '2025-02-10 09:00:00', NULL, 1, 'Nutrition', NULL),
(48, 21, 'Routine bien-être du soir pour mieux dormir', 'Une routine courte, sans écran, avec hydratation, respiration et tisane douce. Étapes détaillées et conseils pour instaurer une régularité.', '2025-02-08 19:00:00', NULL, 1, 'Bien-être', NULL),
(49, 21, 'Camomille, verveine : plantes du sommeil', 'La camomille et la verveine aident à relâcher les tensions. Posologies, précautions et idées d’associations simples pour une tisane du soir.', '2025-02-05 08:30:00', NULL, 1, 'Plantes', NULL),
(50, 21, 'Comprendre les étiquettes nutritionnelles', 'Décrypter la liste d’ingrédients, le tableau nutritionnel et les additifs fréquents. Focus sur le sel, les sucres et les matières grasses.', '2025-02-03 10:00:00', NULL, 1, 'Conseils', NULL),
(51, 21, 'Hydratation: eau, tisanes et boissons à privilégier', 'Objectifs quotidiens, signes de déshydratation, rôle des tisanes fruitées et des eaux minérales. Repères pratiques au fil de la journée.', '2025-01-30 09:00:00', NULL, 1, 'Nutrition', NULL),
(52, 21, 'Hibiscus et tension: que disent les études ?', 'L’hibiscus est étudié pour son effet modeste sur la tension. Cet article résume les précautions et propose un usage responsable en tisane.', '2025-01-28 09:10:00', NULL, 1, 'Plantes', NULL),
(53, 21, 'Batch-cooking sain: organisation en 2 heures', 'Méthode pas-à-pas, liste de courses type et idées d’associations de recettes pour gagner du temps tout en mangeant équilibré.', '2025-01-25 11:00:00', NULL, 1, 'Conseils', NULL),
(54, 21, 'Fibres: alliées digestion et satiété', 'Différence entre fibres solubles et insolubles, apports conseillés et astuces pour augmenter les fibres sans inconfort digestif.', '2025-01-22 09:00:00', NULL, 1, 'Nutrition', NULL),
(55, 21, 'Tilleul et relaxation: usages traditionnels', 'Le tilleul est une plante clé de la détente. Préparations d’infusion, dosage courant et précautions générales à connaître.', '2025-01-19 09:40:00', NULL, 1, 'Plantes', NULL),
(56, 21, 'Petit-déjeuner équilibré: 5 modèles rapides', '5 modèles simples: protéiné, fruité, céréales complètes, laitage + oléagineux, et salé. Ajustables selon l’appétit et le temps.', '2025-01-16 07:45:00', NULL, 1, 'Conseils', NULL),
(57, 21, 'Oméga-3: quels aliments choisir au quotidien ?', 'Poissons gras, huiles de colza et de noix, graines de lin et de chia: idées concrètes pour couvrir les besoins hebdomadaires.', '2025-01-12 13:15:00', NULL, 1, 'Nutrition', NULL),
(58, 21, 'Thym et menthe: respiration et digestion', 'Deux aromatiques courantes aux usages complémentaires. Conseils d’infusion, associations et mise en garde en cas de reflux.', '2025-01-10 09:00:00', NULL, 1, 'Plantes', NULL),
(59, 21, 'Snack malin: 10 idées <200 kcal', 'Idées pratiques et rassasiantes: fruit + oléagineux, yaourt nature, crudités, houmous, carré de chocolat noir + amandes, etc.', '2025-01-08 16:00:00', NULL, 1, 'Conseils', NULL),
(60, 21, 'Équilibre acide-base dans l’assiette', 'Sans dogme: plus de végétaux, moins d’ultra-transformés. Exemples d’assiettes ‘80/20’ et repères simples à retenir.', '2025-01-05 10:30:00', NULL, 1, 'Nutrition', NULL),
(61, 21, 'Romarin et gingembre: tonus et digestion', 'Deux plantes aromatiques utiles en cuisine et en infusion. Dosages usuels, associations possibles et précautions.', '2025-01-03 09:00:00', NULL, 1, 'Plantes', NULL),
(62, 21, 'Construire une assiette équilibrée (méthode 1/2-1/4-1/4)', 'Demi-assiette de légumes, un quart de protéines, un quart de féculents complets. Variantes et exemples concrets.', '2024-12-28 12:00:00', NULL, 1, 'Conseils', NULL),
(63, 21, 'Antioxydants: où les trouver facilement ?', 'Fruits rouges, agrumes, légumes colorés, thé vert et cacao: tour d’horizon et idées pour en consommer au quotidien.', '2024-12-20 09:00:00', NULL, 1, 'Nutrition', NULL),
(64, 21, 'Sauge et lavande: détente et usages pratiques', 'Deux plantes à manier avec précaution. Rappels de dosage, contre-indications et idées de tisane apaisante en soirée.', '2024-12-15 18:30:00', NULL, 1, 'Plantes', NULL),
(65, 24, 'Comment faire un bon repas équilibré ?', '<div class=\"list-style block-heading-wrapper relative\">\r\n<h2 class=\"mt-8 font-border flex items-center mb-4\">&nbsp;</h2>\r\n</div>\r\n<div class=\"list-style block-text-wrapper relative\">\r\n<p><strong>L&rsquo;organisme n&rsquo;a pas besoin de tous les aliments en m&ecirc;mes quantit&eacute;s.&nbsp;Certaines familles doivent &ecirc;tre tr&egrave;s pr&eacute;sentes (base de la pyramide alimentaire), d&rsquo;autres moins (pointe). Cela donne pour chaque journ&eacute;e&nbsp;:</strong></p>\r\n</div>\r\n<div class=\"list-style block-list-wrapper relative\">\r\n<div class=\"list-style block-text-wrapper relative mx-5\">\r\n<ul class=\"my-3\">\r\n<li class=\"lg:text-lg text-base list-disc\"><strong>De l&rsquo;eau &agrave; volont&eacute;.&nbsp;Buvez au moins 1,5 litre de liquide, pendant et entre les repas, sous forme d&rsquo;eau et de&nbsp;boissons non caloriques. Oubliez les boissons sucr&eacute;es ou chimiques, vous ne trouverez jamais autant de bienfaits que dans l&rsquo;eau de source (ou du robinet, adapt&eacute;e &agrave; nos besoins) ;</strong></li>\r\n<li class=\"lg:text-lg text-base list-disc\"><strong>Des f&eacute;culents &agrave; chaque repas.&nbsp;C&eacute;r&eacute;ales, aliments &agrave; base de c&eacute;r&eacute;ales (riz, p&acirc;tes, semoule, bl&eacute;, pain&hellip;),&nbsp;l&eacute;gumes secs&nbsp;(lentilles, f&egrave;ves, pois&hellip;), pommes de terre&hellip; de pr&eacute;f&eacute;rence compl&egrave;tes ;</strong></li>\r\n<li class=\"lg:text-lg text-base list-disc\"><strong>5 fruits et l&eacute;gumes.&nbsp;C&rsquo;est-&agrave;-dire au moins 400 g, &agrave; tous les repas, sous toutes leurs formes (cuits, crus, mix&eacute;s&hellip;) ;</strong></li>\r\n<li class=\"lg:text-lg text-base list-disc\"><strong>1 &agrave; 2 fois de la viande, du poisson ou des &oelig;ufs.&nbsp;En proportion inf&eacute;rieure &agrave; celle de l&rsquo;accompagnement (f&eacute;culents et l&eacute;gumes). Pour la viande, pr&eacute;f&eacute;rez les morceaux les moins gras. Pour le poisson, consommez aussi des esp&egrave;ces grasses ;</strong></li>\r\n<li class=\"lg:text-lg text-base list-disc\"><strong>3 produits laitiers.&nbsp;&Agrave; chaque repas, alternez lait, fromages, yaourts afin d&rsquo;obtenir un bon compromis entre mati&egrave;res grasses et&nbsp;calcium&nbsp;;</strong></li>\r\n<li class=\"lg:text-lg text-base list-disc\"><strong>Un peu de mati&egrave;res grasses. Variez les sources (huiles, beurre, margarine&hellip;) et mod&eacute;rez votre consommation ;</strong></li>\r\n<li class=\"lg:text-lg text-base list-disc\"><strong>Rarement, des produits sucr&eacute;s, en particulier ceux &agrave;&nbsp;indice glyc&eacute;mique &eacute;lev&eacute;&nbsp;seuls.&nbsp;Tous sont caloriques, soit parce qu&rsquo;ils sont riches en sucre (sodas, bonbons&hellip;), soit parce qu&rsquo;ils cumulent sucre et gras (p&acirc;tisseries, viennoiseries, chocolat&hellip;)&nbsp;</strong></li>\r\n</ul>\r\n</div>\r\n</div>\r\n<div style=\"position: absolute; left: -65535px;\">&nbsp;</div>\r\n<div style=\"position: absolute; left: -65535px;\">&nbsp;</div>', '2025-08-28 19:57:05', 'manger-equilibre-68b0b491918dd.jpg', 0, 'Nutrition', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `bienfait`
--

CREATE TABLE `bienfait` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `bienfait`
--

INSERT INTO `bienfait` (`id`, `nom`, `description`) VALUES
(44, 'Relaxation', 'Aide à calmer le système nerveux et la tension intérieure. Utile en période de stress.'),
(45, 'Sommeil', 'Favorise l’endormissement naturel et un sommeil plus réparateur.'),
(46, 'Digestion', 'Soutient la digestion après les repas, limite ballonnements et inconforts.'),
(47, 'Respiration', 'Apaise la sphère ORL et soutient le confort respiratoire.'),
(48, 'Antioxydant', 'Aide à lutter contre le stress oxydatif lié aux radicaux libres.'),
(49, 'Cardio', 'Participe au maintien d’une bonne santé cardiovasculaire.'),
(50, 'Tonique', 'Apporte un effet stimulant/dynamisant doux (vitalité, énergie).'),
(51, 'Détox', 'Soutient les fonctions d’élimination (foie, reins) et l’équilibre métabolique.'),
(52, 'Circulation sanguine', 'Aide à activer la micro-circulation et à réduire la sensation de jambes lourdes.'),
(53, 'Anti-inflammatoire', 'Contribue à apaiser les phénomènes inflammatoires légers.'),
(54, 'Antispasmodique', 'Aide à limiter les spasmes digestifs et les crampes légères.'),
(55, 'Nausées', 'Aide à réduire la sensation de nausée et le mal des transports.'),
(56, 'Hydratation', 'Favorise une bonne hydratation, agréable en boisson chaude ou froide.'),
(57, 'Stress', 'Aide à mieux gérer les tensions nerveuses et l’irritabilité.'),
(58, 'Métabolisme', NULL),
(59, 'Immunité', NULL),
(60, 'Carminatif', NULL),
(61, 'Antistress', NULL),
(62, 'Sédatif léger', NULL),
(63, 'Diurétique', NULL),
(64, 'Antinauséeux', NULL),
(65, 'Antitussif', NULL),
(66, 'Antiseptique', NULL),
(67, 'Drainage hépatique', NULL),
(68, 'Cicatrisant', NULL),
(69, 'Expectorant', NULL),
(70, 'Émollient', NULL),
(71, 'Adoucissant', NULL),
(72, 'Humeur', NULL),
(73, 'Antidépresseur léger', NULL),
(74, 'Sudorifique', NULL),
(75, 'Foie', NULL),
(76, 'Drainage rénal', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `commentaire`
--

CREATE TABLE `commentaire` (
  `id` int(11) NOT NULL,
  `recette_id` int(11) DEFAULT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `contenu` longtext DEFAULT NULL,
  `date` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `note` int(11) DEFAULT NULL,
  `signaler` tinyint(1) NOT NULL DEFAULT 0,
  `article_id` int(11) DEFAULT NULL,
  `signale_par_id` int(11) DEFAULT NULL,
  `type` smallint(6) NOT NULL,
  `signale_le` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `commentaire`
--

INSERT INTO `commentaire` (`id`, `recette_id`, `utilisateur_id`, `contenu`, `date`, `note`, `signaler`, `article_id`, `signale_par_id`, `type`, `signale_le`) VALUES
(12, 42, 24, 'recette simple et savoureuse', '2025-09-03 15:44:05', 3, 0, NULL, NULL, 1, NULL),
(13, NULL, 24, 'Article très intéressent', '2025-09-03 15:51:53', NULL, 0, 47, NULL, 2, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `doctrine_migration_versions`
--

CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `doctrine_migration_versions`
--

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
('DoctrineMigrations\\Version20250726095824', '2025-07-26 09:58:41', 570),
('DoctrineMigrations\\Version20250728192433', '2025-07-28 19:24:47', 55),
('DoctrineMigrations\\Version20250803145403', '2025-08-03 14:54:24', 137),
('DoctrineMigrations\\Version20250806100327', '2025-08-06 10:03:42', 143),
('DoctrineMigrations\\Version20250820165415', '2025-08-20 16:54:38', 452),
('DoctrineMigrations\\Version20250825133343', '2025-08-25 13:33:46', 12),
('DoctrineMigrations\\Version20250903160634', '2025-09-03 16:06:46', 15),
('DoctrineMigrations\\Version20250905165620', '2025-09-05 16:56:35', 93),
('DoctrineMigrations\\Version20250906173306', '2025-09-06 17:33:30', 382),
('DoctrineMigrations\\Version20250907133636', '2025-09-07 13:36:45', 69),
('DoctrineMigrations\\Version20251003190114', '2025-10-03 19:01:29', 88);

-- --------------------------------------------------------

--
-- Structure de la table `gene`
--

CREATE TABLE `gene` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `description` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `gene`
--

INSERT INTO `gene` (`id`, `nom`, `description`) VALUES
(49, 'Lactose', NULL),
(50, 'Gluten', NULL),
(51, 'Reflux', NULL),
(52, 'Ballonnements', NULL),
(53, 'Glycémie', NULL),
(54, 'Allergie fruits à coque', NULL),
(55, 'CYP1A2', 'Métabolisme de la caféine.'),
(56, 'LCT', 'Persistance de la lactase (digestion du lactose).'),
(57, 'HLA-DQ2', 'Prédisposition coeliaque (sensibilité au gluten).'),
(58, 'HLA-DQ8', 'Prédisposition coeliaque (sensibilité au gluten).'),
(59, 'ALDH2', 'Métabolisme de l’alcool.'),
(60, 'MTHFR', 'Cycle du folate / homocystéine.'),
(61, 'FADS1', 'Métabolisme des oméga-3/6.'),
(62, 'TCF7L2', 'Métabolisme glucidique / risque diabète.'),
(63, 'AMY1', 'Digestion de l’amidon (amylase salivaire).'),
(64, 'SLC23A1', 'Transport de la vitamine C.'),
(65, 'HFE', 'Métabolisme du fer.'),
(66, 'DAO', 'Dégradation de l’histamine (intolérance).'),
(67, 'TAS2R38', 'Perception de l’amertume (ex. brassicacées).'),
(68, 'GSTM1', 'Détoxication hépatique (glutathion S-transférase).'),
(69, 'SOD2', 'Défense antioxydante mitochondriale.');

-- --------------------------------------------------------

--
-- Structure de la table `gene_bienfait`
--

CREATE TABLE `gene_bienfait` (
  `gene_id` int(11) NOT NULL,
  `bienfait_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `gene_bienfait`
--

INSERT INTO `gene_bienfait` (`gene_id`, `bienfait_id`) VALUES
(49, 46),
(50, 46),
(51, 46),
(52, 46),
(53, 58),
(54, 47),
(54, 59),
(55, 48),
(55, 49),
(56, 46),
(57, 46),
(58, 46),
(59, 49),
(59, 51),
(60, 48),
(60, 49),
(61, 49),
(62, 46),
(63, 46),
(64, 48),
(64, 59),
(65, 48),
(66, 46),
(67, 48),
(68, 48),
(68, 51),
(69, 48);

-- --------------------------------------------------------

--
-- Structure de la table `ingredient`
--

CREATE TABLE `ingredient` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `unite` varchar(255) NOT NULL,
  `calories` int(11) DEFAULT NULL,
  `proteines` double DEFAULT NULL,
  `glucides` double DEFAULT NULL,
  `lipides` double DEFAULT NULL,
  `origine` varchar(255) DEFAULT NULL,
  `bio` tinyint(1) NOT NULL DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `allergenes` longtext DEFAULT NULL,
  `saisonnalite` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `ingredient`
--

INSERT INTO `ingredient` (`id`, `nom`, `description`, `unite`, `calories`, `proteines`, `glucides`, `lipides`, `origine`, `bio`, `image`, `type`, `allergenes`, `saisonnalite`) VALUES
(100, 'Concombre', NULL, 'g', 15, 0.7, 3.6, 0.1, 'Maraîchage local', 1, NULL, 'Légume', NULL, 'Été'),
(101, 'Tomate', NULL, 'g', 18, 0.9, 3.9, 0.2, 'Maraîchage local', 1, NULL, 'Légume', NULL, 'Été'),
(102, 'Oignon rouge', NULL, 'g', 40, 1.1, 9.3, 0.1, 'France', 0, NULL, 'Légume', NULL, 'Toute l\'année'),
(103, 'Poivron', NULL, 'g', 26, 0.9, 6, 0.3, 'Espagne', 0, NULL, 'Légume', NULL, 'Été'),
(104, 'Avocat', NULL, 'g', 160, 2, 9, 15, 'Espagne', 0, NULL, 'Fruit', NULL, 'Hiver'),
(105, 'Citron', NULL, 'g', 29, 1.1, 9.3, 0.3, 'Italie', 1, NULL, 'Fruit', NULL, 'Hiver'),
(106, 'Miel', NULL, 'g', 304, 0.3, 82, 0, 'Local', 1, NULL, 'Sucre', NULL, 'Toute l\'année'),
(107, 'Gingembre', NULL, 'g', 80, 1.8, 18, 0.7, 'Inde', 0, NULL, 'Épice', NULL, 'Toute l\'année'),
(108, 'Ail', NULL, 'g', 149, 6.4, 33, 0.5, 'France', 1, NULL, 'Aromate', NULL, 'Toute l\'année'),
(109, 'Huile d\'olive', NULL, 'ml', 884, 0, 0, 100, 'Méditerranée', 1, NULL, 'Matière grasse', NULL, 'Toute l\'année'),
(110, 'Quinoa', NULL, 'g', 368, 14, 64, 6, 'Pérou', 1, NULL, 'Céréale', NULL, 'Toute l\'année'),
(111, 'Riz complet', NULL, 'g', 365, 7.5, 76, 2.7, 'Asie', 0, NULL, 'Céréale', NULL, 'Toute l\'année'),
(112, 'Poulet', NULL, 'g', 165, 31, 0, 3.6, 'France', 0, NULL, 'Viande', NULL, 'Toute l\'année'),
(113, 'Saumon', NULL, 'g', 208, 20, 0, 13, 'Norvège', 0, NULL, 'Poisson', NULL, 'Toute l\'année'),
(114, 'Laitue', NULL, 'g', 15, 1.4, 2.9, 0.2, 'France', 1, NULL, 'Légume', NULL, 'Printemps'),
(115, 'Épinards', NULL, 'g', 23, 2.9, 3.6, 0.4, 'France', 1, NULL, 'Légume', NULL, 'Printemps'),
(116, 'Fromage blanc', NULL, 'g', 98, 10, 3.4, 4.3, 'France', 0, NULL, 'Laitier', NULL, 'Toute l\'année'),
(117, 'Yaourt nature', NULL, 'g', 61, 3.5, 4.7, 3.3, 'France', 0, NULL, 'Laitier', NULL, 'Toute l\'année'),
(118, 'Avoine (flocons)', NULL, 'g', 389, 17, 66, 7, 'UE', 1, NULL, 'Céréale', NULL, 'Toute l\'année'),
(119, 'Amandes', NULL, 'g', 579, 21, 22, 50, 'Espagne', 0, NULL, 'Oléagineux', NULL, 'Automne'),
(120, 'Noix', NULL, 'g', 654, 15, 14, 65, 'France', 1, NULL, 'Oléagineux', NULL, 'Automne'),
(121, 'Banane', NULL, 'g', 89, 1.1, 23, 0.3, 'Amérique du Sud', 0, NULL, 'Fruit', NULL, 'Toute l\'année'),
(122, 'Fraise', NULL, 'g', 33, 0.7, 8, 0.3, 'France', 1, NULL, 'Fruit', NULL, 'Printemps'),
(123, 'Myrtille', NULL, 'g', 57, 0.7, 14, 0.3, 'France', 1, NULL, 'Fruit', NULL, 'Été'),
(124, 'Pomme', NULL, 'g', 52, 0.3, 14, 0.2, 'France', 0, NULL, 'Fruit', NULL, 'Automne'),
(125, 'Carotte', NULL, 'g', 41, 0.9, 10, 0.2, 'France', 1, NULL, 'Légume', NULL, 'Hiver'),
(126, 'Céleri', NULL, 'g', 16, 0.7, 3, 0.2, 'France', 0, NULL, 'Légume', NULL, 'Hiver'),
(127, 'Poireau', NULL, 'g', 61, 1.5, 14, 0.3, 'France', 0, NULL, 'Légume', NULL, 'Hiver'),
(128, 'Brocoli', NULL, 'g', 34, 2.8, 7, 0.4, 'France', 1, NULL, 'Légume', NULL, 'Hiver'),
(129, 'Pois chiches', NULL, 'g', 364, 19, 61, 6, 'Méditerranée', 1, NULL, 'Légumineuse', NULL, 'Toute l\'année'),
(130, 'Lentilles', NULL, 'g', 352, 25, 60, 1.1, 'France', 1, NULL, 'Légumineuse', NULL, 'Toute l\'année'),
(131, 'Oeuf', NULL, 'pièce', 155, 13, 1.1, 11, 'France', 1, NULL, 'Œuf', NULL, 'Toute l\'année'),
(132, 'Lait', NULL, 'ml', 64, 3.4, 5, 3.6, 'France', 0, NULL, 'Laitier', NULL, 'Toute l\'année'),
(133, 'Beurre de cacahuète', NULL, 'g', 588, 25, 20, 50, 'USA', 0, NULL, 'Pâte à tartiner', NULL, 'Toute l\'année'),
(134, 'Café', NULL, 'ml', 2, 0.1, 0, 0, 'Amérique du Sud', 0, NULL, 'Boisson', NULL, 'Toute l\'année'),
(135, 'Thé vert', NULL, 'ml', 0, 0, 0, 0, 'Asie', 1, NULL, 'Boisson', NULL, 'Toute l\'année'),
(136, 'Chocolat noir 70%', NULL, 'g', 600, 7.8, 45, 42, 'Afrique de l\'Ouest', 1, NULL, 'Sucré', NULL, 'Toute l\'année'),
(137, 'Tofu (soja)', NULL, 'g', 76, 8, 1.9, 4.8, 'Asie', 1, NULL, 'Protéine végétale', NULL, 'Toute l\'année'),
(138, 'Pâtes (blé)', NULL, 'g', 371, 13, 75, 1.5, 'Italie', 0, NULL, 'Céréale', NULL, 'Toute l\'année'),
(139, 'Pain complet', NULL, 'g', 247, 13, 41, 4.2, 'France', 0, NULL, 'Céréale', NULL, 'Toute l\'année'),
(140, 'Courgette', NULL, 'g', 17, 1.2, 3.1, 0.3, 'France', 1, NULL, 'Légume', NULL, 'Été'),
(141, 'Aubergine', NULL, 'g', 25, 1, 6, 0.2, 'Espagne', 0, NULL, 'Légume', NULL, 'Été'),
(142, 'Champignon de Paris', NULL, 'g', 22, 3.1, 3.3, 0.3, 'France', 1, NULL, 'Légume', NULL, 'Automne'),
(143, 'Pomme de terre', NULL, 'g', 77, 2, 17, 0.1, 'France', 0, NULL, 'Légume', NULL, 'Hiver'),
(144, 'Patate douce', NULL, 'g', 86, 1.6, 20, 0.1, 'USA', 0, NULL, 'Légume', NULL, 'Automne'),
(145, 'Betterave', NULL, 'g', 43, 1.6, 10, 0.2, 'France', 1, NULL, 'Légume', NULL, 'Automne'),
(146, 'Radis', NULL, 'g', 16, 0.7, 3.4, 0.1, 'France', 1, NULL, 'Légume', NULL, 'Printemps'),
(147, 'Chou-fleur', NULL, 'g', 25, 1.9, 5, 0.3, 'France', 1, NULL, 'Légume', NULL, 'Hiver'),
(148, 'Chou kale', NULL, 'g', 49, 4.3, 9, 0.9, 'France', 1, NULL, 'Légume', NULL, 'Hiver'),
(149, 'Artichaut', NULL, 'g', 47, 3.3, 11, 0.2, 'Italie', 0, NULL, 'Légume', NULL, 'Printemps'),
(150, 'Asperge', NULL, 'g', 20, 2.2, 3.9, 0.1, 'France', 1, NULL, 'Légume', NULL, 'Printemps'),
(151, 'Haricot vert', NULL, 'g', 31, 1.8, 7, 0.1, 'France', 1, NULL, 'Légume', NULL, 'Été'),
(152, 'Orange', NULL, 'g', 47, 0.9, 12, 0.1, 'Espagne', 1, NULL, 'Fruit', NULL, 'Hiver'),
(153, 'Pêche', NULL, 'g', 39, 0.9, 10, 0.3, 'France', 1, NULL, 'Fruit', NULL, 'Été'),
(154, 'Abricot', NULL, 'g', 48, 1.4, 11, 0.4, 'France', 1, NULL, 'Fruit', NULL, 'Été'),
(155, 'Raisin', NULL, 'g', 69, 0.7, 18, 0.2, 'France', 0, NULL, 'Fruit', NULL, 'Automne'),
(156, 'Poire', NULL, 'g', 57, 0.4, 15, 0.1, 'France', 1, NULL, 'Fruit', NULL, 'Automne'),
(157, 'Ananas', NULL, 'g', 50, 0.5, 13, 0.1, 'Costa Rica', 0, NULL, 'Fruit', NULL, 'Été'),
(158, 'Mangue', NULL, 'g', 60, 0.8, 15, 0.4, 'Brésil', 0, NULL, 'Fruit', NULL, 'Été'),
(159, 'Kiwi', NULL, 'g', 61, 1.1, 15, 0.5, 'Nouvelle-Zélande', 0, NULL, 'Fruit', NULL, 'Hiver'),
(160, 'Framboise', NULL, 'g', 52, 1.2, 12, 0.7, 'France', 1, NULL, 'Fruit', NULL, 'Été'),
(161, 'Cerise', NULL, 'g', 50, 1, 12, 0.3, 'France', 1, NULL, 'Fruit', NULL, 'Été'),
(162, 'Prune', NULL, 'g', 46, 0.7, 11, 0.3, 'France', 1, NULL, 'Fruit', NULL, 'Été'),
(163, 'Figue', NULL, 'g', 74, 0.8, 19, 0.3, 'Méditerranée', 1, NULL, 'Fruit', NULL, 'Été'),
(164, 'Grenade', NULL, 'g', 83, 1.7, 19, 1.2, 'Espagne', 0, NULL, 'Fruit', NULL, 'Automne'),
(165, 'Basilic', NULL, 'g', 23, 3.2, 2.7, 0.6, 'Italie', 1, NULL, 'Aromate', NULL, 'Été'),
(166, 'Persil', NULL, 'g', 36, 3, 6.3, 0.8, 'France', 1, NULL, 'Aromate', NULL, 'Toute l\'année'),
(167, 'Coriandre', NULL, 'g', 23, 2.1, 3.7, 0.5, 'Maroc', 1, NULL, 'Aromate', NULL, 'Toute l\'année'),
(168, 'Aneth', NULL, 'g', 43, 3.5, 7, 1.1, 'Pologne', 0, NULL, 'Aromate', NULL, 'Été'),
(169, 'Ciboulette', NULL, 'g', 30, 3.3, 4.4, 0.7, 'France', 1, NULL, 'Aromate', NULL, 'Printemps'),
(170, 'Thym (sec)', NULL, 'g', 276, 9.1, 63, 7.4, 'France', 1, NULL, 'Aromate', NULL, 'Toute l\'année'),
(171, 'Curcuma', NULL, 'g', 312, 9.7, 67, 3.3, 'Inde', 0, NULL, 'Épice', NULL, 'Toute l\'année'),
(172, 'Cumin', NULL, 'g', 375, 18, 44, 22, 'Maghreb', 0, NULL, 'Épice', NULL, 'Toute l\'année'),
(173, 'Paprika', NULL, 'g', 282, 14, 54, 13, 'Hongrie', 0, NULL, 'Épice', NULL, 'Toute l\'année'),
(174, 'Cannelle', NULL, 'g', 247, 4, 81, 1.2, 'Sri Lanka', 0, NULL, 'Épice', NULL, 'Toute l\'année'),
(175, 'Poivre noir', NULL, 'g', 251, 10, 64, 3.3, 'Inde', 0, NULL, 'Épice', NULL, 'Toute l\'année'),
(176, 'Graine de fenouil', NULL, 'g', 345, 16, 52, 15, 'Italie', 0, NULL, 'Épice', NULL, 'Toute l\'année'),
(177, 'Cardamome', NULL, 'g', 311, 11, 68, 7, 'Guatemala', 0, NULL, 'Épice', NULL, 'Toute l\'année'),
(178, 'Beurre', NULL, 'g', 717, 0.9, 0.1, 81, 'France', 0, NULL, 'Matière grasse', NULL, 'Toute l\'année'),
(179, 'Huile de colza', NULL, 'ml', 884, 0, 0, 100, 'France', 1, NULL, 'Matière grasse', NULL, 'Toute l\'année'),
(180, 'Crème fraîche', NULL, 'g', 292, 2.3, 2.9, 30, 'France', 0, NULL, 'Laitier', NULL, 'Toute l\'année'),
(181, 'Sucre de canne', NULL, 'g', 387, 0, 100, 0, 'Brésil', 0, NULL, 'Sucre', NULL, 'Toute l\'année'),
(182, 'Sirop d\'érable', NULL, 'g', 260, 0, 67, 0, 'Canada', 1, NULL, 'Sucre', NULL, 'Hiver'),
(183, 'Boulgour', NULL, 'g', 342, 12, 76, 1.3, 'Turquie', 0, NULL, 'Céréale', NULL, 'Toute l\'année'),
(184, 'Orge', NULL, 'g', 354, 12, 73, 2.3, 'France', 0, NULL, 'Céréale', NULL, 'Toute l\'année'),
(185, 'Semoule de blé', NULL, 'g', 360, 12, 72, 1, 'Maroc', 0, NULL, 'Céréale', NULL, 'Toute l\'année'),
(186, 'Sarrasin', NULL, 'g', 343, 13, 71, 3.4, 'France', 1, NULL, 'Céréale', NULL, 'Toute l\'année'),
(187, 'Millet', NULL, 'g', 378, 11, 73, 4.2, 'Niger', 0, NULL, 'Céréale', NULL, 'Toute l\'année'),
(188, 'Haricots rouges', NULL, 'g', 333, 24, 60, 1, 'Mexique', 0, NULL, 'Légumineuse', NULL, 'Toute l\'année'),
(189, 'Haricots noirs', NULL, 'g', 339, 21, 63, 0.9, 'Brésil', 0, NULL, 'Légumineuse', NULL, 'Toute l\'année'),
(190, 'Fèves', NULL, 'g', 341, 26, 58, 1.5, 'Méditerranée', 1, NULL, 'Légumineuse', NULL, 'Printemps'),
(191, 'Edamame', NULL, 'g', 121, 11, 8.9, 5.2, 'Japon', 1, NULL, 'Légumineuse', NULL, 'Été'),
(192, 'Bœuf', NULL, 'g', 250, 26, 0, 15, 'France', 0, NULL, 'Viande', NULL, 'Toute l\'année'),
(193, 'Porc', NULL, 'g', 242, 27, 0, 14, 'France', 0, NULL, 'Viande', NULL, 'Toute l\'année'),
(194, 'Dinde', NULL, 'g', 135, 29, 0, 1.5, 'France', 0, NULL, 'Viande', NULL, 'Toute l\'année'),
(195, 'Agneau', NULL, 'g', 294, 25, 0, 21, 'France', 0, NULL, 'Viande', NULL, 'Printemps'),
(196, 'Thon', NULL, 'g', 144, 23, 0, 5, 'Océan Indien', 0, NULL, 'Poisson', NULL, 'Toute l\'année'),
(197, 'Cabillaud', NULL, 'g', 82, 18, 0, 0.7, 'Atlantique Nord', 0, NULL, 'Poisson', NULL, 'Hiver'),
(198, 'Sardine', NULL, 'g', 208, 25, 0, 11, 'Méditerranée', 0, NULL, 'Poisson', NULL, 'Été'),
(199, 'Crevette', NULL, 'g', 99, 24, 0.2, 0.3, 'Vietnam', 0, NULL, 'Poisson', NULL, 'Toute l\'année'),
(200, 'Fromage de chèvre', NULL, 'g', 364, 22, 2.2, 30, 'France', 0, NULL, 'Laitier', NULL, 'Toute l\'année'),
(201, 'Parmesan', NULL, 'g', 431, 38, 4.1, 29, 'Italie', 0, NULL, 'Laitier', NULL, 'Toute l\'année'),
(202, 'Mozzarella', NULL, 'g', 280, 28, 3.1, 17, 'Italie', 0, NULL, 'Laitier', NULL, 'Toute l\'année'),
(203, 'Noisettes', NULL, 'g', 628, 15, 17, 61, 'Turquie', 0, NULL, 'Oléagineux', NULL, 'Automne'),
(204, 'Pistaches', NULL, 'g', 562, 20, 28, 45, 'Iran', 0, NULL, 'Oléagineux', NULL, 'Automne'),
(205, 'Noix de cajou', NULL, 'g', 553, 18, 30, 44, 'Côte d\'Ivoire', 0, NULL, 'Oléagineux', NULL, 'Automne'),
(206, 'Pignons de pin', NULL, 'g', 673, 14, 13, 68, 'Italie', 0, NULL, 'Oléagineux', NULL, 'Automne'),
(207, 'Jus d\'orange', NULL, 'ml', 45, 0.7, 10, 0.2, 'Espagne', 1, NULL, 'Boisson', NULL, 'Hiver'),
(208, 'Lait d\'amande', NULL, 'ml', 15, 0.6, 0.3, 1.2, 'UE', 1, NULL, 'Boisson', NULL, 'Toute l\'année'),
(209, 'Kombucha', NULL, 'ml', 12, 0, 3, 0, 'Artisanal', 1, NULL, 'Boisson', NULL, 'Toute l\'année'),
(210, 'Chocolat au lait', NULL, 'g', 535, 7.7, 59, 30, 'UE', 0, NULL, 'Sucré', NULL, 'Toute l\'année'),
(211, 'Caramel', NULL, 'g', 382, 0, 97, 0, 'France', 0, NULL, 'Sucré', NULL, 'Toute l\'année'),
(212, 'Pâte à tartiner choco-noisette', NULL, 'g', 539, 6.3, 57, 31, 'France', 0, NULL, 'Pâte à tartiner', NULL, 'Toute l\'année'),
(213, 'Tempeh', NULL, 'g', 195, 20, 9.4, 11, 'Indonésie', 1, NULL, 'Protéine végétale', NULL, 'Toute l\'année'),
(214, 'Seitan', NULL, 'g', 370, 75, 14, 1.9, 'Asie', 0, NULL, 'Protéine végétale', NULL, 'Toute l\'année');

-- --------------------------------------------------------

--
-- Structure de la table `ingredient_gene`
--

CREATE TABLE `ingredient_gene` (
  `ingredient_id` int(11) NOT NULL,
  `gene_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `ingredient_gene`
--

INSERT INTO `ingredient_gene` (`ingredient_id`, `gene_id`) VALUES
(105, 64),
(109, 61),
(110, 62),
(110, 63),
(111, 62),
(111, 63),
(113, 61),
(115, 60),
(115, 65),
(116, 56),
(116, 66),
(117, 56),
(117, 66),
(118, 62),
(118, 63),
(122, 64),
(122, 69),
(123, 68),
(123, 69),
(128, 67),
(128, 68),
(130, 60),
(130, 65),
(132, 56),
(132, 66),
(134, 55),
(135, 55),
(135, 69),
(136, 69),
(137, 57),
(137, 58),
(138, 57),
(138, 58),
(138, 62),
(138, 63),
(139, 57),
(139, 58),
(139, 62),
(139, 63),
(143, 63),
(144, 63),
(147, 68),
(148, 60),
(148, 65),
(152, 64),
(153, 64),
(154, 64),
(159, 64),
(160, 69),
(164, 69),
(166, 64),
(178, 56),
(179, 61),
(180, 56),
(180, 66),
(183, 57),
(183, 58),
(183, 62),
(183, 63),
(184, 57),
(184, 58),
(184, 62),
(184, 63),
(185, 57),
(185, 58),
(185, 62),
(185, 63),
(186, 62),
(186, 63),
(187, 62),
(187, 63),
(191, 57),
(191, 58),
(196, 61),
(198, 61),
(200, 56),
(200, 66),
(201, 56),
(201, 66),
(202, 56),
(202, 66),
(207, 64),
(213, 57),
(213, 58),
(214, 57),
(214, 58);

-- --------------------------------------------------------

--
-- Structure de la table `messenger_messages`
--

CREATE TABLE `messenger_messages` (
  `id` bigint(20) NOT NULL,
  `body` longtext NOT NULL,
  `headers` longtext NOT NULL,
  `queue_name` varchar(190) NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `available_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `delivered_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `messenger_messages`
--

INSERT INTO `messenger_messages` (`id`, `body`, `headers`, `queue_name`, `created_at`, `available_at`, `delivered_at`) VALUES
(1, 'O:36:\\\"Symfony\\\\Component\\\\Messenger\\\\Envelope\\\":2:{s:44:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0stamps\\\";a:1:{s:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\";a:1:{i:0;O:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\":1:{s:55:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\0busName\\\";s:21:\\\"messenger.bus.default\\\";}}}s:45:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0message\\\";O:51:\\\"Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\\":2:{s:60:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0message\\\";O:39:\\\"Symfony\\\\Bridge\\\\Twig\\\\Mime\\\\TemplatedEmail\\\":5:{i:0;s:29:\\\"email/contact_email.html.twig\\\";i:1;s:28:\\\"email/contact_email.txt.twig\\\";i:2;a:4:{s:4:\\\"name\\\";s:17:\\\"Ahlem Ben hamouda\\\";s:10:\\\"user_email\\\";s:17:\\\"bhahlem3@yahoo.fr\\\";s:7:\\\"subject\\\";s:4:\\\"test\\\";s:7:\\\"message\\\";s:109:\\\"Bonjour <strong>équipe</strong>\n\n<a>clique</a>\n<a href=\\\"/ma-page\\\" target=\\\"_blank\\\" rel=\\\"noopener\\\">interne</a>\\\";}i:3;a:6:{i:0;N;i:1;N;i:2;N;i:3;N;i:4;a:0:{}i:5;a:2:{i:0;O:37:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\\":2:{s:46:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0headers\\\";a:4:{s:4:\\\"from\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:4:\\\"From\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:27:\\\"no-reply@sante-nature.local\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:15:\\\"Santé & Nature\\\";}}}}s:2:\\\"to\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:2:\\\"To\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:20:\\\"rofranebh1@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:0:\\\"\\\";}}}}s:8:\\\"reply-to\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:8:\\\"Reply-To\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:17:\\\"bhahlem3@yahoo.fr\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:17:\\\"Ahlem Ben hamouda\\\";}}}}s:7:\\\"subject\\\";a:1:{i:0;O:48:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:7:\\\"Subject\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:55:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\0value\\\";s:14:\\\"[Contact] test\\\";}}}s:49:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0lineLength\\\";i:76;}i:1;N;}}i:4;N;}s:61:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0envelope\\\";N;}}', '[]', 'default', '2025-10-02 20:03:28', '2025-10-02 20:03:28', NULL),
(2, 'O:36:\\\"Symfony\\\\Component\\\\Messenger\\\\Envelope\\\":2:{s:44:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0stamps\\\";a:1:{s:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\";a:1:{i:0;O:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\":1:{s:55:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\0busName\\\";s:21:\\\"messenger.bus.default\\\";}}}s:45:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0message\\\";O:51:\\\"Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\\":2:{s:60:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0message\\\";O:39:\\\"Symfony\\\\Bridge\\\\Twig\\\\Mime\\\\TemplatedEmail\\\":5:{i:0;s:29:\\\"email/contact_email.html.twig\\\";i:1;s:28:\\\"email/contact_email.txt.twig\\\";i:2;a:4:{s:4:\\\"name\\\";s:17:\\\"Ahlem Ben hamouda\\\";s:10:\\\"user_email\\\";s:20:\\\"rofranebh1@gmail.com\\\";s:7:\\\"subject\\\";s:4:\\\"test\\\";s:7:\\\"message\\\";s:32:\\\"Bonjour <strong>équipe</strong>\\\";}i:3;a:6:{i:0;N;i:1;N;i:2;N;i:3;N;i:4;a:0:{}i:5;a:2:{i:0;O:37:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\\":2:{s:46:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0headers\\\";a:4:{s:4:\\\"from\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:4:\\\"From\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:27:\\\"no-reply@sante-nature.local\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:15:\\\"Santé & Nature\\\";}}}}s:2:\\\"to\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:2:\\\"To\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:20:\\\"rofranebh1@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:0:\\\"\\\";}}}}s:8:\\\"reply-to\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:8:\\\"Reply-To\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:20:\\\"rofranebh1@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:17:\\\"Ahlem Ben hamouda\\\";}}}}s:7:\\\"subject\\\";a:1:{i:0;O:48:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:7:\\\"Subject\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:55:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\0value\\\";s:14:\\\"[Contact] test\\\";}}}s:49:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0lineLength\\\";i:76;}i:1;N;}}i:4;N;}s:61:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0envelope\\\";N;}}', '[]', 'default', '2025-10-02 20:20:47', '2025-10-02 20:20:47', NULL),
(3, 'O:36:\\\"Symfony\\\\Component\\\\Messenger\\\\Envelope\\\":2:{s:44:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0stamps\\\";a:1:{s:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\";a:1:{i:0;O:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\":1:{s:55:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\0busName\\\";s:21:\\\"messenger.bus.default\\\";}}}s:45:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0message\\\";O:51:\\\"Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\\":2:{s:60:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0message\\\";O:39:\\\"Symfony\\\\Bridge\\\\Twig\\\\Mime\\\\TemplatedEmail\\\":5:{i:0;s:30:\\\"reset_password/email.html.twig\\\";i:1;N;i:2;a:1:{s:10:\\\"resetToken\\\";O:58:\\\"SymfonyCasts\\\\Bundle\\\\ResetPassword\\\\Model\\\\ResetPasswordToken\\\":4:{s:65:\\\"\\0SymfonyCasts\\\\Bundle\\\\ResetPassword\\\\Model\\\\ResetPasswordToken\\0token\\\";s:40:\\\"d3m4A6u4iO0SMOwT6fg5wDxitxcwbJkQ6KAtqzcI\\\";s:69:\\\"\\0SymfonyCasts\\\\Bundle\\\\ResetPassword\\\\Model\\\\ResetPasswordToken\\0expiresAt\\\";O:17:\\\"DateTimeImmutable\\\":3:{s:4:\\\"date\\\";s:26:\\\"2025-10-03 20:25:55.207400\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}s:71:\\\"\\0SymfonyCasts\\\\Bundle\\\\ResetPassword\\\\Model\\\\ResetPasswordToken\\0generatedAt\\\";i:1759519555;s:73:\\\"\\0SymfonyCasts\\\\Bundle\\\\ResetPassword\\\\Model\\\\ResetPasswordToken\\0transInterval\\\";i:1;}}i:3;a:6:{i:0;N;i:1;N;i:2;N;i:3;N;i:4;a:0:{}i:5;a:2:{i:0;O:37:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\\":2:{s:46:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0headers\\\";a:3:{s:4:\\\"from\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:4:\\\"From\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:20:\\\"rofranebh1@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:26:\\\"Administrateur_HealthyFood\\\";}}}}s:2:\\\"to\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:2:\\\"To\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:17:\\\"bhahlem3@yahoo.fr\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:0:\\\"\\\";}}}}s:7:\\\"subject\\\";a:1:{i:0;O:48:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:7:\\\"Subject\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:55:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\0value\\\";s:27:\\\"Your password reset request\\\";}}}s:49:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0lineLength\\\";i:76;}i:1;N;}}i:4;N;}s:61:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0envelope\\\";N;}}', '[]', 'default', '2025-10-03 19:25:55', '2025-10-03 19:25:55', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `plante`
--

CREATE TABLE `plante` (
  `id` int(11) NOT NULL,
  `nom_commun` varchar(255) NOT NULL,
  `nom_scientifique` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `partie_utilisee` varchar(255) NOT NULL,
  `precautions` longtext NOT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `plante`
--

INSERT INTO `plante` (`id`, `nom_commun`, `nom_scientifique`, `description`, `partie_utilisee`, `precautions`, `image`) VALUES
(91, 'Camomille matricaire', 'Matricaria chamomilla', 'Fleurs aromatiques utilisées pour favoriser la relaxation, la digestion et le sommeil.', 'Capitules (fleurs)', 'Allergie possible aux Astéracées. Déconseillée en cas d’allergie connue.', 'camomille.jpg'),
(92, 'Menthe poivrée', 'Mentha × piperita', 'Feuilles rafraîchissantes, utiles après les repas et pour la sphère respiratoire.', 'Feuilles', 'Peut majorer le reflux gastro-œsophagien. Éviter chez l’enfant < 6 ans en HE.', 'menthe poivreé.jpg'),
(93, 'Verveine odorante', 'Aloysia citrodora', 'Plante au parfum citronné, apaisante et digestive.', 'Feuilles', 'Aucune particulière aux doses usuelles.', 'vervaine odorante.jpg'),
(94, 'Tilleul', 'Tilia cordata', 'Sommités fleuries calmantes, soutiennent le sommeil.', 'Bractées et fleurs', 'Aucune particulière aux doses usuelles.', 'tilleul.png'),
(95, 'Fenouil', 'Foeniculum vulgare', 'Graines carminatives pour la digestion et les spasmes.', 'Graines (akènes)', 'Attention aux antécédents hormonodépendants en usage prolongé.', 'fenouil.png'),
(96, 'Hibiscus sabdariffa', 'Hibiscus sabdariffa', 'Calyces riches en anthocyanes, boisson acidulée antioxydante.', 'Calyces', 'Déconseillé pendant la grossesse; prudence si tension basse ou antihypertenseurs.', 'hibiscus-sabdariffa.png'),
(97, 'Gingembre', 'Zingiber officinale', 'Rhizome tonique et antinauséeux, utile aussi pour la digestion.', 'Rhizome', 'Prudence avec anticoagulants et troubles hémorragiques.', 'gingembre.png'),
(98, 'Citronnelle', 'Cymbopogon citratus', 'Saveur citronnée, apaisante et digestive.', 'Feuilles', 'Aucune particulière aux doses usuelles.', 'citronnelle.jpg'),
(99, 'Sauge officinale', 'Salvia officinalis', 'Feuilles aromatiques, respiratoire et digestive.', 'Feuilles', 'Déconseillée grossesse/allaitement; prudence en cas d’antécédent convulsif (thuyone).', 'sauge_officinale.png'),
(100, 'Lavande vraie', 'Lavandula angustifolia', 'Sommités fleuries relaxantes, favorisent le sommeil.', 'Sommités fleuries', 'Aucune particulière aux doses usuelles.', 'lavande.jpg'),
(101, 'Thym', 'Thymus vulgaris', 'Antiseptique des voies respiratoires, aussi digestif.', 'Sommités fleuries', 'HE dermocaustique; infusion OK aux doses usuelles.', 'thym.png'),
(102, 'Romarin', 'Rosmarinus officinalis', 'Plante tonique et digestive, soutien hépatique.', 'Feuilles', 'Éviter en cas d’hypertension non contrôlée (HE); infusion OK modération.', 'romarin.png'),
(103, 'Mélisse', 'Melissa officinalis', 'Apaisante, digestive et légèrement sédative.', 'Feuilles', 'Aucune particulière aux doses usuelles.', 'melisse.jpg'),
(104, 'Ortie', 'Urtica dioica', 'Feuilles reminéralisantes, effet diurétique doux.', 'Feuilles', 'Diurétique léger; surveiller si traitement diurétique.', 'ortie.png'),
(105, 'Réglisse', 'Glycyrrhiza glabra', 'Racine adoucissante digestive; peut élever la tension à fortes doses.', 'Racine', 'Déconseillée en cas d’hypertension, rétention hydrique ou insuffisance rénale.', 'reglisse.png'),
(106, 'Passiflore', 'Passiflora incarnata', 'Sédative légère pour stress et insomnie d’endormissement.', 'Parties aériennes', 'Somnolence possible; prudence si conduite.', 'passiflore.png'),
(107, 'Valériane', 'Valeriana officinalis', 'Racine sédative, facilite l’endormissement.', 'Racine', 'Somnolence; éviter avec sédatifs sans avis médical.', 'valeriane.png'),
(108, 'Bardane', 'Arctium lappa', 'Racine dépurative, utile pour la peau et le foie.', 'Racine', 'Diurétique doux; prudence si traitement hypoglycémiant.', 'bardane.png'),
(109, 'Échinacée', 'Echinacea purpurea', 'Stimulation de l’immunité en cures courtes.', 'Parties aériennes/racine', 'Éviter maladies auto-immunes sans avis médical.', 'echinacee.png'),
(110, 'Pissenlit', 'Taraxacum officinale', 'Feuilles et racines dépuratives; soutien hépatobiliaire.', 'Feuilles et racines', 'Calculs biliaires : avis médical préalable.', 'pissenlit.png'),
(111, 'Achillée millefeuille', 'Achillea millefolium', 'Plante digestive et antispasmodique légère.', 'Sommités fleuries', 'Allergie possible aux Astéracées.', 'achillee_millefeuille.png'),
(112, 'Anis vert', 'Pimpinella anisum', 'Graines carminatives et digestives, saveur anisée.', 'Graines', 'Aucune particulière aux doses usuelles.', 'anis vert.jpg'),
(113, 'Basilic sacré (Tulsi)', 'Ocimum tenuiflorum', 'Adaptogène doux, antistress et respiratoire.', 'Feuilles', 'Aucune particulière aux doses usuelles.', 'basilic_sacre_tulsi.png'),
(114, 'Curcuma', 'Curcuma longa', 'Rhizome anti-inflammatoire et digestif.', 'Rhizome', 'Prudence anticoagulants, calculs biliaires.', 'curcuma.png'),
(115, 'Eucalyptus globuleux', 'Eucalyptus globulus', 'Feuilles pour voies respiratoires, expectorant.', 'Feuilles', 'HE à éviter chez l’enfant; infusion modérée.', 'eucalyptus_globuleux.png'),
(116, 'Guimauve', 'Althaea officinalis', 'Racine/feuilles émollientes pour muqueuses.', 'Racine et feuilles', 'Peut ralentir l’absorption de médicaments (mucilages).', 'guimauve.jpg'),
(117, 'Millepertuis', 'Hypericum perforatum', 'Plante de l’humeur; interactions médicamenteuses.', 'Sommités fleuries', 'Nombreuses interactions (CYP3A4). Avis médical.', 'millepertuis.jpg'),
(118, 'Plantain lancéolé', 'Plantago lanceolata', 'Feuilles adoucissantes, respiratoire et peau.', 'Feuilles', 'Aucune particulière aux doses usuelles.', 'plantain_lanceole.png'),
(119, 'Sureau noir', 'Sambucus nigra', 'Fleurs/baies pour états grippaux, sueur/défense.', 'Fleurs et baies', 'Baies crues laxatives; cuire/infuser.', 'sureau_noir.png'),
(120, 'Souci (Calendula)', 'Calendula officinalis', 'Fleurs adoucissantes et cicatrisantes.', 'Capitules (fleurs)', 'Allergie possible aux Astéracées.', 'souci_calendula.png'),
(121, 'Chardon-Marie', 'Silybum marianum', 'Soutien hépatique (silymarine), antioxydant.', 'Graine', 'Aucune particulière aux doses usuelles.', 'chardon_marie.png'),
(122, 'Ortosiphon (Thé de Java)', 'Orthosiphon aristatus', 'Diurétique doux, draineur rénal.', 'Feuilles', 'Insuffisance rénale : avis médical.', 'ortosiphon.jpg'),
(123, 'TEST', 'TEST', 'TEST', 'TEST', 'TEST', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `plante_bienfait`
--

CREATE TABLE `plante_bienfait` (
  `plante_id` int(11) NOT NULL,
  `bienfait_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `plante_bienfait`
--

INSERT INTO `plante_bienfait` (`plante_id`, `bienfait_id`) VALUES
(91, 44),
(91, 45),
(91, 46),
(91, 54),
(92, 46),
(92, 47),
(92, 60),
(93, 44),
(93, 45),
(93, 46),
(93, 61),
(94, 44),
(94, 45),
(94, 62),
(95, 46),
(95, 54),
(95, 60),
(96, 48),
(96, 49),
(96, 63),
(97, 46),
(97, 50),
(97, 53),
(97, 64),
(98, 44),
(98, 46),
(99, 46),
(99, 47),
(99, 53),
(100, 44),
(100, 45),
(100, 61),
(101, 46),
(101, 47),
(101, 65),
(101, 66),
(102, 46),
(102, 48),
(102, 50),
(102, 67),
(103, 44),
(103, 45),
(103, 46),
(103, 54),
(104, 48),
(104, 63),
(105, 46),
(105, 53),
(105, 65),
(106, 44),
(106, 45),
(106, 62),
(107, 45),
(107, 62),
(108, 51),
(108, 63),
(108, 67),
(109, 48),
(109, 59),
(110, 51),
(110, 63),
(110, 67),
(111, 46),
(111, 54),
(111, 68),
(112, 46),
(112, 60),
(113, 47),
(113, 48),
(113, 61),
(114, 46),
(114, 48),
(114, 53),
(115, 47),
(115, 69),
(116, 46),
(116, 70),
(116, 71),
(117, 68),
(117, 72),
(117, 73),
(118, 47),
(118, 68),
(118, 71),
(119, 48),
(119, 59),
(119, 74),
(120, 53),
(120, 68),
(120, 71),
(121, 48),
(121, 51),
(121, 75),
(122, 63),
(122, 76),
(123, 55);

-- --------------------------------------------------------

--
-- Structure de la table `recette`
--

CREATE TABLE `recette` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `instructions` longtext NOT NULL,
  `temps_preparation` int(11) DEFAULT NULL,
  `difficulte` varchar(255) DEFAULT NULL,
  `validation` tinyint(1) NOT NULL,
  `portions` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `temps_cuisson` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `recette`
--

INSERT INTO `recette` (`id`, `utilisateur_id`, `titre`, `description`, `instructions`, `temps_preparation`, `difficulte`, `validation`, `portions`, `image`, `temps_cuisson`, `created_at`) VALUES
(42, 21, 'Salade fraîcheur citronnée', 'Concombre, tomate et citron pour une entrée légère.', 'Couper le concombre et la tomate en dés. Arroser de jus de citron et d\'un filet d\'huile d\'olive. Saler, poivrer.', 10, 'Facile', 1, 2, 'Salade_fraîcheur_citronnée.jpg', 0, '2025-08-26 16:04:22'),
(43, 21, 'Buddha bowl quinoa-saumon', 'Un bol complet aux bons lipides et protéines.', 'Cuire le quinoa. Cuire le saumon à la poêle. Dresser avec avocat, épinards et un filet de citron.', 20, 'Moyen', 1, 2, 'Buddha_bowl_quinoa_saumon.jpg', 10, '2025-08-26 16:04:22'),
(44, 21, 'Wrap poulet-avocat', 'Snack protéiné et rapide.', 'Cuire le poulet, l\'émincer. Garnir une base salade avec avocat et tomate. Rouler.', 15, 'Facile', 1, 2, 'Wrap_poulet_avocat.jpg', 8, '2025-08-26 16:04:22'),
(45, 21, 'Porridge avoine-fruits rouges', 'Petit-déj\' rassasiant et antioxydant.', 'Chauffer le lait, ajouter les flocons d\'avoine, cuire doucement. Servir avec fraises et myrtilles.', 5, 'Facile', 1, 2, 'Porridge_avoine_fruits_rouges.jpg', 8, '2025-08-26 16:04:22'),
(46, 21, 'Soupe carotte-poireau', 'Velouté léger pour le soir.', 'Faire revenir poireau et carotte, couvrir d\'eau, mijoter puis mixer.', 10, 'Facile', 1, 3, 'Soupe_carotte_poireau.jpg', 20, '2025-08-26 16:04:22'),
(47, 21, 'Curry de pois chiches au brocoli', 'Végétarien, riche en fibres.', 'Saisir ail et gingembre, ajouter pois chiches et brocoli, mijoter avec épices et un peu d\'eau.', 15, 'Moyen', 1, 2, 'Curry_de_pois_chiches_au_brocoli.jpg', 15, '2025-08-26 16:04:22'),
(48, 21, 'Salade lentilles-épinards', 'Source de fer végétal.', 'Cuire les lentilles. Mélanger avec épinards, oignon rouge, citron et huile d\'olive.', 15, 'Facile', 1, 2, 'Salade_lentilles_épinards.jpg', 20, '2025-08-26 16:04:22'),
(49, 21, 'Riz sauté poulet-légumes', 'Plat complet familial.', 'Cuire le riz. Saisir poulet, ajouter poivron et carotte, puis le riz. Assaisonner.', 15, 'Moyen', 1, 3, 'Riz_sauté_poulet_légumes.jpg', 15, '2025-08-26 16:04:22'),
(50, 21, 'Pâtes brocoli-citron', 'Version légère et parfumée.', 'Cuire les pâtes. Blanchir le brocoli. Mélanger avec jus de citron, ail et un filet d\'huile.', 10, 'Facile', 1, 2, 'Pâtes_brocoli_citron.jpg', 12, '2025-08-26 16:04:22'),
(51, 21, 'Quinoa bowl avocat-tomate', 'Bowl rapide et veggie.', 'Cuire le quinoa. Dresser avec tomate, avocat, citron, huile, sel, poivre.', 15, 'Facile', 1, 2, 'Quinoa_bowl_avocat_tomate.jpg', 12, '2025-08-26 16:04:22'),
(52, 21, 'Omelette épinards-fromage blanc', 'Protéinée et moelleuse.', 'Battre les œufs, ajouter épinards et fromage blanc. Cuire doucement à la poêle.', 8, 'Facile', 1, 2, 'Omelette_épinards_fromage_blanc.jpg', 6, '2025-08-26 16:04:22'),
(53, 21, 'Smoothie banane-mytille-yaourt', 'Goûter express antioxydant.', 'Mixer banane, myrtilles, yaourt et un trait de lait si besoin.', 5, 'Facile', 1, 2, 'Smoothie_banane_mytille_yaourt.jpg', 0, '2025-08-26 16:04:22'),
(54, 21, 'Tofu sauté aux légumes', 'Végé rapide et équilibré.', 'Saisir tofu, ajouter poivron et brocoli, assaisonner, servir avec riz.', 12, 'Moyen', 1, 2, 'Tofu_sauté_aux_légumes.jpg', 10, '2025-08-26 16:04:22'),
(55, 21, 'Salade pomme-noix-concombre', 'Croquante et fraîche.', 'Couper pomme et concombre. Ajouter noix et un filet d\'huile d\'olive.', 10, 'Facile', 1, 2, 'Salade_pomme_noix_concombre.jpg', 0, '2025-08-26 16:04:22'),
(56, 21, 'Bol protéiné poulet-quinoa', 'Post-entraînement.', 'Cuire quinoa et poulet. Dresser avec avocat et épinards.', 20, 'Moyen', 1, 2, 'Bol_protéiné_poulet_quinoa.jpg', 15, '2025-08-26 16:04:22'),
(57, 24, 'Salade chou-rave, pomme chèvre et noix', 'Les noix peuvent être remplacées par des noisettes.', 'Épluchez le chou-rave et détaillez-le en spaghettis à l\'aide d\'un spiralizer.\r\nLavez la pomme et émincez-la finement.\r\nMettez dans un saladier la roquette rincée et égouttée, le chou-rave, la pomme, le chèvre émietté, les noix hachées et les cramberries séchées.\r\nFouettez l\'huile avec le vinaigre, du sel et du poivre. Nappez-en la salade, mélangez et servez.', 20, 'Facile', 0, 4, 'salade-chou-rave-pomme-chevre-et-noix-68b0ab6a5cce7.jpg', 0, '2025-08-28 19:18:01'),
(59, 25, 'test', 'test', 'testttt   bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb', 10, 'Facile', 0, 1, 'chili-con-carne-68b4490fb5d47.jpg', 10, '2025-08-31 13:04:07');

-- --------------------------------------------------------

--
-- Structure de la table `recette_ingredient`
--

CREATE TABLE `recette_ingredient` (
  `id_recette` int(11) NOT NULL,
  `id_ingredient` int(11) NOT NULL,
  `quantite` double NOT NULL,
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `recette_ingredient`
--

INSERT INTO `recette_ingredient` (`id_recette`, `id_ingredient`, `quantite`, `id`) VALUES
(42, 100, 150, 1),
(42, 101, 150, 2),
(42, 105, 20, 3),
(42, 109, 10, 4),
(43, 104, 100, 5),
(43, 105, 15, 6),
(43, 110, 120, 7),
(43, 113, 200, 8),
(43, 115, 60, 9),
(44, 101, 120, 10),
(44, 104, 100, 11),
(44, 112, 200, 12),
(44, 114, 80, 13),
(45, 118, 80, 14),
(45, 122, 80, 15),
(45, 123, 80, 16),
(45, 132, 300, 17),
(46, 109, 10, 18),
(46, 125, 300, 19),
(46, 127, 200, 20),
(47, 107, 10, 21),
(47, 108, 10, 22),
(47, 128, 250, 23),
(47, 129, 200, 24),
(48, 102, 50, 25),
(48, 105, 15, 26),
(48, 109, 10, 27),
(48, 115, 80, 28),
(48, 130, 160, 29),
(49, 103, 150, 30),
(49, 109, 15, 31),
(49, 111, 200, 32),
(49, 112, 250, 33),
(49, 125, 150, 34),
(50, 105, 10, 35),
(50, 108, 8, 36),
(50, 109, 10, 37),
(50, 128, 200, 38),
(50, 138, 180, 39),
(51, 101, 150, 40),
(51, 104, 100, 41),
(51, 105, 10, 42),
(51, 109, 10, 43),
(51, 110, 120, 44),
(52, 115, 80, 45),
(52, 116, 80, 46),
(52, 131, 3, 47),
(53, 117, 200, 48),
(53, 121, 120, 49),
(53, 123, 120, 50),
(54, 103, 150, 51),
(54, 111, 150, 52),
(54, 128, 200, 53),
(54, 137, 200, 54),
(55, 100, 150, 55),
(55, 109, 8, 56),
(55, 120, 25, 57),
(55, 124, 150, 58),
(56, 104, 80, 59),
(56, 110, 120, 60),
(56, 112, 220, 61),
(56, 115, 60, 62),
(57, 109, 10, 63),
(57, 115, 10, 64),
(57, 120, 10, 65),
(57, 124, 20, 66),
(57, 148, 200, 67),
(57, 175, 2, 68),
(57, 200, 100, 69),
(59, 122, 50, 70),
(59, 134, 100, 71);

-- --------------------------------------------------------

--
-- Structure de la table `reset_password_request`
--

CREATE TABLE `reset_password_request` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `selector` varchar(20) NOT NULL,
  `hashed_token` varchar(100) NOT NULL,
  `requested_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `expires_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `reset_password_request`
--

INSERT INTO `reset_password_request` (`id`, `user_id`, `selector`, `hashed_token`, `requested_at`, `expires_at`) VALUES
(1, 27, 'd3m4A6u4iO0SMOwT6fg5', 'xdt+e16lZw8SpiYTts6PZgNccMQhEdp863/VytVmgF0=', '2025-10-03 19:25:55', '2025-10-03 20:25:55');

-- --------------------------------------------------------

--
-- Structure de la table `tisane`
--

CREATE TABLE `tisane` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `mode_preparation` longtext NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `dosage` longtext DEFAULT NULL,
  `precautions` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `tisane`
--

INSERT INTO `tisane` (`id`, `nom`, `mode_preparation`, `image`, `dosage`, `precautions`) VALUES
(62, 'Tisane de Camomille', 'Infuser 2–3 g de camomille sèche dans 250 ml d’eau à 90–95°C pendant 7–10 min. Filtrer.', 'Tisane_de_camomille.png', '1–3 tasses/jour. Cure 2–3 semaines puis pause.', 'Allergie possible (Astéracées).'),
(63, 'Tisane de Verveine', 'Infuser 2 g de verveine dans 250 ml d’eau à ~90°C pendant 7–10 min.', 'Tisane_de_verveine.png', '1–3 tasses/jour, de préférence le soir.', NULL),
(64, 'Tisane de Tilleul', 'Infuser 2–3 g de bractées de tilleul 8–10 min à 90–95°C.', 'Tisane_de_tilleul.png', '1–3 tasses/jour, le soir pour favoriser l’endormissement.', NULL),
(65, 'Tisane de Menthe poivrée', 'Infuser 1–2 g de feuilles 7–9 min à ~90°C.', 'Tisane_de_menthe_poivree.png', 'Après les repas ou selon besoin.', 'Peut majorer le reflux gastro-œsophagien.'),
(66, 'Tisane de Fenouil', 'Infuser 1–2 g de graines 7–10 min à ~95°C. Écraser légèrement les graines.', 'Tisane_de_fenouil.png', 'Après repas, 1–2 tasses/jour.', NULL),
(67, 'Tisane d’Hibiscus', 'Infuser 2–3 g de calices 8–10 min à ~95°C (chaud ou froid).', 'Tisane_de_hibiscus.png', '1–3 tasses/jour.', 'Éviter pendant la grossesse. Prudence si tension basse/traitements antihypertenseurs.'),
(68, 'Tisane de Gingembre', 'Infuser 2–3 fines tranches de rhizome 7–10 min à ~95°C.', 'Tisane_de_Gingembre.png', '1–2 tasses/jour.', 'Prudence avec les anticoagulants ou troubles hémorragiques.'),
(69, 'Tisane de Citronnelle', 'Infuser 2–3 g de feuilles 8–10 min à ~95°C.', 'Tisane_de_citronnelle.png', '1–3 tasses/jour.', NULL),
(70, 'Sommeil doux (Tilleul + Verveine + Camomille)', '1 c. à c. de chaque plante (≈ 3 c. à c. au total) pour 250 ml, 8–10 min à ~90°C.', 'Sommeil_doux.png', 'Le soir, 1 tasse 30–45 min avant le coucher.', 'Allergie Astéracées (camomille).'),
(71, 'Digestion légère (Fenouil + Verveine + Menthe poivrée)', '≈ 2 c. à c. au total pour 250 ml, 7–10 min. Réduire la menthe si RGO.', 'Digestion_légère.png', 'Après repas.', 'Menthe poivrée : peut majorer le RGO.'),
(72, 'Confort hivernal (Tilleul + Citronnelle + Gingembre)', '2 c. à c. d’herbes + 2–3 tranches de gingembre pour 250 ml, 8–10 min.', 'Confort_hivernal.png', '1–2 tasses/jour selon besoin.', 'Gingembre : prudence si anticoagulants.'),
(73, 'Hydratation fruitée (Hibiscus + Citronnelle)', '≈ 2 c. à c. pour 250 ml, 8–10 min. Délicieux servi frais.', 'Hydratation_fruitée.png', '1–3 tasses/jour.', 'Hibiscus : éviter grossesse ; prudence si hypotension/traitement antihypertenseur.'),
(74, 'Après-repas apaisant (Fenouil + Camomille)', '≈ 2 c. à c. au total pour 250 ml, 7–10 min.', 'Après_repas_apaisant.png', 'Après un repas copieux.', 'Camomille : allergie Astéracées possible.'),
(75, 'Respiration douce (Thym + Sauge)', '≈ 2 c. à c. au total pour 250 ml, 7–9 min.', 'Respiration_douce.png', '1–2 tasses/jour en période sensible.', 'Sauge : prudence en cas de pathologies hormonodépendantes (consommation modérée).'),
(76, 'Énergie douce (Romarin + Gingembre)', '≈ 2 c. à c. au total pour 250 ml, 7–9 min.', 'Énergie_douce.png', 'Le matin ou début d’après-midi.', 'Gingembre : prudence si anticoagulants.'),
(77, 'Détox foie (Romarin + Hibiscus)', '≈ 2 c. à c. au total pour 250 ml, 8–10 min.', 'Détox_foie.png', '1–2 tasses/jour en cure courte.', 'Hibiscus : prudence si hypotension.'),
(78, 'Sérénité (Lavande + Verveine)', '≈ 2 c. à c. au total pour 250 ml, 7–10 min.', 'Sérénité.png', 'En fin de journée.', NULL),
(79, 'Sommeil profond (Tilleul + Lavande + Camomille)', '1 c. à c. de chaque plante pour 250 ml, 8–10 min.', 'Sommeil_profond.png', 'Le soir 30–45 min avant le coucher.', 'Camomille : allergie Astéracées possible.'),
(80, 'Fraîcheur digestive (Menthe poivrée + Citronnelle)', '≈ 2 c. à c. au total pour 250 ml, 7–9 min.', 'Fraîcheur_digestive.png', 'Après repas ou selon besoin.', 'Menthe : prudence si RGO.'),
(81, 'Cocon du soir (Camomille + Verveine)', '≈ 2 c. à c. au total pour 250 ml, 7–10 min.', 'Cocon_du_soir.png', '1 tasse en soirée.', 'Camomille : allergie Astéracées possible.');

-- --------------------------------------------------------

--
-- Structure de la table `tisane_bienfait`
--

CREATE TABLE `tisane_bienfait` (
  `tisane_id` int(11) NOT NULL,
  `bienfait_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `tisane_bienfait`
--

INSERT INTO `tisane_bienfait` (`tisane_id`, `bienfait_id`) VALUES
(62, 44),
(62, 45),
(62, 46),
(63, 44),
(63, 45),
(64, 44),
(64, 45),
(65, 46),
(66, 46),
(67, 51),
(67, 52),
(68, 46),
(69, 44),
(69, 46),
(70, 44),
(70, 45),
(71, 44),
(71, 46),
(72, 44),
(72, 46),
(73, 51),
(73, 52),
(74, 44),
(74, 46),
(75, 46),
(75, 52),
(76, 46),
(76, 51),
(77, 51),
(77, 52),
(78, 44),
(78, 45),
(79, 44),
(79, 45),
(80, 46),
(81, 44),
(81, 45);

-- --------------------------------------------------------

--
-- Structure de la table `tisane_plante`
--

CREATE TABLE `tisane_plante` (
  `tisane_id` int(11) NOT NULL,
  `plante_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `tisane_plante`
--

INSERT INTO `tisane_plante` (`tisane_id`, `plante_id`) VALUES
(62, 91),
(63, 93),
(64, 94),
(65, 92),
(66, 95),
(67, 96),
(68, 97),
(69, 98),
(70, 91),
(70, 93),
(70, 94),
(71, 92),
(71, 93),
(71, 95),
(72, 94),
(72, 97),
(72, 98),
(73, 96),
(73, 98),
(74, 91),
(74, 95),
(75, 99),
(75, 101),
(76, 97),
(76, 102),
(77, 96),
(77, 102),
(78, 93),
(78, 100),
(79, 91),
(79, 94),
(79, 100),
(80, 92),
(80, 98),
(81, 91),
(81, 93);

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id` int(11) NOT NULL,
  `email` varchar(180) NOT NULL,
  `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '(DC2Type:json)' CHECK (json_valid(`roles`)),
  `password` varchar(255) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `prenom` varchar(255) NOT NULL,
  `preferences` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id`, `email`, `roles`, `password`, `nom`, `prenom`, `preferences`) VALUES
(21, 'admin@example.com', '[\"ROLE_ADMIN\"]', '$2y$13$J76OuxiX2SnwxzfC0tslfO/E2/zKz3/X3F0ua2xqhyNLbn8SdlySS', 'Admin', 'Principal', NULL),
(22, 'user1@example.com', '[\"ROLE_USER\"]', '$2y$13$cKieO3AIcRoV75MtRS2OzesHIZjXhNqk77bzVL.GD9SBk7H6E/lkO', 'Dupont', 'Alice', NULL),
(23, 'user2@example.com', '[\"ROLE_USER\"]', '$2y$13$sqTBCarG8Mru2UoZY7hJ9.oPtdXQHmOXV2BVIWsE7VLbymJFduEJm', 'Martin', 'Bob', 'allergique au gluten'),
(24, 'ahlem@exemple.com', '[\"ROLE_USER\"]', '$2y$13$ZCsYR.FbEIJZTrnU3RYVrupEYFYtvjzkJWZECALT/s4Q109ce853u', 'BEN HAMOUDA', 'AHLEM', 'j\'aime le bio et le saisonier'),
(25, 'nadabh91@gmail.com', '[\"ROLE_USER\"]', '$2y$13$opZQPEiKbVIKL7wlxqt2K.H62Z8Wd.fgKUXalFbs9sPPTII.8PX7y', 'bhn', 'nada', NULL),
(26, 'wissem@example.com', '[\"ROLE_USER\"]', '$2y$13$KdKS8VwPj28STVvbl/BKtOfL0ykqb8.LADGh52LpOwHtM9edwWYPi', 'BEN HAMOUDA', 'WISSEM', NULL),
(27, 'bhahlem3@yahoo.fr', '[\"ROLE_USER\"]', '$2y$13$kz/DGWoYc01uvUZSON9s2uK6CiKj2AD9XcxtgBcMqeehIDMCplREC', 'BEN HAMOUDA', 'AHLEM', NULL);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `accord_aromatique`
--
ALTER TABLE `accord_aromatique`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_pair_ing` (`plante_id`,`ingredient_id`),
  ADD UNIQUE KEY `u_pair_type` (`plante_id`,`ingredient_type`),
  ADD KEY `IDX_E38D4CF177B16E8` (`plante_id`),
  ADD KEY `IDX_E38D4CF933FE08C` (`ingredient_id`);

--
-- Index pour la table `article`
--
ALTER TABLE `article`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_23A0E66FB88E14F` (`utilisateur_id`);

--
-- Index pour la table `bienfait`
--
ALTER TABLE `bienfait`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `commentaire`
--
ALTER TABLE `commentaire`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_67F068BC89312FE9` (`recette_id`),
  ADD KEY `IDX_67F068BCFB88E14F` (`utilisateur_id`),
  ADD KEY `IDX_67F068BC7294869C` (`article_id`),
  ADD KEY `IDX_67F068BCAE190A20` (`signale_par_id`);

--
-- Index pour la table `doctrine_migration_versions`
--
ALTER TABLE `doctrine_migration_versions`
  ADD PRIMARY KEY (`version`);

--
-- Index pour la table `gene`
--
ALTER TABLE `gene`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_F0FCA936C6E55B5` (`nom`);

--
-- Index pour la table `gene_bienfait`
--
ALTER TABLE `gene_bienfait`
  ADD PRIMARY KEY (`gene_id`,`bienfait_id`),
  ADD KEY `IDX_1256E80738BEE1C3` (`gene_id`),
  ADD KEY `IDX_1256E8075FE95C38` (`bienfait_id`);

--
-- Index pour la table `ingredient`
--
ALTER TABLE `ingredient`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `ingredient_gene`
--
ALTER TABLE `ingredient_gene`
  ADD PRIMARY KEY (`ingredient_id`,`gene_id`),
  ADD KEY `IDX_515E7A05933FE08C` (`ingredient_id`),
  ADD KEY `IDX_515E7A0538BEE1C3` (`gene_id`);

--
-- Index pour la table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_75EA56E0FB7336F0` (`queue_name`),
  ADD KEY `IDX_75EA56E0E3BD61CE` (`available_at`),
  ADD KEY `IDX_75EA56E016BA31DB` (`delivered_at`);

--
-- Index pour la table `plante`
--
ALTER TABLE `plante`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `plante_bienfait`
--
ALTER TABLE `plante_bienfait`
  ADD PRIMARY KEY (`plante_id`,`bienfait_id`),
  ADD KEY `IDX_F7AB8E56177B16E8` (`plante_id`),
  ADD KEY `IDX_F7AB8E565FE95C38` (`bienfait_id`);

--
-- Index pour la table `recette`
--
ALTER TABLE `recette`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_49BB6390FB88E14F` (`utilisateur_id`);

--
-- Index pour la table `recette_ingredient`
--
ALTER TABLE `recette_ingredient`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_recette_ingredient` (`id_recette`,`id_ingredient`),
  ADD KEY `IDX_17C041A99726CAE0` (`id_recette`),
  ADD KEY `IDX_17C041A9CE25F8A7` (`id_ingredient`);

--
-- Index pour la table `reset_password_request`
--
ALTER TABLE `reset_password_request`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_7CE748AA76ED395` (`user_id`);

--
-- Index pour la table `tisane`
--
ALTER TABLE `tisane`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `tisane_bienfait`
--
ALTER TABLE `tisane_bienfait`
  ADD PRIMARY KEY (`tisane_id`,`bienfait_id`),
  ADD KEY `IDX_13E295F72930F991` (`tisane_id`),
  ADD KEY `IDX_13E295F75FE95C38` (`bienfait_id`);

--
-- Index pour la table `tisane_plante`
--
ALTER TABLE `tisane_plante`
  ADD PRIMARY KEY (`tisane_id`,`plante_id`),
  ADD KEY `IDX_A0A8F8E62930F991` (`tisane_id`),
  ADD KEY `IDX_A0A8F8E6177B16E8` (`plante_id`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_IDENTIFIER_EMAIL` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `accord_aromatique`
--
ALTER TABLE `accord_aromatique`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT pour la table `article`
--
ALTER TABLE `article`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT pour la table `bienfait`
--
ALTER TABLE `bienfait`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT pour la table `commentaire`
--
ALTER TABLE `commentaire`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pour la table `gene`
--
ALTER TABLE `gene`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT pour la table `ingredient`
--
ALTER TABLE `ingredient`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=215;

--
-- AUTO_INCREMENT pour la table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `plante`
--
ALTER TABLE `plante`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=125;

--
-- AUTO_INCREMENT pour la table `recette`
--
ALTER TABLE `recette`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT pour la table `recette_ingredient`
--
ALTER TABLE `recette_ingredient`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT pour la table `reset_password_request`
--
ALTER TABLE `reset_password_request`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `tisane`
--
ALTER TABLE `tisane`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `accord_aromatique`
--
ALTER TABLE `accord_aromatique`
  ADD CONSTRAINT `FK_E38D4CF177B16E8` FOREIGN KEY (`plante_id`) REFERENCES `plante` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_E38D4CF933FE08C` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredient` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `article`
--
ALTER TABLE `article`
  ADD CONSTRAINT `FK_23A0E66FB88E14F` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`);

--
-- Contraintes pour la table `commentaire`
--
ALTER TABLE `commentaire`
  ADD CONSTRAINT `FK_67F068BC7294869C` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_67F068BC89312FE9` FOREIGN KEY (`recette_id`) REFERENCES `recette` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_67F068BCAE190A20` FOREIGN KEY (`signale_par_id`) REFERENCES `utilisateur` (`id`),
  ADD CONSTRAINT `FK_67F068BCFB88E14F` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`);

--
-- Contraintes pour la table `gene_bienfait`
--
ALTER TABLE `gene_bienfait`
  ADD CONSTRAINT `FK_1256E80738BEE1C3` FOREIGN KEY (`gene_id`) REFERENCES `gene` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_1256E8075FE95C38` FOREIGN KEY (`bienfait_id`) REFERENCES `bienfait` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `ingredient_gene`
--
ALTER TABLE `ingredient_gene`
  ADD CONSTRAINT `FK_515E7A0538BEE1C3` FOREIGN KEY (`gene_id`) REFERENCES `gene` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_515E7A05933FE08C` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredient` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `plante_bienfait`
--
ALTER TABLE `plante_bienfait`
  ADD CONSTRAINT `FK_F7AB8E56177B16E8` FOREIGN KEY (`plante_id`) REFERENCES `plante` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_F7AB8E565FE95C38` FOREIGN KEY (`bienfait_id`) REFERENCES `bienfait` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `recette`
--
ALTER TABLE `recette`
  ADD CONSTRAINT `FK_49BB6390FB88E14F` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`);

--
-- Contraintes pour la table `recette_ingredient`
--
ALTER TABLE `recette_ingredient`
  ADD CONSTRAINT `FK_17C041A99726CAE0` FOREIGN KEY (`id_recette`) REFERENCES `recette` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_17C041A9CE25F8A7` FOREIGN KEY (`id_ingredient`) REFERENCES `ingredient` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `reset_password_request`
--
ALTER TABLE `reset_password_request`
  ADD CONSTRAINT `FK_7CE748AA76ED395` FOREIGN KEY (`user_id`) REFERENCES `utilisateur` (`id`);

--
-- Contraintes pour la table `tisane_bienfait`
--
ALTER TABLE `tisane_bienfait`
  ADD CONSTRAINT `FK_13E295F72930F991` FOREIGN KEY (`tisane_id`) REFERENCES `tisane` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_13E295F75FE95C38` FOREIGN KEY (`bienfait_id`) REFERENCES `bienfait` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `tisane_plante`
--
ALTER TABLE `tisane_plante`
  ADD CONSTRAINT `FK_A0A8F8E6177B16E8` FOREIGN KEY (`plante_id`) REFERENCES `plante` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_A0A8F8E62930F991` FOREIGN KEY (`tisane_id`) REFERENCES `tisane` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
