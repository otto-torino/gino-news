<?php
/**
* @file view/archive.php
* @ingroup gino-news
* @brief Template per la vista archivio news
*
* Variabili disponibili:
* - **section_id**: attributo id del tag section
* - **title**: titolo della vista
* - **feed**: link ai feed rss
* - **items**: template per ciascuna delle news (decisi da opzioni)   
* - **pagination_navigation**: navigazione della paginazione
* - **pagination_summary**: riassunto paginazione (es. 1-5 di 10)
*
* @version 1.12
* @copyright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<section id="<?= $section_id ?>">
<header>
	<h1 class="left"><?= $title ?></h1>
	<div class="right feed">
		<?= $feed ?>
	</div>
	<div class="null"></div>
</header>
<? if(count($items)): ?>
	<? foreach($items as $item): ?>
	<?= $item ?>
	<? endforeach ?>
	<div class="pagination">
	<div class="left">
		<?= $pagination_navigation ?>
	</div>
	<div class="right">
		<?= $pagination_summary ?>
	</div>
	<div class="null"></div>
	</div>
<? else: ?>
	<p><?= _('Non risultano elementi registrati') ?></p>
<? endif ?>
</section>
