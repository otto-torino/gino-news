<?php
/**
* @file showcase.php
* @brief Template per la vista vetrina news
*
* Variabili disponibili:
* - **instance_name**: string, nome istanza modulo
* - **news**: array, oggetti di tipo @ref Gino.App.News.Article
* - **feed_url**: string, url ai feed RSS
* - **autostart**: bool, opzione autostart
* - **autointerval**: int, intervallo animazione (ms)
*
* @version 2.1.0
* @copyright 2012-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<? namespace Gino\App\News; ?>
<? //@cond no-doxygen ?>
<section id="news-showcase-news-<?= $instance_name ?>">
    <h1>
        <?= _('News') ?>
        <? if($feed_url): ?>
            <a href="<?= $feed_url ?>" class="fa fa-rss"></a>
        <? endif ?>
    </h1>
    <div id="news-showcase-wrapper-news-<?= $instance_name ?>">
        <? $ctrls = array(); ?>
        <? $tot = count($news); ?>
        <? $i = 0; ?>
        <? foreach($news as $n): ?>
            <div class='news-showcase-item' style='display: block;z-index:<?= $tot - $i ?>' id="news_<?= $i ?>">
                <article>
                    <h1><a href="<?= $n->getUrl() ?>"><?= \Gino\htmlChars($n->ml('title')) ?></a></h1>
                    <?= \Gino\htmlChars(\Gino\cutHtmlText($n->ml('text'), 150, '...', false, false, true, array('endingPosition'=>'in'))) ?>
                </article>
            </div>
            <? $ctrls[] = "<div id=\"sym_$i\" class=\"news-showcase-sym\" onclick=\"newslider.set($i)\"><span></span></div>"; ?>
            <? $i++; ?>
        <? endforeach ?>
    </div>
    <table>
        <tr>
        <? foreach($ctrls as $ctrl): ?>
            <td><?= $ctrl ?></td>
        <? endforeach ?>
        </tr>
    </table>
    <script type="text/javascript">
        var newslider;
        window.addEvent('load', function() {
            newslider = new NewSlider('news-showcase-wrapper-news-<?= $instance_name ?>', 'sym_', {auto_start: <?= $autostart ? 'true' : 'false' ?>, auto_interval: <?= $autointerval ?>});
        });
    </script>
</section>
<? // @endcond ?>
