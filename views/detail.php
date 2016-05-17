<?php
/**
* @file detail.php
* @ingroup gino-news
* @brief Template per la vista dettaglio news
*
* Variabili disponibili:
* - **instance_name**: string, nome istanza modulo
* - **news**: \Gino\App\News\Article istanza di @ref Gino.App.News.Article
* - **related_contents_list**: html, lista di link a risorse correlate
* - **social**: html, bottoni per lo share sui social
*
* @version 2.1.1
* @copyright 2012-2016 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<? namespace Gino\App\News; ?>
<? //@cond no-doxygen ?>
<section itemscope itemtype="http://www.schema.org/NewsArticle" id="news-detail-<?= $instance_name ?>">
    <div class="row">
        <div class="col-md-12">
            <h1 itemprop="name"><?= \Gino\htmlChars($news->ml('title')) ?></h1>
            <p><time itemprop="datePublished" content="<?= $news->dateIso() ?>" pubdate="pubdate" datetime="<?= $news->dateIso() ?>"><?= \Gino\dbDateToDate($news->date) ?></time></p>
            <? if($news->objCategories()): ?>
                <p><span class="fa fa-cubes"></span> 
                <? $router = \Gino\Router::instance(); ?>
                <? foreach($news->objCategories() as $ctg): ?>
                <a href="<?= $router->link($news->getController()->getInstanceName(), 'archive', array('ctg' => $ctg->slug)) ?>"><?= implode(', ', $news->objCategories()) ?></a> 
                <? endforeach ?>
                </p>
            <? endif ?>
            <? if($news->tags): ?>
                <p><span class="fa fa-tag"></span> <?= $news->tags ?></p>
            <? endif ?>
        </div>
    </div>
    <div class="row">
        <? if($news->img): ?>
            <div class="col-sm-4 col-xs-12">
                <img class="img-responsive" src="<?= $news->getImgPath() ?>" alt="<?= _('immagine') ?>" />
            </div>
            <div class="col-sm-8 col-xs-12">
        <? else: ?>
            <div class="col-sm-12">
        <? endif ?>
        <div itemprop="articleBody">
            <?= \Gino\htmlChars($news->ml('text')) ?>
        </div>
        <? if($news->social): ?>
            <?= $social ?>
        <? endif ?>
        <? if($related_contents_list): ?>
            <h2><?= _('Potrebbe interessarti anche...') ?></h2>
            <?= $related_contents_list ?>
        <? endif ?>
        </div>
    </div>
</section>
<? // @endcond ?>
