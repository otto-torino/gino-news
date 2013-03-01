<?php
/**
 * \file class.newsCtg.php
 * @brief Contiene la definizione ed implementazione della classe newsCtg.
 * 
 * @version 2.0.1
 * @copyright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */

/**
 * \ingroup gino-news
 * Classe tipo model che rappresenta una categoria di news.
 *
 * @version 2.0.1
 * @copyright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class newsCtg extends propertyObject {

	private $_controller;

	protected static $_extension_img = array('jpg', 'jpeg', 'png');

	public static $tbl_ctg = 'news_ctg';

	/**
	 * Costruttore
	 * 
	 * @param integer $id valore ID del record
	 * @param object $instance istanza del controller
	 */
	function __construct($id, $instance) {

		$this->_controller = $instance;
		$this->_tbl_data = self::$tbl_ctg;

		$this->_fields_label = array(
			'name'=>_("Nome"),
			'slug'=>array(_("Slug"), _('utilizzato per creare un permalink alla risorsa')),
			'description'=>_('Descrizione'),
			'image'=>array(_('Immagine'), _('Attenzione, l\'immagine inserita non viene ridimensionata'))
		);

		parent::__construct($id);

		$this->_model_label = $this->id ? $this->name : '';
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

        $base_path = $this->_controller->getBaseAbsPath('img');

		$structure['image'] = new imageField(array(
            'name'=>'image', 
            'value'=>$this->image, 
            'label'=>$this->_fields_label['image'], 
            'lenght'=>100, 
            'extensions'=>self::$_extension_img, 
            'path'=>$base_path, 
            'resize'=>false
        ));
		
		return $structure;
	}

	/**
	 * Restituisce l'istanza newsCtg a partire dallo slug fornito 
	 * 
	 * @param string $slug lo slug 
	 * @param news $controller istanza del controller
	 * @access public
	 * @return istanza di newsCtg
	 */
	public static function getFromSlug($slug, $controller) {
	
		$res = null;

		$db = db::instance();
		$rows = $db->select('id', self::$tbl_ctg, "slug='$slug'", null, array(0, 1));
		if(count($rows)) {
			$res = new newsCtg($rows[0]['id'], $controller);
		}

		return $res;
			
	}

    /**
	 * Path relativo dell'immagine associata 
	 * 
	 * @param news $controller istanza del controller
	 * @return path relativo dell'immagine
	 */
	public function imagePath($controller) {

		return $controller->getBasePath('img').'/'.$this->image;

	}




}

?>
