<?php
/**
* @file archive.php
* @brief Template per la vista archivio news
*
* Variabili disponibili:
* - **instance_name**: string, nome istanza modulo
* - **ctg**: mixed, categoria @ref Gino.App.News.Category o null
* - **news**: array, oggetti di tipo @ref Gino.App.News.Article
* - **feed_url**: string, url ai feed RSS
* - **pagination**: html, paginazione
*
* @version 2.1.0
* @copyright 2012-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<? namespace Gino\App\News; ?>
<? //@cond no-doxygen ?>
<section id="news-archive-news-<?= $instance_name ?>">
    <h1>
        <?= _('Archivio news') ?> 
        <? if($ctg): ?>
        - <?= \Gino\htmlChars($ctg->ml('name')); ?>
        <? endif ?>
    </h1>
    <? if(count($news)): ?>
        <? foreach($news as $n): ?>
            <article>
                <h1><a href="<?= $n->getUrl() ?>"><?= \Gino\htmlChars($n->ml('title')) ?></a></h1>
                <time><?= \Gino\dbDateToDate($n->date) ?></time>
                <? if($n->img): ?>
                    <? $image = new \Gino\GImage(\Gino\absolutePath($n->getImgPath())); $thumb = $image->thumb(200, 200); ?>
                    <img class="left" style="margin: 0 10px 10px 0" src="<?= $thumb->getPath() ?>" />
                    <?= \Gino\cutHtmlText(\Gino\htmlChars($n->ml('text')), 300, '...', false, false, true, array('endingPosition' => 'in')) ?>
                    <div class="null"></div>
                <? else: ?>
                    <?= \Gino\cutHtmlText(\Gino\htmlChars($n->ml('text')), 300, '...', false, false, true, array('endingPosition' => 'in')) ?>
                <? endif ?>
            </article>
        <? endforeach ?>
        <?= $pagination ?>
    <? else: ?>
        <p><?= _('Non risultano elementi registrati') ?></p>
    <? endif ?>
</section>
<? // @endcond ?>
