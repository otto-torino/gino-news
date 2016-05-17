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
* - **slideshow**: bool
* - **slideshow_items**: array, oggetti @ref Gino.App.News.Article
*
* @version 2.1.1
* @copyright 2012-2016 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<? namespace Gino\App\News; ?>
<? //@cond no-doxygen ?>
<section id="news-last-<?= $instance_name ?>">
    <h1>Ultime news <a class="fa fa-rss" href="<?= $feed_url ?>"></a></h1>
    
    <? if($slideshow && count($slideshow_items) > 1): ?>
    	<div id="news-feed-<?= $instance_name ?>">
			<ul>
			<? foreach($slideshow_items as $slide): ?>
				<li>
				<? if($slide->img): ?>
                    <? $image = new \Gino\GImage(\Gino\absolutePath($slide->getImgPath())); $thumb = $image->thumb(100, 100); ?>
                    <img class="left" style="margin: 0 10px 10px 0" src="<?= $thumb->getPath() ?>" />
                <? endif ?>
                <div class="title"><a href="<?= $slide->getUrl() ?>"><?= \Gino\htmlChars($slide->ml('title')) ?></a></div>
                <time><?= \Gino\dbDateToDate($slide->date) ?></time>
                
                <?= \Gino\cutHtmlText(\Gino\htmlChars($slide->ml('text')), 80, '...', false, false, true, array('endingPosition' => 'in')) ?>
                <div class="null"></div>
                </li>
			<? endforeach ?>
			</ul>
		</div>
		
		<script type="text/javascript">
        	window.addEvent('domready', function() {
            	NewsShow('news-feed-<?= $instance_name ?>', 4000, 500);
        	});
   		</script>
    <? endif ?>
    
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
