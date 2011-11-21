<?php
/**
* Gino News
*
* Modulo per la gestione e presentazione di news. Viene gestito un flusso che comprende
* la redazione dei contenuti e la loro pubblicazione. Esistono dei gruppi di utenti che 
* possono accedere alle varie funzionalità.
*
* @package gino-news
* @version 1.0
* @copyright 2005 Otto srl
* @author Marco Guidotti <guidottim@gmail.com>, abidibo <abidibo@gmail.com>
* @license http://www.opensource.org/licenses/mit-license.php MIT license
*/

/**
 * La classe category è inclusa in Gino base 
 */
require_once(CLASSES_DIR.OS."class.category.php");

/**
 * Classe per la gestione di news categorizzate
 *
 * Campi:
 *
 * * ctg: categoria
 * * title: titolo della news
 * * text: testo della news
 * * img: campo file immagine, con generazione automatica di thumb personalizzabile da opzioni
 * * filename: campo file allegato
 * * date: data di pubblicazione
 * * private: accesso alla news ristretto agli appartenenti al gruppo "iscritti"
 * * social: attivazione condivisione social networks
 * * published: pubblicazione della news
 *
 * Il modulo comprende una vasta serie di opzioni per controllare al meglio la parte pubblica.
 *
 * Gli output disponibili sono:
 *
 * * ultime n news (n da opzioni)
 * * elenco news paginate
 * * vista singola news
 * 
 * @uses AbstractEvtClass
 * @package gino-news 
 * @version 1.0
 * @copyright 2005 Otto srl
 * @author Marco Guidotti <guidottim@gmail.com>, abidibo <abidibo@gmail.com> 
 * @license http://www.opensource.org/licenses/mit-license.php MIT license
 */
class news extends AbstractEvtClass{

	/**
	 * Percorso assoluto alla cartella di upload di immagini ed allegati 
	 * 
	 * @var string 
	 * @access protected
	 */
	protected $_data_dir;

	/**
	 * Percorso relativo alla cartella di upload di immagini ed allegati 
	 * 
	 * @var string 
	 * @access protected
	 */
	protected $_data_www;

	/**
	 * Array associativo contenente i valori di default delle opzioni 
	 * 
	 * @var array[string]mixed 
	 * @access private
	 */
	private $_optionsValue = array();

	/**
	 * Opzione titolo della vista "ultime news" 
	 * 
	 * @var string 
	 * @access private
	 */
	private $_title_last;

	/**
	 * Opzione titolo della vista "elenco news" 
	 * 
	 * @var string 
	 * @access private
	 */
	private $_title_page;

 	/**
 	 * Opzione visualizzazione categorie (bool 0 | 1 ) 
 	 * 
 	 * @var int 
 	 * @access private
 	 */
 	private $_view_ctg = 0;
	
	/**
	 * Opzione numero di news per pagina nella vista "elenco news" 
	 * 
	 * @var int 
	 * @access private
	 */
	private $_news_for_page = 10;

	/**
	 * Opzione numero di news mostrate nella vista "ultime news" 
	 * 
	 * @var int 
	 * @access private
	 */
	private $_news_homepage = 4;
	
	/**
	 * Opzione numero di caratteri mostrati per news nella vista "ultime news" 
	 * 
	 * @var int 
	 * @access private
	 */
	private $_news_char = 60;

	/**
	 * Opzione modalità di apertura news completa (1: layer, 2: espansione, 3: nuova pagina) 
	 * 
	 * @var int  
	 * @access private
	 */
	private $_win_layer = 2;

	/**
	 * Opzione larghezza layer 
	 * 
	 * @var int 
	 * @access private
	 */
	private $_win_width = 300;

	/**
	 * Opzione altezza layer 
	 * 
	 * @var int 
	 * @access private
	 */
	private $_win_height = 150;
	
	/**
	 * Opzione abilitazione effetto lightbox sulle thumb (bool 0 | 1) 
	 * 
	 * @var int 
	 * @access private
	 */
	private $_news_img_lightbox = 0;

	/**
	 * Opzione visualizza news completa al click sull'immagine thumb (bool 0 | 1). Se attiva disabilita l'opzione lightbox 
	 * 
	 * @var int 
	 * @access private
	 */
	private $_img_expand = 0;	
	
	/**
	 * Opzione visualizzazione form di ricerca in "elenco news" 
	 * 
	 * @var int 
	 * @access private
	 */
	private $_search_form = 0;
	
	/**
	 * Opzione larghezza usata per il ridimensionamnto delle immagini (px) 
	 * 
	 * @var int 
	 * @access private
	 */
	private $_width_img = 600;

	/**
	 * Opzione larghezza usata per la generazione delle thumb (px) 
	 * 
	 * @var int 
	 * @access private
	 */
	private $_width_thumb = 80;

	/**
	 * Opzione visualizzazione link feed rss 
	 * 
	 * @var int 
	 * @access private
	 */
	private $_feed_rss = 0;
	
	/**
	 * Oggetto di tipo options per la gestione automatica delle opzioni 
	 * 
	 * @var object 
	 * @access private
	 */
	private $_options;

	/**
	 * Elenco di proprietà delle opzioni per la creazione del form 
	 * 
	 * @var array[string]mixed 
	 * @access public
	 */
	public $_optionsLabels = array();
	
	/**
	 * Contiene gli id dei gruppi abilitati alla pubblicazione delle news 
	 * 
	 * @var array[int] 
	 * @access private
	 */
	private $_group_1 = array();

	/**
	 * Contiene gli id dei gruppi abilitati alla redazione delle news 
	 * 
	 * @var array[int] 
	 * @access private
	 */
	private $_group_2 = array();

	/**
	 * Contiene gli id dei gruppi abilitati alla visualizzazione delle news private 
	 * 
	 * @var array[int] 
	 * @access private
	 */
	private $_group_3 = array();
	
	/**
	 * Settaggio della toolbar dell'editor CKEditor, possibili valori: 'Basic' | 'Full' 
	 * 
	 * @var string 
	 * @access private
	 */
	private $_fck_toolbar = 'Full';

	/**
	 * Larghezza dell'editor CKEditor (#pixel oppure %, i.e. 500 | '90%') 
	 * 
	 * @var mixed 
	 * @access private
	 */
	private $_fck_width = '100%';

	/**
	 * Altezza dell'editor CKEditor (#pixel oppure %, i.e. 300 | '90%') 
	 * 
	 * @var mixed 
	 * @access private
	 */
	private $_fck_height = 150;
	
	/**
	 * Nome della tabella che contiene i record delle news 
	 * 
	 * @var string 
	 * @access private
	 */
	private $_tbl_news = 'news';

 	/**
 	 * Nome della tabella che contiene le categorie 
 	 * 
 	 * @var string 
 	 * @access private
 	 */
 	private $_tbl_ctg = 'news_ctg';
 	
	/**
	 * Nome della tabella che contiene le opzioni 
	 * 
	 * @var string 
	 * @access private
	 */
	private $_tbl_opt = 'news_opt';

	/**
	 * Nome della tabella che contiene l'associazione utenti-gruppi 
	 * 
	 * @var string 
	 * @access private
	 */
	private $_tbl_usr = 'news_usr';

	/**
	 * Nome del campo data di pubblicazione 
	 * 
	 * @var string 
	 * @access private
	 */
	private $_field_date = 'date';
	
	/**
	 * Lista delle estensioni permesse per l'upload di immagini (estensioni supportate: jpg, png se supportate dalle librerie gd) 
	 * 
	 * @var array 
	 * @access private
	 */
	private $_extension_media = array('jpg', 'png');

	/**
	 * Lista delle estensioni permesse per l'upload di allegati 
	 * 
	 * @var array 
	 * @access private
	 */
	private $_extension_attach = array('pdf', 'txt', 'rtf', 'doc');
	
	/**
	 * Prefisso delle immagini uploadate 
	 * 
	 * @var string prefisso delle immagini uploadate
	 * @access private
	 */
	private $_prefix_img = 'img_';

	/**
	 * Prefisso delle thumb generate dalle immagini uploadate 
	 * 
	 * @var string 
	 * @access private
	 */
	private $_prefix_thumb = 'thumb_';
	
	/**
	 * Parametro action letto da url 
	 * 
	 * @var string 
	 * @access private
	 */
	private $_action;

	/**
	 * Parametro block letto da url 
	 * 
	 * @var string 
	 * @access private
	 */
	private $_block;
	
	/**
	 * Costruisce un'istanza di tipo news 
	 * 
	 * @param int $mdlId id dell'istanza di tipo news
	 * @access public
	 * @return void
	 */
	function __construct($mdlId){
		
		parent::__construct();

		$this->_instance = $mdlId;
		$this->_instanceName = $this->_db->getFieldFromId($this->_tbl_module, 'name', 'id', $this->_instance);
		$this->_instanceLabel = $this->_db->getFieldFromId($this->_tbl_module, 'label', 'id', $this->_instance);

		$this->_data_dir = $this->_data_dir.$this->_os.$this->_instanceName;
		$this->_data_www = $this->_data_www."/".$this->_instanceName;

		$this->setAccess();
		$this->setGroups();
		
		/*
			Opzioni
		*/
		
		// Valori di default
		$this->_optionsValue = array(
			'title_last'=>_("News"), 
			'title_page'=>_("News"), 
			'home_news'=>4, 
			'page_news'=>10, 
			'summary_char'=>60, 
			'layer'=>2, 
			'layer_width'=>300, 
			'layer_height'=>150, 
			'width_img'=>600, 
			'width_thumb'=>80
		);

		$this->_title_last = htmlChars($this->setOption('title_last', array('value'=>$this->_optionsValue['title_last'], 'translation'=>true)));
		$this->_title_page = htmlChars($this->setOption('title_page', array('value'=>$this->_optionsValue['title_page'], 'translation'=>true)));
		$this->_view_ctg = $this->setOption('view_ctg');
		$this->setNewsHomePage($this->setOption('home_news', array('value'=>$this->_optionsValue['home_news'])));
		$this->setNewsForPage($this->setOption('page_news', array('value'=>$this->_optionsValue['page_news'])));
		$this->_news_char = $this->setOption('summary_char', array('value'=>$this->_optionsValue['summary_char']));
		$this->_win_layer = $this->setOption('layer', array('value'=>$this->_optionsValue['layer']));
		$this->_win_width = $this->setOption('layer_width', array('value'=>$this->_optionsValue['layer_width']));
		$this->_win_height = $this->setOption('layer_height', array('value'=>$this->_optionsValue['layer_height']));
		$this->_news_img_lightbox = $this->setOption('img_lightbox');
		$this->_img_expand = $this->setOption('img_expand');
		$this->_search_form = $this->setOption('news_search');
		$this->_width_img = $this->setOption('width_img', array('value'=>$this->_optionsValue['width_img']));
		$this->_width_thumb = $this->setOption('width_thumb', array('value'=>$this->_optionsValue['width_thumb']));
		$this->_feed_rss = $this->setOption('feed_rss');

		$this->_options = new options($this->_className, $this->_instance);
		$this->_optionsLabels = array(
		"title_last"=>array('label'=>_("Titolo ultime news"), 'value'=>$this->_optionsValue['title_last'], 'required'=>false),
		"title_page"=>array('label'=>_("Titolo news paginate"), 'value'=>$this->_optionsValue['title_page'], 'required'=>false),
		"view_ctg"=>array('label'=>_("Visualizza categorie (pagina)")),
		"home_news"=>array('label'=>_("Numero news mostrate in 'ultime news'"), 'value'=>$this->_optionsValue['home_news']),
		"page_news"=>array('label'=>_("Numero news per pagina"), 'value'=>$this->_optionsValue['page_news']),
		"summary_char"=>array('label'=>_("Numero caratteri riassunto"), 'value'=>$this->_optionsValue['summary_char']),
		"layer"=>array('label'=>array(_("Visualizzazione news completa"), _("'1': apertura in layer (no social)<br/>'2': apertura nella pagina stessa<br />'3': apertura in nuova pagina"))),
		"layer_width"=>array('label'=>array(_("Larghezza finestra (px)"), _("attiva solo se si setta a 'sì' l'opzione precedente")), 'value'=>$this->_optionsValue['layer_width']),
		"layer_height"=>array('label'=>array(_("Altezza finestra (px)"), _("attiva solo se si setta a 'sì' l'opzione precedente. Se il campo viene lasciato vuoto o nullo l'altezza verrà settata automaticamente dal sistema a seconsa del contenuto")), 'value'=>$this->_optionsValue['layer_height']),
		"img_lightbox"=>array('label'=>_("Effetto lightbox sulla thumb")),
		"img_expand"=>array('label'=>array(_("Effetto espansione news sulla thumb"), _("se attivo disabilita l'opzione precedente"))),
		"news_search"=>array('label'=>_("Ricerca news")),
		"width_img"=>array('label'=>_("Larghezza max immagini (px)"), 'value'=>$this->_optionsValue['width_img']),
		"width_thumb"=>array('label'=>_("Larghezza max thumbs delle immagini (px)"), 'value'=>$this->_optionsValue['width_thumb']),
		"feed_rss"=>array('label'=>_("Attiva feed RSS"))
		);

		if(!pub::enabledPng()) {
			$this->_extension_media = array_diff($this->_extension_media, array('png'));
		}

		$this->_action = cleanVar($_REQUEST, 'action', 'string', '');
		$this->_block = cleanVar($_REQUEST, 'block', 'string', '');
	}
	
	/**
	 * Restituisce alcune proprietà della classe utili per la generazione di nuove istanze 
	 * 
	 * @access public
	 * @return array[string]array lista proprietà utilizzate per la creazione di istanze di tipo news
	 */
	public function getClassElements() {

		return array("tables"=>array('news', 'news_ctg', 'news_opt', 'news_grp', 'news_usr'),
			     "css"=>array('news.css'),
			     "folderStructure"=>array(
				CONTENT_DIR.OS.'news'=>null	
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
		 * delete records and translations from table news
		 */
		$query = "SELECT id FROM ".$this->_tbl_news." WHERE instance='$this->_instance'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a)>0) 
			foreach($a as $b) 
				language::deleteTranslations($this->_tbl_news, $b['id']);
		
		$query = "DELETE FROM ".$this->_tbl_news." WHERE instance='$this->_instance'";	
		$result = $this->_db->actionquery($query);
		
		/*
		 * delete record and translations from table news_ctg
		 */
		$query = "SELECT id FROM ".$this->_tbl_ctg." WHERE instance='$this->_instance'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a)>0) {
			foreach($a as $b) {
				language::deleteTranslations($this->_tbl_ctg, $b['id']);
			}
		}
		$query = "DELETE FROM ".$this->_tbl_ctg." WHERE instance='$this->_instance'";	
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
	 * Definizione dei gruppi che gestiscono l'accesso alle funzionalità amministrative
	 *
	 * @access private
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
	 * Metodo chiamato per settare headlines personalizzati a seconda del metodo
	 *
	 * Questo metodo viene chiamato dalla classe document di Gino base per sovrascrivere il valore di alcuni
	 * tag presenti nell'head (meta_title, description, image_src). Lo scopo è quello di personalizzare gli headlines
	 * a seconda del metodo invocato. Utile soprattutto per la condivisione con i social networks. 
	 * 
	 * @param string $method il metodo per il quale settare gli headline
	 * @access public
	 * @return mixed array associativo contenente i valori dei tag head da settare, oppure null
	 */
	public function getHeadlines($method) {

		if($method=='view') {
			$id = cleanVar($_GET, 'id', 'int', '');

			$title = htmlChars(pub::variable('head_title'))." - ".htmlChars($this->_trd->selectTXT($this->_tbl_news, 'title', $id, 'id'));
			$image = $this->_db->getFieldFromId($this->_tbl_news, 'img', 'id', $id);

			$description = cutHtmlText(htmlChars($this->_trd->selectTXT($this->_tbl_news, 'text', $id, 'id')), $this->_news_char, '...', true, false, true);
			$image_src = is_file($this->_data_dir.$this->_os.$this->_prefix_thumb.$image) 
					? $this->_url_root.$this->_data_www."/".$this->_prefix_thumb.$image
					: null;
			return array("meta_title"=>$title, "description"=>$description, "image_src"=>$image_src);
		}
		else return null;
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
			"blockList" => array("label"=>_("Lista utime news"), "role"=>'1'),
			"viewList" => array("label"=>_("Lista news paginata"), "role"=>'1')
		);

		return $list;
	}
	
	/**
	 * Getter del numero di news presentate per pagina 
	 * 
	 * @access private
	 * @return int
	 */
	private function getNewsForPage() {
		return $this->_news_for_page;
	}
	
	/**
	 * Setter del numero di news presentate per pagina 
	 * 
	 * @param int $req_var numero di news per pagina
	 * @access private
	 * @return void
	 */
	private function setNewsForPage($req_var) {
	
		if($req_var) $this->_news_for_page = $req_var;
	}
	
	/**
	 * Getter del numero di news presentate nella vista "ultime news" 
	 * 
	 * @access private
	 * @return int
	 */
	private function getNewsHomePage() {
		return $this->_news_homepage;
	}
	
	/**
	 * Setter del numero di news presentate nella vista "ultime news" 
	 * 
	 * @param int $req_var numero di news nella vista "ultime news"
	 * @access private
	 * @return void
	 */
	private function setNewsHomePage($req_var) {
	
		if($req_var) $this->_news_homepage = $req_var;
	}
	
	/**
	 * Esegue il download clientside del documento indicato da url ($doc_id) 
	 * 
	 * @access public
	 * @return stream
	 */
	public function downloader(){
		
		$doc_id = cleanVar($_GET, 'id', 'int', '');
		
		if(!empty($doc_id))
		{
			$query = "SELECT filename FROM ".$this->_tbl_news." WHERE id='$doc_id'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				foreach($a AS $b)
				{
					$filename = htmlChars($b['filename']);
					$full_path = $this->_data_dir.$this->_os.$filename;
					
					download($full_path);
					exit();
				}
			}
			else exit();
		}
		else exit();
	}
	
	/**
	 * Metodo di output "ultime news" 
	 * 
	 * Presenta una lista delle ultime news pubblicate. Molti parametri di configurazione sono gestibili dalle opzioni.
	 *
	 * @access public
	 * @return string
	 */
	public function blockList(){

		$this->accessType($this->_access_base);

		$htmlsection = new htmlSection(array('id'=>"news_".$this->_instanceName,'class'=>'public', 'headerTag'=>'header', 'headerLabel'=>$this->_title_last));

		if($this->_feed_rss)
			$htmlsection->headerLinks = "<a href=\"".$this->_plink->aLink($this->_instanceName, 'feedRSS')."\">".pub::icon('feed')."</a>";

		$GINO = $this->scriptAsset("news_".$this->_instanceName.".css", "newsCSS".$this->_instance, 'css');
		
		$limit = $this->_db->limit($this->_news_homepage, 0);
		$query = $this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, $this->_user_group, $this->_group_3)
			? "SELECT id, ctg, img, filename, ".$this->_field_date.", private, social, published FROM ".$this->_tbl_news." WHERE instance='$this->_instance' AND published='1' ORDER BY date DESC $limit"
			: "SELECT id, ctg, img, filename, ".$this->_field_date.", private, social, published FROM ".$this->_tbl_news." WHERE instance='$this->_instance' AND published='1' AND private='no' ORDER BY date DESC $limit";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach($a AS $b)
			{
				$GINO .= $this->displayNews($b);
			}
			
			$htmlsection->footer = "<a href=\"".$this->_plink->aLink($this->_instanceName, 'viewList')."\">"._("elenco completo")."</a>\n";
		}
		else $GINO .= "<p class=\"message\">"._("Non risultano news pubblicate")."</p>";
		
		$htmlsection->content = $GINO;

		return $htmlsection->render();
	}
	
	/**
	 * Taglia il testo della news ad un numero preciso di caratteri (gestibile da opzioni) 
	 * 
	 * @param string $text il testo completo della news
	 * @access private
	 * @return string
	 */
	private function printSummary($text) {
		
		$ending = "... ";
			
		$summary = cutHtmlText($text, $this->_news_char, $ending, false, false, true, array("endingPosition"=>"in"));
		
		return $summary;
	}
	
	/**
	 * Restituisce la vista di una singola news espansa o collassata
	 *
	 * Molti aspetti di visualizzazione sono configurabili da opzioni.
	 * Contiene il controllo dei privilegi di accesso al contenuto richiesto.  
	 * La news viene presentata collassata o espansa a seconda del  valore del parametro url "full".  
	 * 
	 * @param mixed $data l'id della news da visualizzare o null se deve essere letto da url
	 * @param bool $check_published controllo o meno della pubblicazione della news
	 * @access public
	 * @return string
	 */
	public function displayNews($data=null, $check_published=true) {

		if(!$data || is_int($data)) {
			$id = is_int($data) ? $data : cleanVar($_GET, 'id', 'int', '');
			$query = "SELECT id, img, filename, date, private, ctg, social, published FROM ".$this->_tbl_news." WHERE id='$id'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a)>0) $data = $a[0];
			else return "";
		}
		$id = htmlChars($data['id']);
		$private = htmlChars($data['private']);
		$published = htmlChars($data['published']);
		$social = htmlChars($data['social']);

		if(!$published && $check_published) header("Location: $this->_home");
		
		if($private=='yes' && !$this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, $this->_user_group, $this->_group_3))
			exit(error::errorMessage(array('error'=>_("Permessi insufficienti per visualizzare i contenuti richiesti")), $this->_home));

		$full = cleanVar($_GET, 'full', 'int', '');

		if($full)
			$htmlsection = new htmlSection(array('id'=>"news_".$this->_instanceName,'class'=>'public full'));
		$ctg = htmlChars($data['ctg']);
		$img = htmlChars($data['img']);
		$filename = htmlChars($data['filename']);
		$date = $this->shortDate($data['date']);
		if($this->_win_layer==1)
			$onclick_exp = "onclick=\"if(!window.myWin$id || !window.myWin$id.showing) {window.myWin$id = new layerWindow({'title':'"._("Dettagli")."', 'url':'$this->_home?pt[$this->_instanceName-view]&id=$id&layer=1', 'bodyId':'news_$id', 'width':$this->_win_width, 'height':".($this->_win_height ? $this->_win_height : 'null').", 'destroyOnClose':true, 'closeButtonUrl':'img/ico_close2.gif', 'disableObjects':true});window.myWin$id.display();}\"";
		elseif($this->_win_layer==2)
			$onclick_exp = "onclick=\"if(\$chk(window.news$id) && window.news$id==1) {\$('n$id').set('html', $('cutNews$id').get('html'));window.news$id=0;}else{\$('n$id').set('html', $('fullNews$id').get('html'));window.news$id = 1;}\"";
		else 
			$onclick_exp = "onclick=\"location.href='".$this->_plink->aLink($this->_instanceName, 'view', array("id"=>$id))."';\"";

		$title = "<span class=\"link newsTitle\" $onclick_exp>".htmlChars($this->_trd->selectTXT($this->_tbl_news, 'title', $id))."</span>";
		$text = htmlChars($this->_trd->selectTXT($this->_tbl_news, 'text', $id));
		if($filename) $text .= "<p><a href=\"".$this->_plink->aLink($this->_instanceName, 'downloader', array("id"=>$id))."\">$filename</a></p>";			
		if($social=='yes') {
			$text .= shareAll("all", $this->_url_root.SITE_WWW."/".$this->_plink->aLink($this->_instanceName, 'view', array("id"=>$id)), htmlChars($this->_trd->selectTXT($this->_tbl_news, 'title', $id)));
		}

		$textCut = $full ? $text : $this->printSummary($text);
		
		$htmlarticle = new htmlArticle(array('class'=>'public'));

		if(!empty($img)) {
			$GINO = '';
			$img_view = $this->_data_www.'/'.$this->_prefix_thumb.$img;
			$full_view = $this->_data_www.'/'.$this->_prefix_img.$img;
			if($this->_news_img_lightbox && !$full && !$this->_img_expand) $GINO .= "<a href=\"$full_view\" rel=\"lightbox\">";
			elseif($this->_img_expand) $GINO .= "<span class=\"link\" $onclick_exp>";
			if($full)
				$GINO .= "<img class=\"left\" style=\"margin:0 5px 0px 0\" src=\"$full_view\" alt=\""._("immagine news")."\" />\n";
			else
				$GINO .= "<img class=\"left\" style=\"margin:0 5px 0px 0\" src=\"$img_view\" alt=\""._("immagine news")."\" />\n";
			if($this->_news_img_lightbox && !$full && !$this->_img_expand) $GINO .= "</a>";
			elseif($this->_img_expand) $GINO .= "</span>";
			$GINO .= "<div><span class=\"newsTitle\">$title</span></div>";
			$GINO .= "<div id=\"n".$id."\">\n";
			$GINO .= "$textCut\n";
			$GINO .= "</div>\n";
			$GINO .= "<div class=\"null\"></div>\n";
		}
		else {
			$GINO = "<div><span class=\"newsTitle\">$title</span></div>";
			$GINO .= "<div id=\"n".$id."\">$textCut</div>\n";
		}
		$GINO .= "<div id=\"fullNews$id\" style=\"display:none\">$text</div>";
		$GINO .= "<div id=\"cutNews$id\" style=\"display:none\">$textCut</div>";
		$GINO .= "<div class=\"newsSeparator\"></div>";

		$htmlarticle->content = $GINO;

		if($full) {
			$htmlsection->content = $htmlarticle->render();
			return $htmlsection->render();
		}
		else
			return $htmlarticle->render();
	}

	/**
	 * Metodo di output della singola news
	 * 
	 * @access public
	 * @return string
	 */
	public function view() {

		$id = cleanVar($_GET, 'id', 'int', '');
		$layer = cleanVar($_GET, 'layer', 'int', '');

		$query = "SELECT id, ctg, img, filename, ".$this->_field_date.", social FROM ".$this->_tbl_news." WHERE id='$id' AND published='1'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach($a AS $b)
			{
				$id = htmlChars($b['id']);
				$ctg = htmlChars($b['ctg']);
				$img = htmlChars($b['img']);
				$filename = htmlChars($b['filename']);
				$social = htmlChars($b['social']);
				$date = dbDatetimeToDate($b['date'], "/");
				
				$title = htmlChars($this->_trd->selectTXT($this->_tbl_news, 'title', $id));
				$text = htmlChars($this->_trd->selectTXT($this->_tbl_news, 'text', $id));
				
				$ctgObj = new category($ctg, $this->_tbl_ctg, $this->_instance);

				if($this->_view_ctg) 
					$subtitle = _("Pubblicata il ").$date._(" nella categoria ")."<span class=\"newsCtg\"><a href=\"".$this->_plink->aLink($this->_instanceName, 'viewList', array("ctg"=>$ctgObj->id))."\">".htmlChars($ctgObj->name)."</a></span>";
				else 
					$subtitle = _("Pubblicata il ").$date;

				$htmlsection = new htmlSection(array('class'=>'public', 'id'=>'view_news_'.$this->_instanceName, 'headerLabel'=>$title, 'subHeaderLabel'=>$subtitle));
				
				$link = $filename?"<p><a href=\"".$this->_plink->aLink($this->_instanceName, 'downloader', array("id"=>$id))."\">$filename</a></p>":"";

				$buffer = '';
				if(!empty($img))
				{
					$img_view = $this->_data_www.'/'.$this->_prefix_thumb.$img;
					$full_view = $this->_data_www.'/'.$this->_prefix_img.$img;
					if($this->_news_img_lightbox) $buffer .= "<a href=\"$full_view\" rel=\"lightbox\">";
					$buffer .= "<img style=\"float:left;margin:0 5px 0px 0\" src=\"".($this->_news_img_lightbox ? $img_view : $full_view)."\" alt=\""._("immagine news")."\" />\n";
					if($this->_news_img_lightbox) $buffer .= "</a>";
					$buffer .= "<div><span class=\"newsTitle\" style=\"text-decoration:none;\">$title</span></div>";
					$buffer .= $text.$link;
					$buffer .= "<div class=\"null\"></div>\n";
				}
				else {
					$buffer .= "<div><span class=\"newsTitle\" style=\"text-decoration:none;\">$title</span></div>";
					$buffer .= $text.$link;
				}
				if($social=='yes' && !$layer) {
					$buffer .= shareAll("all", $this->_url_root.SITE_WWW."/".$this->_plink->aLink($this->_instanceName, 'view', array("id"=>$id)), $title);
				}

				$htmlsection->content = $buffer;
				$GINO = $htmlsection->render();
			}
		}
		else
			header("Location $this->_home");

		return $GINO;
	}
	
	/**
	 * Metodo di output "elenco news"
	 * 
	 * Restituisce l'elenco paginato di tutte le news pubblicate.
	 * Include un form di ricerca se abilitato dalle opzioni.
	 * 
	 * @access public
	 * @return string
	 */
	public function viewList(){
	
		$this->accessType($this->_access_base);
		
		$ctg = cleanVar($_REQUEST, 'ctg', 'int', '');

		if($this->_search_form)
		{
			$where = '';
			$date_search = '';
			
			$month = cleanVar($_REQUEST, 'month', 'string', '');
			$year = cleanVar($_REQUEST, 'year', 'int', '');
			
			if(!empty($year))	$date_search .= "$year";
			if(!empty($month)) $date_search .= "-$month-";
			if($date_search) $where = "AND date LIKE '%$date_search%'";
			if($ctg) $where .= " AND ctg='$ctg'";
			
			$search = $this->searchNews($month, $year, $ctg);
			$link_search = "&month=$month&year=$year&ctg=$ctg";
		}
		else
		{
			$where = $ctg ? " AND ctg='$ctg'":"";
		       	$search = ''; $link_search = '';
		}

		$GINO = $this->scriptAsset("news_".$this->_instanceName.".css", "newsCSS".$this->_instance, 'css');

		$htmlsection = new htmlSection(array('id'=>"news_".$this->_instanceName,'class'=>'public', 'headerTag'=>'header', 'headerLabel'=>$this->_title_page));
		
		if($this->_feed_rss)
			$htmlsection->headerLinks = "<a href=\"".$this->_plink->aLink($this->_instanceName, 'feedRSS')."\">".pub::icon('feed')."</a>";
		
		$GINO .= $search;
		
		$numberTotRecord = $this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, $this->_user_group, $this->_group_3)
			? "SELECT id FROM ".$this->_tbl_news." WHERE instance='$this->_instance' AND published='1' $where"
			: "SELECT id FROM ".$this->_tbl_news." WHERE instance='$this->_instance' AND published='1' $where AND private='no'";
		$this->_list = new PageList($this->_news_for_page, $numberTotRecord, 'query', array("permalink_primary"=>true));
		
		$limit = $this->_db->limit($this->_list->rangeNumber, $this->_list->start());
		$query = $this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, $this->_user_group, $this->_group_3)
			? "SELECT id, ctg, img, filename, ".$this->_field_date.", social FROM ".$this->_tbl_news." WHERE instance='$this->_instance' AND published='1' $where ORDER BY date DESC $limit"
			: "SELECT id, ctg, img, filename, ".$this->_field_date.", social FROM ".$this->_tbl_news." WHERE instance='$this->_instance' AND private='no' AND published='1' $where ORDER BY date DESC $limit";
		$a = $this->_db->selectquery($query);

		if(sizeof($a) > 0)
		{
			foreach($a AS $b)
			{
				$id = htmlChars($b['id']);
				$ctg = htmlChars($b['ctg']);
				$img = htmlChars($b['img']);
				$filename = htmlChars($b['filename']);
				$social = htmlChars($b['social']);
				$date = dbDatetimeToDate($b['date'], "/");
				
				$title = htmlChars($this->_trd->selectTXT($this->_tbl_news, 'title', $id));
				$text = htmlChars($this->_trd->selectTXT($this->_tbl_news, 'text', $id));
				
				$ctgObj = new category($ctg, $this->_tbl_ctg, $this->_instance);

				if($this->_view_ctg) 
					$title_art = "<span class=\"newsCtg\"><a href=\"".$this->_plink->aLink($this->_instanceName, 'viewList', array("ctg"=>$ctgObj->id))."\">".htmlChars($ctgObj->name)."</a></span>";
				else 
					$title_art = null;

				$htmlarticle = new htmlArticle(array('class'=>'public', 'headerLabel'=>$title_art));
				
				$link = $filename?"<p><a href=\"".$this->_plink->aLink($this->_instanceName, 'downloader', array("id"=>$id))."\">$filename</a></p>":"";

				$buffer = '';
				if(!empty($img))
				{
					$img_view = $this->_data_www.'/'.$this->_prefix_thumb.$img;
					$full_view = $this->_data_www.'/'.$this->_prefix_img.$img;
					if($this->_news_img_lightbox) $buffer .= "<a href=\"$full_view\" rel=\"lightbox\">";
					$buffer .= "<img style=\"float:left;margin:0 5px 0px 0\" src=\"$img_view\" alt=\""._("immagine news")."\" />\n";
					if($this->_news_img_lightbox) $buffer .= "</a>";
					$buffer .= "<div><span class=\"newsTitle\" style=\"text-decoration:none;\">$title</span><br/>";
					$buffer .= "<span class=\"newsDate\">pubblicata il $date</span></div>";
					$buffer .= $text.$link;
					$buffer .= "<div class=\"null\"></div>\n";
				}
				else {
					$buffer .= "<div><span class=\"newsTitle\" style=\"text-decoration:none;\">$title</span><br/>";
					$buffer .= "<span class=\"newsDate\">pubblicata il $date</span></div>";
					$buffer .= $text.$link;
				}
				if($social=='yes') {
					$buffer .= shareAll("all", $this->_url_root.SITE_WWW."/".$this->_plink->aLink($this->_instanceName, 'view', array("id"=>$id)), htmlChars($title));
				}

				$buffer .= "<div class=\"newsSeparator\"></div>";

				$htmlarticle->content = $buffer;
				$GINO .= $htmlarticle->render();
			}

			$htmlsection->footer = $this->_list->listReferenceGINO($this->_plink->aLink($this->_instanceName, 'viewList', $link_search, null, array("basename"=>false)));
		}
		else
		{
			$GINO .= "<p>"._("non risultano news registrate.")."</p>";
		}
		
		$htmlsection->content = $GINO;

		return $htmlsection->render();
	}
	
	/**
	 * Form di ricerca news 
	 * 
	 * Restituisce il form di ricerca news con indicazione della ricerca precedente. 
	 * 
	 * @param int $month mese ricercato
	 * @param int $year anno ricercato
	 * @param int $ctg id della categoria ricercata
	 * @access private
	 * @return string
	 */
	private function searchNews($month, $year, $ctg){
		
		$gform = new Form('gform', 'post', false);
		$ctgObj = new category(null, $this->_tbl_ctg, $this->_instance);

		$GINO = "<div style=\"margin-bottom:10px;\">\n";
		$GINO .= "<form action=\"".$this->_plink->aLink($this->_instanceName, 'viewList')."\" method=\"post\">\n";
		
		$GINO .= "<table class=\"generic\" style=\"text-align:center;\">\n";
		$GINO .= "<tr>\n";
		$GINO .= "<th style=\"text-align:center;\">"._("Ricerca")."</th>\n";
		$GINO .= "</tr>\n";
		$GINO .= "<tr>\n";
		$GINO .= "<td>\n";
		if($this->_view_ctg) {
			$GINO .= "<label for=\"ctg\"><b>"._("categoria")."</b></label>&nbsp;&nbsp;";
			$GINO .= $gform->select('ctg', $ctg, $ctgObj->inputTreeArray("SELECT id FROM $this->_tbl_ctg WHERE id NOT IN (SELECT parent FROM $this->_tbl_ctg)"), array()); 
		}
		$GINO .= "<label for=\"month\"><b>"._("mese")."</b></label>&nbsp;&nbsp;";
		$GINO .= "<select name=\"month\">\n";
		$GINO .= "<option></option>\n";
		for($i=1;$i<13;$i++)
		{
			$m = ($i<10)?"0".$i:$i;
			$GINO .= ($m==$month)?"<option selected=\"selected\">":"<option>";
			$GINO .= $m;
			$GINO .= "</option>";
		}
		$GINO .= "</select>\n";
		
		$GINO .= "<label for=\"year\"><b>"._("anno")."</b></label>&nbsp;&nbsp;";
		$begin_year = 2007;
		$last_year = date('Y');
		$GINO .= "<select name=\"year\">\n";
		$GINO .= "<option></option>\n";
		for($i=$begin_year;$i<$last_year+1;$i++) {
			$GINO .= ($i==$year)?"<option selected=\"selected\">":"<option>";
			$GINO .= "$i</option>";
		}
		$GINO .= "</select>&nbsp;&nbsp;\n";
		
		$GINO .= "<input type=\"submit\"name=\"submit_insert\" class=\"submit\" value=\""._("cerca")."\" />";
		
		$GINO .= "</td>\n";
		$GINO .= "</tr>\n";
		$GINO .= "</table>\n";
		$GINO .= "</form>\n";
		$GINO .= "</div>\n";
		
		return $GINO;
	}
	
	/**
	 * Entry point per il backoffice 
	 * 
	 * Questo metodo fa da wrapper per tutte le funzionalità amministrative.
	 * 
	 * @access public
	 * @return string
	 */
	public function manageDoc(){

		$this->accessGroup('ALL');

		$htmltab = new htmlTab(array("linkPosition"=>'right', "title"=>$this->_instanceLabel));	
		$link_admin = "<a href=\"".$this->_home."?evt[$this->_instanceName-manageDoc]&block=permissions\">"._("Permessi")."</a>";
		$link_css = "<a href=\"".$this->_home."?evt[$this->_instanceName-manageDoc]&block=css\">"._("CSS")."</a>";
		$link_options = "<a href=\"".$this->_home."?evt[$this->_instanceName-manageDoc]&block=options\">"._("Opzioni")."</a>";
		$link_ctg = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&block=ctg\">"._("Categorie")."</a>";
		$link_dft = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]\">"._("Contenuti")."</a>";
		$sel_link = $link_dft;

		// Variables
		$id = cleanVar($_GET, 'id', 'int', '');
		$ctg_id = cleanVar($_GET, 'ctg_id', 'int', '');
		$start = cleanVar($_GET, 'start', 'int', '');
		// end

		if($this->_block == 'css') {
			$GINO = sysfunc::manageCss($this->_instance, $this->_className);		
			$sel_link = $link_css;
		}
		elseif($this->_block == 'permissions' && $this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, '', '')) {
			$GINO = sysfunc::managePermissions($this->_instance, $this->_className);		
			$sel_link = $link_admin;
		}
		elseif($this->_block == 'options') {
			$GINO = sysfunc::manageOptions($this->_instance, $this->_className);		
			$sel_link = $link_options;
		}
		elseif($this->_block == 'ctg') {
			$GINO = $this->manageCtg($ctg_id);	
			$sel_link = $link_ctg;
		}
		else {
			$GINO = $this->manageItem($id);
		}

		if($this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, '', '')) $links_array = array($link_admin, $link_css, $link_options, $link_ctg, $link_dft);
		elseif($this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, $this->_user_group, $this->_group_1)) $links_array = array($link_css, $link_options, $link_ctg, $link_dft);
		else $links_array = array($link_ctg, $link_dft);

		$htmltab->navigationLinks = $links_array;
		$htmltab->selectedLink = $sel_link;
		$htmltab->htmlContent = $GINO;
		return $htmltab->render();
	}
	
	/**
	 * Gestione amministrativa dei contenuti delle news 
	 * 
	 * @param mixed $id 
	 * @access private
	 * @return string
	 */
	private function manageItem($id) {
		
		$start = cleanVar($_POST, 'start', 'string', '');

		if($this->_action == $this->_act_modify OR $this->_action == $this->_act_insert)
			$GINO = $this->formNews($id, $start);
		elseif($this->_action == $this->_act_view) { echo $this->displayNews(null, false); exit(); }
		else $GINO = $this->listNews();


		return $GINO;

	}

	/**
	 * Gestione amministrativa delle categorie 
	 * 
	 * @param mixed $ctg_id id della categoria 
	 * @access private
	 * @return string
	 */
	private function manageCtg($ctg_id) {
	
		$ctg = new category($ctg_id, $this->_tbl_ctg, $this->_instance);

		if($this->_action == $this->_act_insert) {
			$newctg = new category(null, $this->_tbl_ctg, $this->_instance);
			$form = $newctg->formCtg($this->_home."?evt[$this->_instanceName-actionCtg]", array("title"=>_("Nuova categoria"), "parent"=>$ctg->id));
		}
		elseif($this->_action == $this->_act_modify)
			$form = $ctg->formCtg($this->_home."?evt[$this->_instanceName-actionCtg]");
		elseif($this->_action == $this->_act_delete)
			$form = $ctg->formDelCtg($this->_home."?evt[$this->_instanceName-actionDelCtg]", array("more_info"=>_("L'eliminazione delle categorie <b>NON</b> comporta l'eliminazione delle news contenute.")));
		else
			$form = $this->infoCtg();

		$GINO = "<div class=\"vertical_1\">\n";
		$GINO .= $this->listCategories($ctg_id);
		$GINO .= "</div>\n";

		$GINO .= "<div class=\"vertical_2\">\n";
		$GINO .= $form;
		$GINO .= "</div>\n";

		$GINO .= "<div class=\"null\"></div>";

		return $GINO;

	}

	/**
	 * Stampa le informazioni sulla categorizzazione delle news 
	 * 
	 * @access private
	 * @return string
	 */
	private function infoCtg(){

		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Informazioni")));
		$buffer = "<p>"._("Le news possono disporre di una categorizzazione ad albero infinito. Una nuova news può essere associata solamente ad una categoria di tipo <b>foglia</b> (<span class=\"link tooltipfull\" title=\"Categoria di tipo foglia::Si tratta di una categoria che non ha altre categorie sotto di se.\">?</span>)")."</p>\n";
		$buffer .= "<p>"._("Nel caso in cui una categoria foglia cessi di esserlo è opportuno modificare l'associazione delle news con questa categoria di modo che le nuovi associazioni siano verso categorie di tipo foglia.")."</p>";
		
		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}

	/**
	 * Elenco amministrativo delle categorie 
	 * 
	 * @param mixed $ctg_id id della categoria selezionata
	 * @access private
	 * @return string
	 */
	private function listCategories($ctg_id) {

		$link_insert = "<a href=\"$this->_home?evt[$this->_instanceName-manageDoc]&block=ctg&action=$this->_act_insert\">".pub::icon('insert', _("nuova categoria"))."</a>";
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLabel'=>_("Categorie"), 'headerLinks'=>$link_insert));

		$ctg = new category($ctg_id, $this->_tbl_ctg, $this->_instance);	

		$GINO = $ctg->printTree(0, $this->_home."?evt[$this->_instanceName-manageDoc]&block=ctg&", array("view"=>false));
		
		$htmlsection->content = $GINO;
		
		return $htmlsection->render();
	}

	/**
	 * Elenco amministrativo delle news registrate 
	 * 
	 * @access private
	 * @return string
	 */
	private function listNews() {
	
		$gform = new Form('gform', 'post', true);

		$filterCtg = cleanVar($_GET, 'filterCtg', 'int', '');
		$order = cleanVar($_GET, 'order', 'string', '');
		if(!$order || $order=='date') $order = 'date DESC';

		$ctgObj = new category(null, $this->_tbl_ctg, $this->_instance);
		$gform = new Form('gform', 'post', true);

		$onchange = "onchange=\"location.href='$this->_home?evt[$this->_instanceName-manageDoc]&filterCtg='+$(this).value\"";
		$filter = $gform->select('filterCtg', $filterCtg, $ctgObj->selectParentArray(), 
			array("noFirst"=>true, "firstValue"=>"", "firstVoice"=>_("tutte le categorie"), "js"=>$onchange));
		$link_insert = "<a href=\"$this->_home?evt[$this->_instanceName-manageDoc]&action=$this->_act_insert\">".pub::icon('insert')."</a>";
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLabel'=>_("Elenco"), 'headerLinks'=>array($filter, $link_insert)));

		if($filterCtg) {
			$where_f = array("ctg='$filterCtg'");
			$fCtg = new category($filterCtg, $this->_tbl_ctg, $this->_instance);
			foreach($fCtg->getChildren() as $k=>$v) {
				$where_f[] = "ctg='$k'";
			}
			$where_f = " AND (".implode(" OR ", $where_f).")";
		}
		else $where_f = '';

		$link_insert = "<a href=\"$this->_home?evt[$this->_instanceName-manageDoc]&action=$this->_act_insert\">".pub::icon('insert')."</a>";
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLabel'=>_("Elenco"), 'headerLinks'=>array($filter, $link_insert)));

		$numberTotRecord = "SELECT id FROM ".$this->_tbl_news." WHERE instance='$this->_instance' $where_f";
		$this->_list = new PageList($this->_news_for_page, $numberTotRecord, 'query');
		
		$start = $this->_list->start();
		$limit = $this->_db->limit($this->_list->rangeNumber, $start);
		$query = "SELECT id, ctg, img, title, text, filename, ".$this->_field_date.", social, private, published FROM ".$this->_tbl_news." WHERE instance='$this->_instance' $where_f ORDER BY $order $limit";
		$a = $this->_db->selectquery($query);

		$GINO = "<p style=\"text-align:right\">"; 
		$GINO .= "</p>"; 

		$link = $this->_home."?evt[$this->_instanceName-manageDoc]";

		$GINO .= "<table class=\"generic\">";
		$GINO .= "<tr>";
		$GINO .= "<th>"._("Id")."</th>";
		$GINO .= "<th><a href=\"$link&order=date\">"._("Data")."</a></th>";
		$GINO .= "<th>"._("Categoria")."</th>";
		$GINO .= "<th><a href=\"$link&order=title\">"._("Titolo")."</a></th>";
		$GINO .= "<th>"._("Immagine")."</th>";
		$GINO .= "<th>"._("File")."</th>";
		$GINO .= "<th>"._("Social")."</th>";
		$GINO .= "<th>"._("Privata")."</th>";
		$GINO .= "<th><a href=\"$link&order=published\">"._("Pubblicata")."</a></th>";
		$GINO .= "<th class=\"thIcon\"></th>";
		$GINO .= "</tr>";
		if(sizeof($a) > 0) {
			foreach($a as $b) {
				$id = $b['id'];
				$link_modify = "<a href=\"index.php?evt[".$this->_instanceName."-manageDoc]&amp;id=$id&amp;start=$start&amp;order=$order&amp;action=".$this->_act_modify.($filterCtg?"&amp;filterCtg=$filterCtg":"")."\">".$this->icon('modify', '')."</a>";
				$link_delete = "<span class=\"link\" onclick=\"if(confirmSubmit('"._("Sicuro di voler procedere con l\'eliminazione")."')) location.href='$this->_home?evt[".$this->_instanceName."-actionDelNews]&amp;id=$id&amp;start=$start&amp;order=$order".($filterCtg?"&amp;filterCtg=$filterCtg":"")."'\">".$this->icon('delete', '')."</span>";
				$url = $this->_home."?pt[$this->_instanceName-manageDoc]&id=$id&action=view";
				$link_view = "<span class=\"link\" onclick=\"window.myWin = new layerWindow({'title':'"._("Preview news")."', 'url':'$url', 'bodyId':'prew_news$id', 'width':400});window.myWin.display();\">".$this->icon('view', '')."</span>";

				if($this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, $this->_user_group, $this->_group_1)) $links = array($link_modify, $link_delete, $link_view);
				else $links = array($link_modify, $link_view);

				$ctgObj = new category($b['ctg'], $this->_tbl_ctg, $this->_instance);
				$GINO .= "<tr>";
				$GINO .= "<td>".$id."</td>";
				$GINO .= "<td>".dbDatetimeToDate($b['date'], "/")." ".dbDatetimeToTime($b['date'])."</td>";
				$GINO .= "<td>".$ctgObj->completeName()."</td>";
				$GINO .= "<td>".htmlChars($b['title'])."</td>";
				$GINO .= "<td>".($b['img'] ? "<span onclick=\"Slimbox.open('$this->_data_www/$this->_prefix_img{$b['img']}')\" class=\"link\">".$b['img']."</span>" : "")."</td>";
				$GINO .= "<td>".htmlChars($b['filename'])."</td>";
				$GINO .= "<td>".($b['social']=='yes' ? _("si") : _("no"))."</td>";
				$GINO .= "<td>".($b['private']=='yes' ? _("si") : _("no"))."</td>";
				if($this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, $this->_user_group, $this->_group_1)) {
					$GINO .= "<td>";
					$onchange = "onclick=\"ajaxRequest('post', '$this->_home?pt[$this->_instanceName-changePublished]', 'id=$id&published='+$(this).value, 'response_$id', {'load':'response_$id', 'script':true});\"";
					$GINO .= $gform->radio('published_'.$id, $b['published'], array("1"=>_("si"), "0"=>_("no")), '0', array("js"=>$onchange));		
					$GINO .= "<span id=\"response_$id\"></span></td>";
				}
				else $GINO .= "<td>".($b['published'] ? _("si") : _("no"))."</td>";
				$GINO .= "<td class=\"tdIcon\">".implode(" ", $links)."</td>";
				$GINO .= "</tr>";
			}
		}
		$GINO .= "</table>";

		if(!sizeof($a)) 
			$GINO .= "<p>"._("Non risultano elementi registrati")."</p>\n";

		$htmlsection->content = $GINO;
		
		$htmlsection->footer = "<p>".$this->_list->listReferenceGINO("evt[".$this->_instanceName."-manageDoc]&order=$order".($filterCtg?"&filterCtg=$filterCtg":""))."</p>";

		return $htmlsection->render();

	}

	/**
	 * Modifica dello stato di pubblicazione di una news 
	 * 
	 * @access public
	 * @return string
	 */
	public function changePublished() {
	
		$this->accessGroup($this->_group_1);

		$id = cleanVar($_POST, 'id', 'int', '');
		$published = cleanVar($_POST, 'published', 'int', '');

		$query = "UPDATE $this->_tbl_news SET published='$published' WHERE id='$id'";
		$result = $this->_db->actionquery($query);

		return "<script>alert('"._("modifica avvenuta con successo")."');</script>";
	}

	/**
	 * Form di inserimento e modifica delle news 
	 * 
	 * @param mixed $news_id id della news da modificare
	 * @param mixed $start indicatore del numero di pagina della lista news da cui si arriva
	 * @access private
	 * @return string
	 */
	private function formNews($news_id, $start){

		$filterCtg = cleanVar($_GET, 'filterCtg', 'int', '');

		$ctgObj = new category(null, $this->_tbl_ctg, $this->_instance);

		$this->_gform = new Form('gform', 'post', true, array("trnsl_table"=>$this->_tbl_news, "trnsl_id"=>$news_id));
		$this->_gform->load('dataform');
		
		if(!empty($news_id) AND $this->_action == $this->_act_modify)
		{
			$query = "SELECT ctg, title, text, img, filename, ".$this->_field_date.", private, social, published FROM ".$this->_tbl_news." WHERE id='$news_id'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				foreach($a AS $b)
				{
					$ctg = htmlInput($b['ctg']);
					$title = htmlInput($b['title']);
					$text = htmlInputEditor($b['text']);
					$media = htmlInput($b['img']);
					$attach = htmlInput($b['filename']);
					$datetime = $b['date'];
					$datetime_array = explode(" ", $datetime);
					$date = dbDateToDate($datetime_array[0], "/");
					$private = htmlInput($b['private']);
					$social = htmlInput($b['social']);
					$published = htmlInput($b['published']);
				}

				$title_form = _("Modifica news");
				$submit = _("modifica");
			}
		}
		else
		{
			$media = $attach = '';
			$date = '';
			$ctg = $this->_gform->retvar('ctg', '');
			$private = $this->_gform->retvar('private', '');
			$social = $this->_gform->retvar('social', '');
			$published = $this->_gform->retvar('published', '');
			$title = $this->_gform->retvar('title', '');
			$text = $this->_gform->retvar('text', '');
			$title_form = _("Nuova news");
			$submit = _("inserisci");
		}
		$required = 'date,title,text';
		
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>$title_form));

		$GINO = $this->_gform->form($this->_home."?evt[".$this->_instanceName."-actionNews]".($filterCtg?"&filterCtg=$filterCtg":""), true, $required);
		$GINO .= $this->_gform->hidden('reference', $news_id);
		$GINO .= $this->_gform->hidden('action', $this->_action);
		$GINO .= $this->_gform->hidden('start', $start);
		
		$GINO .= $this->_gform->cradio('private', $private, array("yes"=>_("si"),"no"=>_("no")), 'no', array(_("News privata"), _("se la news viene impostata come privata potrà essere visualizzata solamente da persone iscritte alle news")), array("required"=>true));
		$ctg_et = $ctgObj->inputTreeArray("SELECT id FROM ".$this->_tbl_ctg." WHERE id NOT IN (SELECT parent FROM ".$this->_tbl_ctg.")");
		$GINO .= $this->_gform->cselect('ctg', $ctg, $ctg_et, _("Categoria"), array());
		$GINO .= $this->_gform->cinput_date('date', $date, _("Data"), array('required'=>true, 'inputClickEvent'=>false));
		$GINO .= $this->_gform->cinput('title', 'text', $title, _("Titolo"), array("size"=>40, "maxlength"=>200, "required"=>true, "trnsl"=>true, "field"=>"title"));
		$GINO .= $this->_gform->fcktextarea('text', $text, _("Testo"), array("required"=>true, "notes"=>true, "img_preview"=>false, "trnsl"=>true, "field"=>"text"));
		$img_view = $this->_data_www.'/'.$this->_prefix_img."$media";
		$GINO .= $this->_gform->cfile('media', $media, _("Media"), array("extensions"=>$this->_extension_media, "del_check"=>true, "preview"=>true, "previewSrc"=>$img_view));
		$GINO .= $this->_gform->cfile('filename', $attach, _("File allegato"), array("extensions"=>$this->_extension_attach, "del_check"=>true));
		$GINO .= $this->_gform->cradio('social', $social, array("yes"=>_("si"),"no"=>_("no")), 'no', _("Attiva condivisione social networks"), array("required"=>true));
		if($this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, $this->_user_group, $this->_group_1))
			$GINO .= $this->_gform->cradio('published', $published, array("1"=>_("si"),"0"=>_("no")), '0', _("Pubblica"), array("required"=>true));
		$GINO .= $this->_gform->cinput('submit_form_news', 'submit', $submit, '', array('classField'=>'submit'));
		
		$GINO .= $this->_gform->cform();
		
		$htmlsection->content = $GINO;
		
		return $htmlsection->render();
	}
	
	/**
	 * Azione di salvataggio su database dei contenuti inseriti nel form nuova/modifica news 
	 * 
	 * @access public
	 * @return void
	 */
	public function actionNews(){

		$this->accessGroup($this->_group_2);
		
		$this->_gform = new Form('gform','post', true);
		$this->_gform->save('dataform');
		$req_error = $this->_gform->arequired();
		
		$filterCtg = cleanVar($_GET, 'filterCtg', 'int', '');

		$reference = cleanVar($_POST, 'reference', 'int', '');
		$action = cleanVar($_POST, 'action', 'string', '');
		$start = cleanVar($_POST, 'start', 'int', '');
		$date = cleanVar($_POST, 'date', 'string', '');
		// Input file
		$media_name = $_FILES['media']['name'];
		$media_tmp = $_FILES['media']['tmp_name'];
		$file_name = $_FILES['filename']['name'];
		$file_tmp = $_FILES['filename']['tmp_name'];
		
		$old_media = cleanVar($_POST, 'old_media', 'string', '');
		$old_file = cleanVar($_POST, 'old_filename', 'string', '');
		// End
				
		$link = "start=$start".($filterCtg?"&filterCtg=$filterCtg":"");
		$link_error = $this->_home."?evt[$this->_instanceName-manageDoc]&action=$action&id=$reference&start=$start".($filterCtg?"&filterCtg=$filterCtg":"");
		$link_error_file = $this->_home."?evt[$this->_instanceName-manageDoc]";

		if($req_error > 0) 
			exit(error::errorMessage(array('error'=>1), $link_error));
		
		$hour = date("H:i:s");
		$datetime = dateToDbDate($date, '/')." ".$hour;

		$directory = $this->_data_dir.$this->_os;
		$redirect = $this->_instanceName.'-manageDoc';
		
		$values = array(
			"ctg"=>cleanVar($_POST, 'ctg', 'int', ''),
			"date"=>$datetime,
			"title"=>cleanVar($_POST, 'title', 'string', ''),
			"text"=>cleanVarEditor($_POST, 'text', ''),
			"private"=>cleanVar($_POST, 'private', 'string', ''),
			"social"=>cleanVar($_POST, 'social', 'string', '')
		);
		if($this->_action==$this->_act_insert) $values['instance'] = $this->_instance;
		if($this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, $this->_user_group, $this->_group_1)) 
			$values['published'] = cleanVar($_POST, 'published', 'string', '');


		if(!empty($reference) && $action == $this->_act_modify) {
			$query = "UPDATE ".$this->_tbl_news." SET ";
			foreach($values as $k=>$v) $query .= "$k='$v', ";
			$query = substr($query, 0, -2)." WHERE id='$reference'";
		}
		elseif(empty($reference) && $action == $this->_act_insert) {	// insert

			$fields = '(';
			$qv = '(';
			foreach($values as $k=>$v) { $fields .= "$k,"; $qv .= "'$v',"; }
			$fields = substr($fields, 0, -1).")";
			$qv = substr($qv, 0, -1).")";
			$query = "INSERT INTO ".$this->_tbl_news." $fields VALUES $qv";
		}
		
		if(!$this->_db->actionquery($query)) exit(error::errorMessage(array('error'=>_("Impossibile salvare i dati inseriti")), $link_error));

		$rid = $reference?$reference:$this->_db->getlastid();

		$this->_gform->manageFile('media', $old_media, true, $this->_extension_media, $directory, $link_error_file, $this->_tbl_news, 'img', 'id', $rid, 
			array("prefix_file"=>$this->_prefix_img, "prefix_thumb"=>$this->_prefix_thumb, "width"=>$this->_width_img, "thumb_width"=>$this->_width_thumb));
		$this->_gform->manageFile('filename', $old_file, false, $this->_extension_attach, $directory, $link_error_file, $this->_tbl_news, 'filename', 'id', $rid);
		
		EvtHandler::HttpCall($this->_home, $this->_instanceName.'-manageDoc', $link);
	}
	
	/**
	 * Azione di inserimento/modifica categoria 
	 * 
	 * @access public
	 * @return void
	 */
	public function actionCtg() {

		$ctg_id = cleanVar($_POST, 'ctg_id', 'int', '');

		$ctg = new category($ctg_id, $this->_tbl_ctg, $this->_instance);

		$result = $ctg->actionCtg($this->_home."?evt[$this->_instanceName-manageDoc]&block=ctg".($ctg->id
			?"&action=$this->_act_modify&ctg_id=$ctg->id"
			:"&action=$this->_act_insert"));

		EvtHandler::HttpCall($this->_home, $this->_instanceName.'-manageDoc', 'block=ctg');
	}

	/**
	 * Azione di eliminazione di una categoria 
	 * 
	 * @access public
	 * @return void
	 */
	public function actionDelCtg() {

		$ctg_id = cleanVar($_POST, 'ctg_id', 'int', '');

		$ctg = new category($ctg_id, $this->_tbl_ctg, $this->_instance);

		$result = $ctg->actionDelCtg($this->_home."?evt[$this->_instanceName-manageDoc]&block=ctg&action=$this->_act_delete&ctg_id=$ctg->id");

		EvtHandler::HttpCall($this->_home, $this->_instanceName.'-manageDoc', 'block=ctg');
	}

	/**
	 * Azione di eliminazione di una news 
	 * 
	 * @access public
	 * @return void
	 */
	public function actionDelNews() {

		$id = cleanVar($_GET, 'id', 'int', '');
		$start = cleanVar($_GET, 'start', 'int', '');
		$order = cleanVar($_GET, 'order', 'string', '');
		$filterCtg = cleanVar($_GET, 'filterCtg', 'int', '');

		$query = "SELECT img, filename FROM ".$this->_tbl_news." WHERE id='$id'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0) {
			$img = $a[0]['img'];
			$filename = $a[0]['filename'];
						
			if(!empty($img)) {
				@unlink($this->_data_dir.$this->_os.$this->_prefix_img.$img);
				@unlink($this->_data_dir.$this->_os.$this->_prefix_thumb.$img);
			}
			if(!empty($filename)) {
				@unlink($this->_data_dir.$this->_os.$filename);
			}
		}

		language::deleteTranslations($this->_tbl_news, $id);
				
		$query = "DELETE FROM ".$this->_tbl_news." WHERE id='$id'";
		$this->_db->actionquery($query);

		EvtHandler::HttpCall($this->_home, $this->_instanceName.'-manageDoc', "order=$order&start=$start".($filterCtg?"&filterCtg=$filterCtg":""));

	}

	/**
	 * Converte il formato datetime del databse in un formato tipo data gg/mm/aaaa 
	 * 
	 * @param string $datetime stringa tipo datetime
	 * @access private
	 * @return void
	 */
	private function shortDate($datetime) {
		
		$datetime_array = explode(" ", $datetime);
		$date = $datetime_array[0];
		$date_array = explode("-",$date);
		
		return $date_array[2]."/".$date_array[1]."/".substr($date_array[0],2,2);
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
	
		return array("table"=>"news", "selected_fields"=>array("id", "date", array("highlight"=>true, "field"=>"title"), array("highlight"=>true, "field"=>"text")), "required_clauses"=>array("instance"=>$this->_instance), "weight_clauses"=>array("title"=>array("weight"=>3), "text"=>array("weight"=>1)));	
	
	}

	/**
	 * Definisce la presentazione del singolo item trovato a seguito di ricerca (modulo "Ricerca nel sito") 
	 * 
	 * @param mixed array array[string]string array associativo contenente i risultati della ricerca 
	 * @access public
	 * @return void
	 */
	public function searchSiteResult($results) {
	
		$buffer = "<div>".dbDatetimeToDate($results['date'], "/")." <a href=\"$this->_home?evt[$this->_instanceName-viewList]&id=".$results['id']."\">";
		$buffer .= $results['title'] ? htmlChars($results['title']) : htmlChars($this->_db->getFieldFromId($this->_tbl_news, 'title', 'id', $results['id']));
		$buffer .= "</a></div>";
		if($results['text']) $buffer .= "<div class=\"search_text_result\">...".htmlChars($results['text'])."...</div>";
		return $buffer;
		
	}
	
	/**
	 * Genera un feed RSS standard che presenta tutte le news pubblicate 
	 * 
	 * @access public
	 * @return string xml che definisce il feed RSS
	 */
	public function feedRSS() {
		
		$this->accessType($this->_access_base);

		header("Content-type: text/xml; charset=utf-8");
		
		$function = "feedRSS";
		$title_site = pub::variable('head_title');
	        $title =  $title_site.($this->_title_page ? " - ".$this->_title_page : "");
		$description = $this->_db->getFieldFromId(TBL_MODULE, 'description', 'id', $this->_instance);
		
		$header = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
 		$header .= "<rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\">\n";
 		$header .= "<channel>\n";
 		$header .= "<atom:link href=\"".$this->_url_root.$this->_home."?pt%5B$this->_instanceName-".$function."%5D\" rel=\"self\" type=\"application/rss+xml\" />\n";
 		$header .= "<title>".$title."</title>\n";
 		$header .= "<link>".$this->_url_root.$this->_home."</link>\n";
 		$header .= "<description>".$description."</description>\n";
 		$header .= "<language>$this->_lng_nav</language>";
 		$header .= "<copyright> Copyright 2009 Otto srl </copyright>\n";
 		$header .= "<docs>http://blogs.law.harvard.edu/tech/rss</docs>\n";

		echo $header;

		$query = "SELECT id, date FROM news WHERE instance='$this->_instance' AND published='1' ORDER BY date DESC LIMIT 50";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach($a AS $b)
			{
				$id = htmlChars($b['id']);
				$title = htmlChars($this->_trd->selectTXT('news', 'title', $id));
				$text = htmlChars($this->_trd->selectTXT('news', 'text', $id));
				$text = str_replace("src=\"", "src=\"".$this->_web_address, $text);
				
				$datetime = htmlChars($b['date']);
				$datetime_array = explode(" ", $datetime);
				$date = dbDateToDate($datetime_array[0],"/");
				$time = $datetime_array[1];
				
				echo "<item>\n";
				echo "<title>".$date.". ".$title."</title>\n";
				echo "<link>".$this->_url_root.SITE_WWW."/".$this->_plink->aLink($this->_instanceName, 'view', array("id"=>$id))."</link>\n";
				echo "<description>\n";
				echo "<![CDATA[\n";
				echo $text;
				echo "]]>\n";
				echo "</description>\n";
				echo "<guid>".$this->_url_root.SITE_WWW.$this->_plink->aLink($this->_instanceName, 'view', array("id"=>$id))."</guid>\n"; 
				//echo "<pubDate>$date $time</pubDate>\n";
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
