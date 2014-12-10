<?php
/**
* @file newsletter.php
* @brief Template per la visualizzazione delle news all'interno di newsletter
*
* Variabili disponibili:
* - **news**: \Gino\App\News\Article, istanza di @ref Gino.App.News.Article
*
* @version 2.1.0
* @copyright 2012-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<? namespace Gino\App\News; ?>
<? //@cond no-doxygen ?>
<section>
    <h1><?= \Gino\htmlChars($news->ml('title')) ?></h1>
    <?= \Gino\htmlChars($news->ml('text')) ?>
 </section>
<? // @endcond ?>
