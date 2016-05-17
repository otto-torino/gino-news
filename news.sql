-- phpMyAdmin SQL Dump
-- Application: news
--

--
-- Struttura della tabella `news_article`
--

CREATE TABLE IF NOT EXISTS `news_article` (
  `id` int(11) NOT NULL,
  `instance` int(11) NOT NULL,
  `insertion_date` datetime NOT NULL,
  `last_edit_date` datetime NOT NULL,
  `date` date NOT NULL,
  `title` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `text` text,
  `tags` varchar(255) DEFAULT NULL,
  `img` varchar(100) DEFAULT NULL,
  `attachment` varchar(100) DEFAULT NULL,
  `private` tinyint(1) NOT NULL DEFAULT '0',
  `social` tinyint(1) NOT NULL DEFAULT '0',
  `slideshow` TINYINT(1) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `news_article_category`
--

CREATE TABLE IF NOT EXISTS `news_article_category` (
  `id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `news_category`
--

CREATE TABLE IF NOT EXISTS `news_category` (
  `id` int(11) NOT NULL,
  `instance` int(11) NOT NULL DEFAULT '0',
  `name` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `description` text,
  `image` varchar(200) DEFAULT NULL
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `news_opt`
--

CREATE TABLE IF NOT EXISTS `news_opt` (
  `id` int(11) NOT NULL,
  `instance` int(11) NOT NULL,
  `last_news_number` int(11) NOT NULL,
  `last_slideshow_view` TINYINT(1) NOT NULL DEFAULT '0',
  `last_slideshow_number` TINYINT(2) DEFAULT NULL,
  `list_nfp` smallint(2) DEFAULT NULL,
  `showcase_news_number` smallint(2) DEFAULT NULL,
  `showcase_auto_start` tinyint(1) NOT NULL,
  `showcase_auto_interval` int(8) NOT NULL,
  `image_width` smallint(4) DEFAULT NULL,
  `newsletter_news_number` smallint(3) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `news_article`
--
ALTER TABLE `news_article`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `news_article_category`
--
ALTER TABLE `news_article_category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `news_category`
--
ALTER TABLE `news_category`
  ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `news_opt`
--
ALTER TABLE `news_opt`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `news_article`
--
ALTER TABLE `news_article`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `news_article_category`
--
ALTER TABLE `news_article_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `news_category`
--
ALTER TABLE `news_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `news_opt`
--
ALTER TABLE `news_opt`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

INSERT INTO `auth_permission` (`class`, `code`, `label`, `description`, `admin`) VALUES
('news', 'can_admin', 'amministrazione', 'amministrazione completa del modulo', 1),
('news', 'can_publish', 'pubblicazione', 'pubblica ed elimina le news', 1),
('news', 'can_write', 'redazione', 'inserisce e modifica le news ma non le può pubblicare o eliminare', 1),
('news', 'can_view_private', 'visualizzazione news private', 'visualizzazione delle news impostate come private', 0);
