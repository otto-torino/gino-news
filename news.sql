CREATE TABLE IF NOT EXISTS `news_ctg` (
  `id` int(11) NOT NULL auto_increment,
  `instance` int(11) NOT NULL default '0',
  `name` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `description` text,
  `image` varchar(200) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `news_ctg`
--

--
-- Table structure for table `news_grp`
--

CREATE TABLE IF NOT EXISTS `news_grp` (
  `id` int(2) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `no_admin` enum('yes','no') NOT NULL default 'no',
  PRIMARY KEY  (`id`)
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
-- Table structure for table `news_item`
--

CREATE TABLE IF NOT EXISTS `news_item` (
  `id` int(11) NOT NULL auto_increment,
  `instance` int(11) NOT NULL,
  `insertion_date` datetime NOT NULL,
  `last_edit_date` datetime NOT NULL,
  `date` date NOT NULL,
  `categories` varchar(200) default NULL,
  `title` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `text` text,
  `img` varchar(100) default NULL,
  `attached` varchar(100) default NULL,
  `private` int(1) NOT NULL default '0',
  `social` int(1) NOT NULL,
  `published` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `news_item`
--

--
-- Table structure for table `news_opt`
--

CREATE TABLE IF NOT EXISTS `news_opt` (
  `id` int(11) NOT NULL auto_increment,
  `instance` int(11) NOT NULL,
  `title_last` varchar(200) default NULL,
  `title_list` varchar(200) default NULL,
  `title_showcase` varchar(200) default NULL,
  `last_news_code` text,
  `last_news_number` int(2) default NULL,
  `list_news_code` text,
  `list_nfp` int(2) default NULL,
  `showcase_news_code` text,
  `showcase_news_number` int(2) default NULL,
  `showcase_auto_start` int(1) NOT NULL,
  `showcase_auto_interval` int(8) NOT NULL,
  `detail_news_code` text,
  `image_width` int(4) default NULL,
  `thumb_width` int(3) default NULL,
  `newsletter_news_code` text NOT NULL,
  `newsletter_news_number` int(3) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `news_opt`
--

-- --------------------------------------------------------

--
-- Table structure for table `news_usr`
--

CREATE TABLE IF NOT EXISTS `news_usr` (
  `instance` int(11) NOT NULL,
  `group_id` int(2) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `news_usr`
--
