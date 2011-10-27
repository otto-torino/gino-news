--
-- Table structure for table `news`
--

CREATE TABLE IF NOT EXISTS `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `instance` int(11) NOT NULL,
  `ctg` int(11) NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  `text` text,
  `img` varchar(100) NOT NULL,
  `filename` varchar(100) NOT NULL,
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `private` enum('yes','no') NOT NULL DEFAULT 'no',
  `social` enum('yes','no') NOT NULL DEFAULT 'no',
  `published` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `news_ctg`
--

CREATE TABLE IF NOT EXISTS `news_ctg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `instance` int(11) NOT NULL DEFAULT '0',
  `name` varchar(200) NOT NULL,
  `parent` int(11) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `news_grp`
--

CREATE TABLE IF NOT EXISTS `news_grp` (
  `id` int(2) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `no_admin` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `news_grp`
--

INSERT INTO `news_grp` (`id`, `name`, `description`, `no_admin`) VALUES
(1, 'responsabili', 'Gestiscono l''assegnazione degli utenti ai singoli gruppi.', 'no'),
(2, 'pubblicatori', 'Gestiscono la pubblicazione delle news, la loro redazione e la loro eliminazione.', 'no'),
(3, 'redattori', 'Gestisce la redazione delle news: inserimento e modifica', 'no'),
(4, 'iscritti', 'Visualizzano news private', 'yes');

-- --------------------------------------------------------

--
-- Table structure for table `news_opt`
--

CREATE TABLE IF NOT EXISTS `news_opt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `instance` int(200) NOT NULL,
  `title_last` varchar(200) NOT NULL,
  `title_page` varchar(200) NOT NULL,
  `view_ctg` tinyint(1) NOT NULL,
  `home_news` int(3) NOT NULL,
  `page_news` int(3) NOT NULL,
  `summary_char` int(5) NOT NULL,
  `layer` int(2) NOT NULL,
  `layer_width` int(4) NOT NULL,
  `layer_height` int(4) NOT NULL,
  `img_lightbox` tinyint(1) NOT NULL,
  `img_expand` tinyint(1) NOT NULL,
  `news_search` tinyint(1) NOT NULL,
  `width_img` int(5) NOT NULL,
  `width_thumb` int(5) NOT NULL,
  `feed_rss` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `news_usr`
--

CREATE TABLE IF NOT EXISTS `news_usr` (
  `instance` int(11) NOT NULL,
  `group_id` int(2) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
