<?php
/**
* @file last.php
* @brief Template per la vista ultime news
*
* Variabili disponibili:
* - **instance_name**: string, nome istanza modulo
* - **news**: array, oggetti @ref Gino.App.News.Article
* - **feed_url**: string, url feed rss
* - **archive_url**: string,  url archivio completo
*
* @version 2.1.0
* @copyright 2012-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<? namespace Gino\App\News; ?>
<? //@cond no-doxygen ?>
<section id="news-last-news-<?= $instance_name ?>">
    <h1>Ultime news <a class="fa fa-rss" href="<?= $feed_url ?>"></a></h1>
    <? if(count($news)): ?>
        <? foreach($news as $n): ?>
            <article>
                <h1><a href="<?= $n->getUrl() ?>"><?= \Gino\htmlChars($n->ml('title')) ?></a></h1>
                <time><?= \Gino\dbDateToDate($n->date) ?></time>
                <? if($n->img): ?>
                    <? $image = new \Gino\GImage(\Gino\absolutePath($n->getImgPath())); $thumb = $image->thumb(100, 100); ?>
                    <img class="left" style="margin: 0 10px 10px 0" src="<?= $thumb->getPath() ?>" />
                <? endif ?>
                <?= \Gino\cutHtmlText(\Gino\htmlChars($n->ml('text')), 80, '...', false, false, true, array('endingPosition' => 'in')) ?>
                <div class="null"></div>
            </article>
        <? endforeach ?>
        <p class="archive"><a href="<?= $archive_url ?>"><?= _('archivio') ?></a></p>
    <? else: ?>
        <p><?= _('Non risultano news registrate') ?></p>
    <? endif ?>
</section>
<? // @endcond ?>
