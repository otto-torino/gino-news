<?php
/**
 * \file class_news.php
 * @brief Contiene la definizione ed implementazione della classe \ref news.
 * 
 * @version 2.0.2
 * @copyright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */

/** \mainpage Caratteristiche, opzioni configurabili da backoffice ed output disponibili per i template e le voci di menu.    
 *        
 * CARATTERISTICHE    
 *  
 * Modulo di gestione news categorizzate 
 *
 * OPZIONI CONFIGURABILI
 * - titolo ultime news
 * - titolo elenco news
 * - titolo vetrina news
 * - template sinfgolo elemento nella vista ultime news
 * - numero ultime news
 * - template singolo elemento vista elenco news
 * - numero di news per pagina
 * - template singolo elemento vista vetrina news
 * - numero di news in vetrina
 * - inizio automatico animazione vetrina
 * - intervallo animazione vetrina
 * - template dettaglio news
 * - larghezza massima immagini
 * - larghezza massima thumbs
 *
 * OUTPUTS
 * - ultime news
 * - archivio news
 * - vetrina news
 * - dettaglio news
 * - feed RSS
 */
require_once('class.newsItem.php');
require_once('class.newsCtg.php');

/**
* @defgroup gino-news
* Modulo di gestione news categorizzate
*
* Il modulo contiene anche dei css, javascript e file di configurazione.
*
*/

/**
 * \ingroup gino-news
 * Classe per la gestione di news categorizzate.
 *
 * Gli output disponibili sono:
 *
 * - ultime n news (n da opzioni)
 * - elenco news paginate
 * - vetrina news
 * - vista singola news
 * - feed RSS
 * 
 * @version 2.0.2
 * @copyright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class news extends AbstractEvtClass {

	/**
	 * @brief titolo della view ultime news  
	 */
	private $_title_last;

	/**
	 * @brief titolo della view lista news  
	 */
	private $_title_list;

	/**
	 * @brief titolo della view vetrina news  
	 */
	private $_title_showcase;

	/**
	 * @brief Template elemento ultime news  
	 */
	private $_last_news_code;

	/**
	 * @brief numero di ultime news  
	 */
	private $_last_news_number;

	/**
	 * @brief Template elemento elenco news  
	 */
	private $_list_news_code;

	/**
	 * @brief numero di news per pagina nella vista elenco news 
	 */
	private $_list_nfp;

	/**
	 * @brief Template elemento showcase news  
	 */
	private $_showcase_news_code;

	/**
	 * @brief Template dettaglio singola news  
	 */
	private $_detail_news_code;

	/**
	 * @brief numero di news nella vetrina  
	 */
	private $_showcase_news_number;

	/**
	 * @brief animazione vetrina start automatico  
	 */
	private $_showcase_auto_start;

	/**
	 * @brief animazione vetrina intervallo animazione  
	 */
	private $_showcase_auto_interval;

	/**
	 * @brief larghezza immagini  
	 */
	private $_image_width;

	/**
	 * @brief larghezza thumb  
	 */
	private $_thumb_width;

	/**
	 * @brief Template dettaglio singola news in esportazione newsletter
	 */
	private $_newsletter_news_code;

    /**
	 * @brief Numero ultime news esportate in lista newsletter
	 */
	private $_newsletter_news_number;

	/**
	 * @brief Tabella di opzioni 
	 */
	private $_tbl_opt;

	/**
	 * @brief Tabella di associazione utenti/gruppi 
	 */
	private $_tbl_usr;

	/**
	 * Percorso assoluto alla directory contenente le viste 
	 */
	private $_view_dir;

	/*
	 * Parametro action letto da url 
	 */
	private $_action;

	/*
	 * Parametro block letto da url 
	 */
	private $_block;
	
	/**
	 * Costruisce un'istanza di tipo news
	 *
	 * @param int $mdlId id dell'istanza di tipo news
	 * @return istanza di news
	 */
	function __construct($mdlId) {

		parent::__construct();

		$this->_instance = $mdlId;
		$this->_instanceName = $this->_db->getFieldFromId($this->_tbl_module, 'name', 'id', $this->_instance);
		$this->_instanceLabel = $this->_db->getFieldFromId($this->_tbl_module, 'label', 'id', $this->_instance);

		$this->_data_dir = $this->_data_dir.$this->_os.$this->_instanceName;
		$this->_data_www = $this->_data_www."/".$this->_instanceName;

		$this->_tbl_opt = 'news_opt';
		$this->_tbl_usr = 'news_usr';

		$this->setAccess();
		$this->setGroups();

		$this->_view_dir = dirname(__FILE__).OS.'view';

		$last_news_code = "<article><p>{{ thumb|class:left }}</p><h1>{{ title|link }}</h1>{{ text|chars:80 }}<div class=\"null\"></div></article>";
		$list_news_code = "<article><header><h1>{{ title }}</h1><p>Pubblicata il <time>{{ date }}</time> in {{ categories }}</p></header>{{ img|class:left }}{{ text }}{{ social }}<div class=\"null\"></div></article>";
		$showcase_news_code = "<article><p>{{ img|class:left }}</p><h1>{{ title|link }}</h1>{{ text|chars:500 }}</article>";
		$detail_news_code = "<header><h1>{{ title }}</h1><p>Pubblicata il <time>{{ date }}</time> in {{ categories }}</p></header>{{ img|class:left }}{{ text }}<p>{{ attached }}</p>{{ social }}<div class=\"null\"></div>";

		$this->_optionsValue = array(
			'title_last'=>_("Ultime news"),
			'title_list'=>_("Elenco news"),
			'title_showcase'=>_("News"),
			'last_news_code'=>$last_news_code,
			'last_news_number'=>3,
			'list_news_code'=>$list_news_code,
			'list_nfp'=>5,
			'showcase_news_code'=>$showcase_news_code,
			'showcase_news_number'=>5,
			'showcase_auto_start'=>0,
			'showcase_auto_interval'=>5000,
			'detail_news_code'=>$detail_news_code,
			'image_width'=>600,
			'thumb_width'=>80,
			'newsletter_news_code'=>$last_news_code,
			'newsletter_news_number'=>50,
		);

		$code_exp = _("Le proprietà della news devono essere inserite all'interno di doppie parentesi {{ proprietà }}. Proprietà disponibili:<br/>");
		$code_exp .= "<ul>";
		$code_exp .= "<li><b>thumb</b>: "._('thumbnail')."</li>";
		$code_exp .= "<li><b>thumb_path</b>: "._('path della thumbnail')."</li>";
		$code_exp .= "<li><b>img</b>: "._('immagine')."</li>";
		$code_exp .= "<li><b>img_path</b>: "._('path dell\'immagine')."</li>";
		$code_exp .= "<li><b>title</b>: "._('titolo')."</li>";
		$code_exp .= "<li><b>text</b>: "._('testo')."</li>";
		$code_exp .= "<li><b>date</b>: "._('data')."</li>";
		$code_exp .= "<li><b>attached</b>: "._('link allegato')."</li>";
		$code_exp .= "<li><b>categories</b>: "._('categorie associate')."</li>";
		$code_exp .= "<li><b>categories_images</b>: "._('immagini categorie associate')."</li>";
		$code_exp .= "<li><b>url</b>: "._('url che porta al dettaglio della news')."</li>";
		$code_exp .= "<li><b>social</b>: "._('condivisione social')."</li>";
		$code_exp .= "<li><b>feed</b>: "._('link ai feed RSS')."</li>";
		$code_exp .= "</ul>";
		$code_exp .= _("Inoltre si possono eseguire dei filtri o aggiungere link facendo seguire il nome della proprietà dai caratteri '|filtro'. Disponibili:<br />");
		$code_exp .= "<ul>";
		$code_exp .= "<li><b><span style='text-style: normal'>|link</span></b>: "._('aggiunge il link che porta al dettaglio della news alla proprietà')."</li>";
		$code_exp .= "<li><b><span style='text-style: normal'>|layer:600x400</span></b>: "._('aggiunge il link che apre il dettaglio della news in un layer (w 600, h 400) alla proprietà. Per non fissare l\'altezza impostarla uguale a 0')."</li>";
		$code_exp .= "<li><b><span style='text-style: normal'>thumb|class:name_class</span></b>: "._('aggiunge la classe name_class alla thumb o all\'immagine')."</li>";
		$code_exp .= "<li><b><span style='text-style: normal'>thumb|style:\"styles\"</span></b>: "._('aggiunge gli stili (styles) alla thumb o all\'immagine')."</li>";
		$code_exp .= "<li><b><span style='text-style: normal'>|chars:n</span></b>: "._('mostra solo n caratteri della proprietà')."</li>";
		$code_exp .= "</ul>";

		$this->_title_last = htmlChars($this->setOption('title_last', array('value'=>$this->_optionsValue['title_last'], 'translation'=>true)));
		$this->_title_list = htmlChars($this->setOption('title_list', array('value'=>$this->_optionsValue['title_list'], 'translation'=>true)));
		$this->_title_showcase = htmlChars($this->setOption('title_showcase', array('value'=>$this->_optionsValue['title_showcase'], 'translation'=>true)));
		$this->_last_news_code = $this->setOption('last_news_code', array('value'=>$this->_optionsValue['last_news_code'], 'translation'=>true));
		$this->_last_news_number = $this->setOption('last_news_number', array('value'=>$this->_optionsValue['last_news_number']));
		$this->_list_news_code = $this->setOption('list_news_code', array('value'=>$this->_optionsValue['list_news_code'], 'translation'=>true));
		$this->_list_nfp = $this->setOption('list_nfp', array('value'=>$this->_optionsValue['list_nfp']));
		$this->_showcase_news_code = $this->setOption('showcase_news_code', array('value'=>$this->_optionsValue['showcase_news_code'], 'translation'=>true));
		$this->_showcase_news_number = $this->setOption('showcase_news_number', array('value'=>$this->_optionsValue['showcase_news_number']));
		$this->_showcase_auto_start = $this->setOption('showcase_auto_start', array('value'=>$this->_optionsValue['showcase_auto_start']));
		$this->_showcase_auto_interval = $this->setOption('showcase_auto_interval', array('value'=>$this->_optionsValue['showcase_auto_interval']));
		$this->_detail_news_code = $this->setOption('detail_news_code', array('value'=>$this->_optionsValue['detail_news_code'], 'translation'=>true));
		$this->_image_width = $this->setOption('image_width', array('value'=>$this->_optionsValue['image_width']));
		$this->_thumb_width = $this->setOption('thumb_width', array('value'=>$this->_optionsValue['thumb_width']));
		$this->_newsletter_news_code = $this->setOption('newsletter_news_code', array('value'=>$this->_optionsValue['newsletter_news_code'], 'translation'=>true));
		$this->_newsletter_news_number = $this->setOption('newsletter_news_number', array('value'=>$this->_optionsValue['newsletter_news_number']));

		$this->_options = new options($this->_className, $this->_instance);
		$this->_optionsLabels = array(
			"title_last"=>array(
				'label'=>_("Titolo ultime news"), 
				'value'=>$this->_optionsValue['title_last'], 
				'section'=>true, 
				'section_title'=>_('Titoli delle viste pubbliche')
			),
			"title_list"=>array(
				'label'=>_("Titolo elenco news"),
				'value'=>$this->_optionsValue['title_list']
			),
			"title_showcase"=>array(
				'label'=>_("Titolo vetrina news"),
				'value'=>$this->_optionsValue['title_showcase']
			),
			"last_news_code"=>array(
				'label'=>array(_("Template singolo elemento vista ultime news"), $code_exp), 
				'value'=>$this->_optionsValue['last_news_code'],
				'section'=>true, 
				'section_title'=>_('Opzioni vista ultime news'),
				'section_description'=>"<p>"._('Il template verrà utilizzato per ogni news ed inserito all\'interno di una section')."</p>"
			), 
			"last_news_number"=>array(
				'label'=>_("Numero ultime news"),
				'value'=>$this->_optionsValue['last_news_number']
			),
			"list_news_code"=>array(
				'label'=>array(_("Template singolo elemento vista elenco news"), _("Vedi 'Template singolo elemento vista ultime news' per le proprietà e filtri disponibili")), 
				'value'=>$this->_optionsValue['list_news_code'],
				'section'=>true, 
				'section_title'=>_('Opzioni vista elenco news'),
				'section_description'=>"<p>"._('Il template verrà utilizzato per ogni news ed inserito all\'interno di una section')."</p>"
			), 
			"list_nfp"=>array(
				'label'=>_("Numero news per pagina"),
				'value'=>$this->_optionsValue['list_nfp']
			),
			"showcase_news_code"=>array(
				'label'=>array(_("Template singolo elemento vista vetrina news"), _("Vedi 'Template singolo elemento vista ultime news' per le proprietà e filtri disponibili")), 
				'value'=>$this->_optionsValue['showcase_news_code'],
				'section'=>true, 
				'section_title'=>_('Opzioni vista vetrina news')
			), 
			"showcase_news_number"=>array(
				'label'=>_("Numero news"),
				'value'=>$this->_optionsValue['showcase_news_number']
			),
			"showcase_auto_start"=>array(
				'label'=>_("Animazione automatica"),
				'value'=>$this->_optionsValue['showcase_auto_start']
			),
			"showcase_auto_interval"=>array(
				'label'=>_("Intervallo animazione automatica (ms)"),
				'value'=>$this->_optionsValue['showcase_auto_start']
			),
			"detail_news_code"=>array(
				'label'=>array(_("Template dettaglio news"), _("Vedi 'Template singolo elemento vista ultime news' per le proprietà e filtri disponibili")), 
				'value'=>$this->_optionsValue['detail_news_code'],
				'section'=>true, 
				'section_title'=>_('Opzioni vista dettaglio news'),
				'section_description'=>"<p>"._('Il template verrà utilizzato per la news selezionata ed inserito all\'interno di una section')."</p>"
			), 
			"image_width"=>array(
				'label'=>_("Larghezza massima immagini"), 
				'value'=>$this->_optionsValue['image_width'],
				'section'=>true, 
				'section_title'=>_('Opzioni ridimensionamento immagini')
			), 
			"thumb_width"=>array(
				'label'=>_("Larghezza massima thumbnail"), 
				'value'=>$this->_optionsValue['thumb_width']
			), 
			"newsletter_news_code"=>array(
				'label'=>array(_("Template singolo elemento esportazione newsletter"), _("Vedi 'Template singolo elemento vista ultime news' per le proprietà e filtri disponibili")), 
				'value'=>$this->_optionsValue['newsletter_news_code'],
				'section'=>true, 
				'section_title'=>_('Opzioni esportazione per newsletter')
			), 
			"newsletter_news_number"=>array(
				'label'=>_("Numero news esportate nella lista"),
				'value'=>$this->_optionsValue['newsletter_news_number']
			),
		);

		$this->_action = cleanVar($_REQUEST, 'action', 'string', '');
		$this->_block = cleanVar($_REQUEST, 'block', 'string', '');

	}

	/**
	 * Restituisce alcune proprietà della classe utili per la generazione di nuove istanze
	 *
	 * @static
	 * @return lista delle proprietà utilizzate per la creazione di istanze di tipo news
	 */
	public static function getClassElements() {

		return array(
			"tables"=>array(
				'news_item', 
				'news_grp', 
				'news_ctg', 
				'news_opt', 
				'news_usr'
			),
			"css"=>array(
				'news.css'
			),
			"folderStructure"=>array (
				CONTENT_DIR.OS.'news'=> array(
					'img' => null,
					'attached' => null
				)
	     		)
		);

	}

	/**
	 * Metodo invocato quando viene eliminata un'istanza di tipo news
	 *
	 * Si esegue la cancellazione dei dati da db e l'eliminazione di file e directory 
	 * 
	 * @access public
	 * @return bool il risultato dell'operazione
	 */
	public function deleteInstance() {

		$this->accessGroup('');

		/*
		 * delete records and translations from table news_item
		 */
		$query = "SELECT id FROM ".newsItem::$tbl_item." WHERE instance='$this->_instance'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a)>0) 
			foreach($a as $b) 
				language::deleteTranslations(newsItem::$tbl_item, $b['id']);
		
		$query = "DELETE FROM ".newsItem::$tbl_item." WHERE instance='$this->_instance'";	
		$result = $this->_db->actionquery($query);
		
		/*
		 * delete record and translations from table news_ctg
		 */
		$query = "SELECT id FROM ".newsCtg::$tbl_ctg." WHERE instance='$this->_instance'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a)>0) {
			foreach($a as $b) {
				language::deleteTranslations(newsCtg::$tbl_ctg, $b['id']);
			}
		}
		$query = "DELETE FROM ".newsCtg::$tbl_ctg." WHERE instance='$this->_instance'";	
		$result = $this->_db->actionquery($query);

		/*
		 * delete record and translation from table news_opt
		 */
		$opt_id = $this->_db->getFieldFromId($this->_tbl_opt, "id", "instance", $this->_instance);
		language::deleteTranslations($this->_tbl_opt, $opt_id);
		
		$query = "DELETE FROM ".$this->_tbl_opt." WHERE instance='$this->_instance'";	
		$result = $this->_db->actionquery($query);
		
		/*
		 * delete group users association
		 */
		$query = "DELETE FROM ".$this->_tbl_usr." WHERE instance='$this->_instance'";	
		$result = $this->_db->actionquery($query);

		/*
		 * delete css files
		 */
		$classElements = $this->getClassElements();
		foreach($classElements['css'] as $css) {
			unlink(APP_DIR.OS.$this->_className.OS.baseFileName($css)."_".$this->_instanceName.".css");
		}

		/*
		 * delete folder structure
		 */
		foreach($classElements['folderStructure'] as $fld=>$fldStructure) {
			$this->deleteFileDir($fld.OS.$this->_instanceName, true);
		}

		return $result;
	}

	/**
	 * Setter per le proprietà group
	 *
	 * Definizione dei gruppi che gestiscono l'accesso alle funzionalità amministrative e non
	 *
	 * @return void
	 */
	private function setGroups(){
		
		// Pubblicazione
		$this->_group_1 = array($this->_list_group[0], $this->_list_group[1]);
		
		// Redazione
		$this->_group_2 = array($this->_list_group[0], $this->_list_group[1], $this->_list_group[2]);

		// Iscritti
		$this->_group_3 = array($this->_list_group[0], $this->_list_group[1], $this->_list_group[2], $this->_list_group[3]);

	}

	/**
	 * Definizione dei metodi pubblici che forniscono un output per il front-end 
	 * 
	 * Questo metodo viene letto dal motore di generazione dei layout e dal motore di generazione di voci di menu
	 * per presentare una lista di output associati all'istanza di classe. 
	 * 
	 * @static
	 * @access public
	 * @return array[string]array
	 */
	public static function outputFunctions() {

		$list = array(
			"last" => array("label"=>_("Lista utime news"), "role"=>'1'),
			"archive" => array("label"=>_("Lista news paginata"), "role"=>'1'),
			"showcase" => array("label"=>_("Vetrina"), "role"=>'1')
		);

		return $list;
	}

	/**
	 * Percorso assoluto alla cartella dei contenuti 
	 * 
	 * @param string $type tipologia di media (img, attached)
	 * @return percorso assoluto
	 */
	public function getBaseAbsPath($type) {

		return $this->_data_dir.OS.$type;

	}

	/**
	 * Percorso relativo alla cartella dei contenuti 
	 * 
	 * @param string $type tipologia di media (img, attached)
	 * @return percorso relativo
	 */
	public function getBasePath($type) {

		return $this->_data_www.'/'.$type;

	}

	/**
	 * Getter larghezza di ridimensionamenteo delle immagini 
	 * 
	 * @access public
	 * @return largheza di ridimensionamento
	 */
	public function getImageWidth() {

		return $this->_image_width;

	}

	/**
	 * Getter larghezza di ridimensionamenteo delle thumb 
	 * 
	 * @access public
	 * @return largheza di ridimensionamento
	 */
	public function getThumbWidth() {

		return $this->_thumb_width;

	}

	/**
	 * Esegue il download clientside del documento indicato da url ($doc_id)
	 *
	 * @access public
	 * @return stream
	 */
	public function download() {

		$doc_id = cleanVar($_GET, 'id', 'int', '');

		if(!empty($doc_id)) {
			$n = new newsItem($doc_id, $this);
			if(!$n->id) {
				error::raise404();
			}
			if(!$this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, $this->_user_group, $this->_group_3) && $n->private) {
				error::raise404();
			}

			$attached = $n->attached;
			if($attached) {
				$full_path = $this->getBaseAbsPath('attached').$this->_os.$attached;
				download($full_path);
			}
			else {
				error::raise404();
			}
		}

		error::raise404();
	}

	/**
	 * Front end vetrina news 
	 * 
	 * @access public
	 * @return vetrina ultime news
	 */
	public function showcase() {
		
		$this->setAccess($this->_access_base);

		if($this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, $this->_user_group, $this->_group_3)) {
			$private = true;
		}
		else {
			$private = false;
		}

		$registry = registry::instance();
		$registry->addCss($this->_class_www."/news_".$this->_instanceName.".css");
		$registry->addJs($this->_class_www."/news.js");

		$news = newsItem::get($this, array('private'=>$private, 'order'=>'date DESC, insertion_date DESC', 'limit'=>array(0, $this->_showcase_news_number)));

		preg_match_all("#{{[^}]+}}#", $this->_showcase_news_code, $matches);
		$items = array();
		$ctrls = array();
		$indexes = array();
		$i = 0;
		$tot = count($news);
		foreach($news as $n) {
			$indexes[] = $i;
			$buffer = "<div class='showcase_item' style='display: block;z-index:".($tot-$i)."' id=\"news_$i\">";
			$buffer .= $this->parseTemplate($n, $this->_showcase_news_code, $matches);
			$buffer .= "</div>";
			$items[] = $buffer;

			$onclick = "newslider.set($i)";
			$ctrls[] = "<div id=\"sym_".$this->_instance.'_'.$i."\" class=\"scase_sym\" onclick=\"$onclick\"><span></span></div>";
			$i++;
		}

		$options = '{}';
		if($this->_showcase_auto_start) {
			$options = "{auto_start: true, auto_interval: ".$this->_showcase_auto_interval."}";
		}

		$view = new view($this->_view_dir);

		$view->setViewTpl('showcase');
		$view->assign('section_id', 'showcase_news_'.$this->_instanceName);
		$view->assign('wrapper_id', 'showcase_items_news_'.$this->_instanceName);
		$view->assign('ctrl_begin', 'sym_'.$this->_instance.'_');
		$view->assign('title', $this->_title_showcase);
		$view->assign('feed', "<a href=\"".$this->_plink->aLink($this->_instanceName, 'feedRSS')."\">".pub::icon('feed')."</a>");
		$view->assign('items', $items);
		$view->assign('ctrls', $ctrls);
		$view->assign('options', $options);

		return $view->render();
	}

	/**
	 * Front end ultime news 
	 * 
	 * @access public
	 * @return lista ultime news
	 */
	public function last() {

		$this->setAccess($this->_access_base);

		if($this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, $this->_user_group, $this->_group_3)) {
			$private = true;
		}
		else {
			$private = false;
		}

		$title_site = pub::variable('head_title');
		$title = $title_site.($this->_title_list ? " - ".$this->_title_list : "");

		$registry = registry::instance();
		$registry->addCss($this->_class_www."/news_".$this->_instanceName.".css");
		$registry->addHeadLink(array(
			'rel' => 'alternate',
			'type' => 'application/rss+xml',
			'title' => jsVar($title),
			'href' => $this->_url_root.SITE_WWW.'/'.$this->_plink->aLink($this->_instanceName, 'feedRSS') 	
		));

		$news = newsItem::get($this, array('private'=>$private, 'order'=>'date DESC, insertion_date DESC', 'limit'=>array(0, $this->_last_news_number)));

		preg_match_all("#{{[^}]+}}#", $this->_last_news_code, $matches);
		$items = array();
		foreach($news as $n) {
			$items[] = $this->parseTemplate($n, $this->_last_news_code, $matches);
		}

		$archive = "<a href=\"".$this->_plink->aLink($this->_instanceName, 'archive')."\">"._('elenco completo')."</a>";

		$view = new view($this->_view_dir);

		$view->setViewTpl('last');
		$view->assign('section_id', 'last_news_'.$this->_instanceName);
		$view->assign('title', $this->_title_last);
		$view->assign('feed', "<a href=\"".$this->_plink->aLink($this->_instanceName, 'feedRSS')."\">".pub::icon('feed')."</a>");
		$view->assign('items', $items);
		$view->assign('archive', $archive);

		return $view->render();

	}

	/**
	 * Front end dettagli news 
	 * 
	 * @access public
	 * @return dettagli news
	 */
	public function detail() {

		$slug = cleanVar($_GET, 'id', 'string', '');

		$item = newsItem::getFromSlug($slug, $this);

		if(!$item || !$item->id || !$item->published || ($item->private && !$this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, $this->_user_group, $this->_group_3))) {
			error::raise404();
		}

		$registry = registry::instance();
		$registry->addCss($this->_class_www."/news_".$this->_instanceName.".css");

		preg_match_all("#{{[^}]+}}#", $this->_detail_news_code, $matches);
		$tpl = $this->parseTemplate($item, $this->_detail_news_code, $matches);

		$view = new view($this->_view_dir);

		$view->setViewTpl('detail');
		$view->assign('section_id', 'detail_news_'.$this->_instanceName);
		$view->assign('tpl', $tpl);

		return $view->render();

	}

	/**
	 * Front end archivio news 
	 * 
	 * @access public
	 * @return lista ultime news
	 */
	public function archive() {

		$this->setAccess($this->_access_base);

		$registry = registry::instance();
		$registry->addCss($this->_class_www."/news_".$this->_instanceName.".css");

		$ctgslug = cleanVar($_GET, 'ctg', 'string', '');

		if($this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, $this->_user_group, $this->_group_3)) {
			$private = true;
		}
		else {
			$private = false;
		}

		if($ctgslug) {
			$ctg = newsCtg::getFromSlug($ctgslug, $this);
			$ctg_id = $ctg ? $ctg->id : 0;
		}
		else {
			$ctg_id = 0;
		}

		$news_number = newsItem::getCount($this, array('private'=>$private, 'ctg'=>$ctg_id));

		$pagination = new pagelist($this->_list_nfp, $news_number, 'array');
		$limit = array($pagination->start(), $this->_list_nfp);

		$news = newsItem::get($this, array('private'=>$private, 'ctg'=>$ctg_id, 'order'=>'date DESC, insertion_date DESC', 'limit'=>$limit));

		preg_match_all("#{{[^}]+}}#", $this->_list_news_code, $matches);
		$items = array();
		foreach($news as $n) {
			$items[] = $this->parseTemplate($n, $this->_list_news_code, $matches);
		}

		$view = new view($this->_view_dir);
		$view->setViewTpl('archive');
		$view->assign('section_id', 'archive_news_'.$this->_instanceName);
		$view->assign('title', $this->_title_list.($ctg_id ? ' '.htmlChars($ctg->ml('name')) : ''));
		$view->assign('feed', "<a href=\"".$this->_plink->aLink($this->_instanceName, 'feedRSS')."\">".pub::icon('feed')."</a>");
		$view->assign('items', $items);
		$view->assign('pagination_summary', $pagination->reassumedPrint());
		$view->assign('pagination_navigation', $pagination->listReferenceGINO($this->_plink->aLink($this->_instanceName, 'archive', '', ($ctg_id ? 'ctg='.$ctgslug : ''), array("basename"=>false))));

		return $view->render();

	}

	/**
	 * Parserizzazione dei template inseriti da opzioni 
	 * 
	 * @param newsItem $item istanza di newsItem
	 * @param string $tpl codice del template 
	 * @param array $matches matches delle variabili da sostituire
	 * @return template parserizzato
	 */
	private function parseTemplate($item, $tpl, $matches) {

		if(isset($matches[0])) {
			foreach($matches[0] as $m) {
				$code = trim(preg_replace("#{|}#", "", $m));
				if($pos = strrpos($code, '|')) {
					$property = substr($code, 0, $pos);
					$filter = substr($code, $pos + 1);
				}
				else {
					$property = $code;
					$filter = null;
				}

				$replace = $this->replaceTplVar($property, $filter, $item);
				$tpl = preg_replace("#".preg_quote($m)."#", $replace, $tpl);
			} 
		}

		return $tpl;
	}

	/**
	 * Replace di una proprietà di newsItem all'interno del template 
	 * 
	 * @param string $property proprietà da sostituire
	 * @param string $filter filtro applicato
	 * @param newsItem $obj istanza di newsItem
	 * @return replace del parametro proprietà
	 */
	private function replaceTplVar($property, $filter, $obj) {

		$pre_filter = '';

		if($property == 'thumb') {
			if($obj->img) {
				$pre_filter = "<img src=\"".$obj->thumbPath($this)."\" alt=\"thumb: ".jsVar($obj->ml('title'))."\" />";	
			}
			else return '';
		}
    elseif($property == 'thumb_path') {
			if($obj->img) {
				$pre_filter = $obj->thumbPath($this);
			}
			else return '';
		}
		elseif($property == 'img') {
			if($obj->img) {
				$pre_filter = "<img src=\"".$obj->imgPath($this)."\" alt=\"img: ".jsVar($obj->ml('title'))."\" />";	
			}
			else return '';
		}
		elseif($property == 'img_path') {
			if($obj->img) {
				$pre_filter = $obj->imgPath($this);
			}
			else return '';
		}
		elseif($property == 'url') {
			$pre_filter = $this->_plink->aLink($this->_instanceName, 'detail', array('id'=>$obj->slug));	
		}

		elseif($property == 'date') {
			$pre_filter = date('d/m/Y', strtotime($obj->{$property}));
		}
		elseif($property == 'text' || $property == 'title') {
			$pre_filter = htmlChars($obj->ml($property));
		}
		elseif($property == 'attached') {
			if($obj->attached) {
				$pre_filter = "<a class=\"attached\" href=\"".$this->_plink->aLink($this->_instanceName, 'download', array('id'=>$obj->id))."\">".$obj->attached."</a>";
			}
		}
		elseif($property == 'social') {
			if($obj->social) {
				$pre_filter = shareAll('all', $this->_url_root.SITE_WWW."/".$this->_plink->aLink($this->_instanceName, 'detail', array('id'=>$obj->slug)), htmlChars($obj->ml('title')));
			}
			else {
				return '';
			}
		}
		elseif($property == 'feed') {
			$pre_filter = "<a href=\"".$this->_plink->aLink($this->_instanceName, 'feedRSS')."\">".pub::icon('feed')."</a>";
		}
		elseif($property == 'categories') {
			$ctgs = $obj->categories($this);
			$categories = array();
			if(count($ctgs)) {
				foreach($ctgs as $ctg) {
					$categories[] = "<a href=\"".$this->_plink->aLink($this->_instanceName, 'archive', array('ctg'=>$ctg->slug))."\">".$ctg->ml('name')."</a>";	
				}
			}
			$pre_filter = implode(", ", $categories);
		}
    elseif($property == 'categories_images') {
			$ctgs = $obj->categories($this);
			$images = array();
			if(count($ctgs)) {
				foreach($ctgs as $ctg) {
					if($ctg->image) {
                        $images[] = "<img src=\"".$ctg->imagePath($this)."\" alt=\"".$ctg->ml('name')."\" />";	
                    }
				}
			}
			$pre_filter = implode(", ", $images);
		}
		else {
			return '';
		}

		if(is_null($filter)) {
			return $pre_filter;
		}

		if($filter == 'link') {
			return "<a href=\"".$this->_plink->aLink($this->_instanceName, 'detail', array('id'=>$obj->slug))."\">".$pre_filter."</a>";
		}
		elseif(preg_match("#layer:(\d+)x(\d+)#", $filter, $matches)) {
			$width = $matches[1];
			$height = $matches[2];
			$onclick = "onclick=\"if(!window.myWin".$obj->id." || !window.myWin".$obj->id.".showing) {window.myWin".$obj->id." = new layerWindow({'title':'"._("Dettagli")."', 'url':'$this->_home?pt[$this->_instanceName-detail]&id=".$obj->slug."&layer=1', 'bodyId':'news_".$obj->id."', 'width':$width, 'height':".($height ? $height : 'null').", 'destroyOnClose':true, 'closeButtonUrl':'img/ico_close2.gif', 'disableObjects':true, reloadZindex:true});window.myWin".$obj->id.".display();}\"";
			return "<span class=\"link\" $onclick>".$pre_filter."</span>";
		}
		elseif(preg_match("#chars:(\d+)#", $filter, $matches)) {
			return cutHtmlText($pre_filter, $matches[1], '...', false, false, true, array('endingPosition'=>'in'));
		}
		elseif(preg_match("#class:(.+)#", $filter, $matches)) {
			if(isset($matches[1]) && ($property == 'thumb' || $property == 'img')) {
				return preg_replace("#<img#", "<img class=\"".$matches[1]."\"", $pre_filter);
			}
			else return $pre_filter;
		}
		elseif(preg_match("#style:\"(.+)\"#", $filter, $matches)) {
			if(isset($matches[1]) && ($property == 'thumb' || $property == 'img')) {
				return preg_replace("#<img#", "<img style=\"".$matches[1]."\"", $pre_filter);
			}
			else return $pre_filter;
		}
		else {
			return $pre_filter;
		}

	}


	/**
	 * Interfaccia di amministrazione del modulo 
	 * 
	 * @return interfaccia di back office
	 */
	public function manageDoc() {
		
		$this->accessGroup('ALL');

		$method = 'manageDoc';

		$htmltab = new htmlTab(array("linkPosition"=>'right', "title"=>$this->_instanceLabel));	
		$link_admin = "<a href=\"".$this->_home."?evt[$this->_instanceName-$method]&block=permissions\">"._("Permessi")."</a>";
		$link_css = "<a href=\"".$this->_home."?evt[$this->_instanceName-$method]&block=css\">"._("CSS")."</a>";
		$link_options = "<a href=\"".$this->_home."?evt[$this->_instanceName-$method]&block=options\">"._("Opzioni")."</a>";
		$link_ctg = "<a href=\"".$this->_home."?evt[$this->_instanceName-$method]&block=ctg\">"._("Categorie")."</a>";
		$link_dft = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-$method]\">"._("Contenuti")."</a>";

		$sel_link = $link_dft;

		// Variables
		$id = cleanVar($_GET, 'id', 'int', '');
		$start = cleanVar($_GET, 'start', 'int', '');
		// end

		if($this->_block == 'css') {
			$buffer = sysfunc::manageCss($this->_instance, $this->_className);		
			$sel_link = $link_css;
		}
		elseif($this->_block == 'permissions' && $this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, '', '')) {
			$buffer = sysfunc::managePermissions($this->_instance, $this->_className);		
			$sel_link = $link_admin;
		}
		elseif($this->_block == 'options' && $this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, '', '')) {
			$buffer = sysfunc::manageOptions($this->_instance, $this->_className);		
			$sel_link = $link_options;
		}
		elseif($this->_block == 'ctg' && $this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, $this->_user_group, $this->_group_2)) {
			$buffer = $this->manageCategory();		
			$sel_link = $link_ctg;
		}
		else {
			$buffer = $this->manageNews();
		}

		// groups privileges
		if($this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, '', '')) {
			$links_array = array($link_admin, $link_css, $link_options, $link_ctg, $link_dft);
		}
		elseif($this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, $this->_user_group, $this->_group_2)) {
			$links_array = array($link_ctg, $link_dft);
		}
		else $links_array = array($link_dft);

		$htmltab->navigationLinks = $links_array;
		$htmltab->selectedLink = $sel_link;
		$htmltab->htmlContent = $buffer;

		return $htmltab->render();
	}

	/**
	 * Interfaccia di amministrazione delle categorie 
	 * 
	 * @return interfaccia di back office
	 */
	private function manageCategory() {

		$registry = registry::instance();
		$registry->addJs($this->_class_www.'/news.js');

		$admin_table = new adminTable($this, array());

    $edit = cleanVar($_GET, 'edit', 'int', '');

    $name_onblur = !$edit 
      ? "onblur=\"$('slug').value = $(this).value.slugify()\""
      : '';

		$buffer = $admin_table->backOffice(
			'newsCtg', 
			array(
				'list_display' => array('id', 'name', 'slug'),
				'list_title'=>_("Elenco categorie"), 
				'list_description'=>"<p>"._('Ciascuna news inserita potrà essere associata ad una o più categorie qui definite.')."</p>",
			     ),
			array(), 
			array(
				'name' => array(
					'js' => $name_onblur
				),
				'slug' => array(
					'id' => 'slug'
				),
				'description' => array(
					'widget'=>'editor', 
					'notes'=>false, 
					'img_preview'=>false, 
				),
                'image' => array(
                    'preview' => true
                )
			)
		);

		return $buffer;
	}

	/**
	 * Interfaccia di amministrazione delle news 
	 * 
	 * @return interfaccia di back office
	 */
	private function manageNews() {

		$registry = registry::instance();
		$registry->addJs($this->_class_www.'/news.js');

    $edit = cleanVar($_GET, 'edit', 'int', '');

    $name_onblur = !$edit 
      ? "onblur=\"var date = $('date').value; $('slug').value = date.substring(6, 10) + date.substring(3, 5) + date.substring(0, 2) + '-' + $(this).value.slugify()\""
      : '';
	
		if(!$this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, $this->_user_group, $this->_group_1)) {
			$remove_fields = array('published');
			$delete_deny = 'all';
		}
		else {
			$remove_fields = array();
			$delete_deny = array();
		}

		$admin_table = new adminTable($this, array('delete_deny'=>$delete_deny));

		$buffer = $admin_table->backOffice(
			'newsItem', 
			array(
				'list_display' => array('id', 'date', 'categories', 'title', 'published', 'private'),
				'list_title'=>_("Elenco news"), 
				'filter_fields'=>array('categories', 'title', 'published') 
			),
			array(
				'removeFields' => $remove_fields
			), 
			array(
        'date' => array(
          'id' => 'date'
        ),
				'title' => array(
					'js' => $name_onblur
				),
				'slug' => array(
					'id' => 'slug'
				),
				'text' => array(
					'widget'=>'editor', 
					'notes'=>false, 
					'img_preview'=>false, 
				),
				'img' => array(
					'preview'=>true
				)
			)
		);

		return $buffer;
	}

	/**
	 * Metodo per la definizione di parametri da utilizzare per il modulo "Ricerca nel sito"
	 *
	 * Il modulo "Ricerca nel sito" di Gino base chiama questo metodo per ottenere informazioni riguardo alla tabella, campi, pesi etc...
	 * per effettuare la ricerca dei contenuti.
	 *
	 * @access public
	 * @return array[string]mixed array associativo contenente i parametri per la ricerca
	 */
	public function searchSite() {
		
		if($this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, $this->_user_group, $this->_group_3)) {
			$private = true;
		}
		else {
			$private = false;
		}

		return array(
			"table"=>newsItem::$tbl_item, 
			"selected_fields"=>array("id", "slug", "date", array("highlight"=>true, "field"=>"title"), array("highlight"=>true, "field"=>"text")), 
			"required_clauses"=>$private ? array("instance"=>$this->_instance, 'published'=>1) : array("instance"=>$this->_instance, 'private'=>0, 'published'=>1), 
			"weight_clauses"=>array("title"=>array("weight"=>3), "text"=>array("weight"=>1))
		);
	}

	/**
	 * Definisce la presentazione del singolo item trovato a seguito di ricerca (modulo "Ricerca nel sito")
	 *
	 * @param mixed array array[string]string array associativo contenente i risultati della ricerca
	 * @access public
	 * @return void
	 */
	public function searchSiteResult($results) {
	
		$obj = new newsItem($results['id'], $this);

		$buffer = "<div>".dbDatetimeToDate($results['date'], "/")." <a href=\"".$this->_plink->aLink($this->_instanceName, 'detail', array('id'=>$results['slug']))."\">";
		$buffer .= $results['title'] ? htmlChars($results['title']) : htmlChars($obj->ml('title'));
		$buffer .= "</a></div>";

		if($results['text']) {
			$buffer .= "<div class=\"search_text_result\">...".htmlChars($results['text'])."...</div>";
		}
		else {
			$buffer .= "<div class=\"search_text_result\">".htmlChars(cutHtmlText($obj->ml('text'), 120, '...', false, false, false, array('endingPosition'=>'in')))."</div>";
		}
		
		return $buffer;

	}

    /**
     * Adattatore per la classe newsletter 
     * 
     * @access public
     * @return array di elementi esportabili nella newsletter
     */
    public function systemNewsletterList() {
        
        $news = newsItem::get($this, array('private'=>true, 'order'=>'date DESC, insertion_date DESC', 'limit'=>array(0, $this->_newsletter_news_number)));

        $items = array();
        foreach($news as $n) {
            $items[] = array(
                _('id') => $n->id,
                _('titolo') => htmlChars($n->ml('title')),
                _('privata') => $n->private ? _('si') : _('no'),
                _('pubblicata') => $n->published ? _('si') : _('no'),
                _('data') => dbDateToDate($n->date),
            ); 
        }

        return $items;
    }

    /**
     * Contenuto di una news quanto inserita in una newsletter 
     * 
     * @param int $id identificativo della news
     * @access public
     * @return contenuto news
     */
    public function systemNewsletterRender($id) {

        $n = new newsItem($id, $this);

        preg_match_all("#{{[^}]+}}#", $this->_newsletter_news_code, $matches);
        $buffer = $this->parseTemplate($n, $this->_newsletter_news_code, $matches);

        return $buffer;

    }

	/**
	 * Genera un feed RSS standard che presenta le ultime 50 news pubblicate
	 *
	 * @access public
	 * @return string xml che definisce il feed RSS
	 */
	public function feedRSS() {

		$this->accessType($this->_access_base);

		header("Content-type: text/xml; charset=utf-8");

		$function = "feedRSS";
		$title_site = pub::variable('head_title');
		$title = $title_site.($this->_title_list ? " - ".$this->_title_list : "");
		$description = $this->_db->getFieldFromId(TBL_MODULE, 'description', 'id', $this->_instance);

		$header = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
		$header .= "<rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\">\n";
		$header .= "<channel>\n";
		$header .= "<atom:link href=\"".$this->_url_root.$this->_home."?pt%5B$this->_instanceName-".$function."%5D\" rel=\"self\" type=\"application/rss+xml\" />\n";
		$header .= "<title>".$title."</title>\n";
		$header .= "<link>".$this->_url_root.$this->_home."</link>\n";
		$header .= "<description>".$description."</description>\n";
		$header .= "<language>$this->_lng_nav</language>";
		$header .= "<copyright> Copyright 2012 Otto srl </copyright>\n";
		$header .= "<docs>http://blogs.law.harvard.edu/tech/rss</docs>\n";

		echo $header;

		$news = newsItem::get($this, array('private'=>false, 'order'=>'date DESC, insertion_date DESC', 'limit'=>array(0, 50)));
		if(count($news) > 0) {
			foreach($news as $n) {
				$id = htmlChars($n->id);
				$title = htmlChars($n->ml('title'));
				$text = htmlChars($n->ml('text'));
				$text = str_replace("src=\"", "src=\"".substr($this->_url_root,0,strrpos($this->_url_root,"/")), $text);
				$text = str_replace("href=\"", "href=\"".substr($this->_url_root,0,strrpos($this->_url_root,"/")), $text);

				$date = date('d/m/Y', strtotime($n->date));

				echo "<item>\n";
				echo "<title>".$date.". ".$title."</title>\n";
				echo "<link>".$this->_url_root.SITE_WWW."/".$this->_plink->aLink($this->_instanceName, 'detail', array("id"=>$n->slug))."</link>\n";
				echo "<description>\n";
				echo "<![CDATA[\n";
				echo $text;
				echo "]]>\n";
				echo "</description>\n";
				echo "<guid>".$this->_url_root.SITE_WWW.$this->_plink->aLink($this->_instanceName, 'detail', array("id"=>$n->slug))."</guid>\n";
				echo "</item>\n";
			}
		}

		$footer = "</channel>\n";
		$footer .= "</rss>\n";

		echo $footer;
		exit;
	}

}

?>
