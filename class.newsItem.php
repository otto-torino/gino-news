<?php
/**
 * \file class.newsItem.php
 * @brief Contiene la definizione ed implementazione della classe newsItem.
 * 
 * @version 2.0.1
 * @copyright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */

/**
 * \ingroup gino-news
 * Classe tipo model che rappresenta una singola news.
 *
 * @version 2.0.1
 * @copyright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class newsItem extends propertyObject {

	private $_controller;

	protected static $_extension_img = array('jpg', 'jpeg', 'png');
	protected static $_extension_attached = array('pdf', 'doc', 'xdoc', 'odt', 'xls', 'csv', 'txt');
	public static $tbl_item = 'news_item';

	/**
	 * Costruttore
	 * 
	 * @param integer $id valore ID del record
	 * @param object $instance istanza del controller
	 */
	function __construct($id, $instance) {

		$this->_controller = $instance;
		$this->_tbl_data = self::$tbl_item;

		$this->_fields_label = array(
			'insertion_date'=>_('Data inserimento'),
			'last_edit_date'=>_('Data ultima modifica'),
			'date'=>_('Data'),
			'categories'=>_("Categorie"),
			'title'=>_("Titolo"),
			'slug'=>array(_("Slug"), _('utilizzato per creare un permalink alla risorsa')),
			'text'=>_('Testo'),
			'img'=>_('Immagine'),
			'attached'=>_('Allegato'),
			'private'=>array(_('Privata'), _('le news private sono visualizzabili solamente dagli utenti appartenenti al gruppo \'iscritti\'')),
			'social'=>_('Condivisione social networks'),
			'published'=>_('Pubblicata'),
		);

		parent::__construct($id);

		$this->_model_label = $this->id ? $this->title : '';
	}

	/**
	 * Sovrascrive la struttura di default
	 * 
	 * @see propertyObject::structure()
	 * @param integer $id
	 * @return array
	 */
	public function structure($id) {
		
		$structure = parent::structure($id);

		$structure['categories'] = new manyToManyField(array(
			'name'=>'categories', 
			'value'=>explode(',', $this->categories), 
			'label'=>$this->_fields_label['categories'], 
			'lenght'=>255, 
			'fkey_table'=>newsCtg::$tbl_ctg, 
			'fkey_id'=>'id', 
			'fkey_field'=>'name', 
			'fkey_where'=>'instance=\''.$this->_controller->getInstance().'\'', 
			'fkey_order'=>'name',
			'table'=>$this->_tbl_data 
		));
		
		$structure['published'] = new booleanField(array(
			'name'=>'published', 
			'required'=>true,
			'label'=>$this->_fields_label['published'], 
			'enum'=>array(1 => _('si'), 0 => _('no')), 
			'default'=>0,
			'value'=>$this->published, 
			'table'=>$this->_tbl_data 
		));

		$structure['private'] = new booleanField(array(
			'name'=>'private', 
			'required'=>true,
			'label'=>$this->_fields_label['private'], 
			'enum'=>array(1 => _('si'), 0 => _('no')), 
			'default'=>0, 
			'value'=>$this->private, 
			'table'=>$this->_tbl_data 
		));

		$structure['social'] = new booleanField(array(
			'name'=>'social', 
			'required'=>true,
			'label'=>$this->_fields_label['social'], 
			'enum'=>array(1 => _('si'), 0 => _('no')), 
			'default'=>0, 
			'value'=>$this->social, 
			'table'=>$this->_tbl_data 
		));

		$structure['insertion_date'] = new datetimeField(array(
			'name'=>'insertion_date', 
			'required'=>true,
			'label'=>$this->_fields_label['insertion_date'], 
			'auto_now'=>false, 
			'auto_now_add'=>true, 
			'value'=>$this->insertion_date 
		));

		$structure['last_edit_date'] = new datetimeField(array(
			'name'=>'last_edit_date', 
			'required'=>true,
			'label'=>$this->_fields_label['last_edit_date'], 
			'auto_now'=>true, 
			'auto_now_add'=>true, 
			'value'=>$this->last_edit_date 
		));

		$base_path = $this->_controller->getBaseAbsPath('img');

		$structure['img'] = new imageField(array(
                        'name'=>'img', 
                        'value'=>$this->img, 
                        'label'=>$this->_fields_label['img'], 
                        'lenght'=>100, 
                        'extensions'=>self::$_extension_img, 
                        'path'=>$base_path, 
                        'resize'=>true,
			'width'=>$this->_controller->getImageWidth(),
			'thumb_width'=>$this->_controller->getThumbWidth()
                ));

		$base_path = $this->_controller->getBaseAbsPath('attached');

		$structure['attached'] = new fileField(array(
                        'name'=>'attached', 
                        'value'=>$this->attached, 
                        'label'=>$this->_fields_label['attached'], 
                        'lenght'=>100, 
                        'extensions'=>self::$_extension_attached, 
                        'path'=>$base_path,
			'check_type'=>false 
                ));

		return $structure;
	}

	/**
	 * Lista di categorie associate alla news 
	 * 
	 * @param news $controller istanza del controller
	 * @access public
	 * @return array di istanze di newsCtg
	 */
	public function categories($controller) {

		$res = array();
		foreach(explode(',', $this->categories) as $ctgid) {
			$res[] = new newsCtg($ctgid, $controller);	
		}

		return $res;

	}

	/**
	 * Restituisce l'istanza newsItem a partire dallo slug fornito 
	 * 
	 * @param string $slug lo slug 
	 * @param news $controller istanza del controller
	 * @access public
	 * @return istanza di newsItem
	 */
	public static function getFromSlug($slug, $controller) {
	
		$res = null;

		$db = db::instance();
		$rows = $db->select('id', self::$tbl_item, "slug='$slug'", null, array(0, 1));
		if(count($rows)) {
			$res = new newsItem($rows[0]['id'], $controller);
		}

		return $res;
			
	}

	/**
	 * Restituisce oggetti di tipo newsItem 
	 * 
	 * @param news $controller istanza del controller 
	 * @param array $options array associativo di opzioni 
	 * @return array di istanze di tipo newsItem
	 */
	public static function get($controller, $options = null) {

		$res = array();

		$private = gOpt('private', $options, false);
		$published = gOpt('published', $options, true);
		$order = gOpt('order', $options, 'name');
		$limit = gOpt('limit', $options, null);
		$ctg = gOpt('ctg', $options, false);

		$db = db::instance();
		$selection = 'id';
		$table = self::$tbl_item;
		$where_arr = array("instance='".$controller->getInstance()."'");
		if(!$private) {
			$where_arr[] = "private='0'";
		} 
		if($published) {
			$where_arr[] = "published='1'";
		}
		if($ctg) {
			$where_arr[] = "categories REGEXP '[[:<:]]".$ctg."[[:>:]]'";
		}
		$where = implode(' AND ', $where_arr);

		$rows = $db->select($selection, $table, $where, $order, $limit);
		if(count($rows)) {
			foreach($rows as $row) {
				$res[] = new newsItem($row['id'], $controller);
			}
		}

		return $res;

	}

	/**
	 * Restituisce il numero di news che soddisfano le condizioni date 
	 * 
	 * @param news $controller istanza del controller 
	 * @param array $options array associativo di opzioni 
	 * @return numero di news
	 */
	public static function getCount($controller, $options = null) {

		$res =0;

		$private = gOpt('private', $options, false);
		$published = gOpt('published', $options, true);
		$ctg = gOpt('ctg', $options, false);

		$db = db::instance();
		$selection = 'COUNT(id) AS tot';
		$table = self::$tbl_item;
		$where_arr = array("instance='".$controller->getInstance()."'");
		if(!$private) {
			$where_arr[] = "private='0'";
		} 
		if($published) {
			$where_arr[] = "published='1'";
		}
		if($ctg) {
			$where_arr[] = "categories REGEXP '[[:<:]]".$ctg."[[:>:]]'";
		}
		$where = implode(' AND ', $where_arr);

		$rows = $db->select($selection, $table, $where, null, null);

		if($rows && count($rows)) {
			$res = $rows[0]['tot'];
		}

		return $res;

	}

	/**
	 * Path relativo della thumb associata 
	 * 
	 * @param news $controller istanza del controller
	 * @return path relativo della thumb
	 */
	public function thumbPath($controller) {

		return $controller->getBasePath('img').'/thumb_'.$this->img;

	}

	/**
	 * Path relativo dell'immagine associata 
	 * 
	 * @param news $controller istanza del controller
	 * @return path relativo dell'immagine
	 */
	public function imgPath($controller) {

		return $controller->getBasePath('img').'/'.$this->img;

	}



}

?>
