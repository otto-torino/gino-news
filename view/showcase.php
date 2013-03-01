<?php
/**
* @file view/showcase.php
* @ingroup gino-news
* @brief Template per la vista vetrina news
*
* Variabili disponibili:
* - **section_id**: attributo id del tag section
* - **title**: titolo della vista
* - **feed**: link ai feed rss
* - **wrapper_id**: attributo id del wrapper che contiene gli elementi della vetrina
* - **ctrl_begin**: parte iniziale dell'attributo id dei controller dello slide (l'attributo viene completato con l'indice)
* - **items**: elementi della vetrina   
* - **ctrls**: controlli della vetrina (per lo slide)
* - **options**: opzioni da passare alla classe javascript che gestisce lo slide
*
* @version 2.0.1
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
	<div id="<?= $wrapper_id ?>">
	<? foreach($items as $item): ?>
		<?= $item ?>
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
			newslider = new NewSlider('<?= $wrapper_id?>', '<?= $ctrl_begin ?>', <?= $options ?>);
		});
	</script>
<? else: ?>
<p><?= _('Non risultano news registrate') ?></p>
<? endif ?>
</section>
