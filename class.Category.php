<?php
/**
 * @file class.Category.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.News.Category
 *
 * @version 2.1.1
 * @copyright 2012-2016 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */

namespace Gino\App\News;

use \Gino\ImageField;
use \Gino\SlugField;

/**
 * \ingroup news
 * @brief Classe di tipo Gino.Model che rappresenta una categoria di news.
 *
 * @version 2.1.1
 * @copyright 2012-2016 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class Category extends \Gino\Model {

    public static $table = 'news_category';
    public static $columns;
    
    protected static $_extension_img = array('jpg', 'jpeg', 'png');

    /**
     * @brief Costruttore
     *
     * @param integer $id valore ID del record
     * @param object $controller
     */
    function __construct($id, $controller) {

        $this->_controller = $controller;
        $this->_tbl_data = self::$table;

        parent::__construct($id);

        $this->_model_label = _('Categoria');
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return nome categoria
     */
    function __toString() {
        return (string) $this->name;
    }
    
    /**
     * @see Gino.Model::properties()
     */
    protected static function properties($model, $controller) {
    	
    	$property['image'] = array(
    		'path' => $controller->getBaseAbsPath() . OS . 'img'
    	);
    	
    	return $property;
    }

    /**
     * Struttura dei campi della tabella di un modello
     *
     * @return array
     */
    public static function columns() {

		$columns['id'] = new \Gino\IntegerField(array(
			'name' => 'id',
			'primary_key' => true,
			'auto_increment' => true,
        	'max_lenght' => 11,
		));
		$columns['instance'] = new \Gino\IntegerField(array(
			'name' => 'instance',
			'required' => true,
			'max_lenght' => 11,
		));
		$columns['name'] = new \Gino\CharField(array(
			'name' => 'name',
			'label' => _("Nome"),
			'required' => true,
			'max_lenght' => 200,
		));
		$columns['slug'] = new \Gino\SlugField(array(
			'name' => 'slug',
			'unique_key' => true,
			'label' => array(_("Slug"), _('utilizzato per creare un permalink alla risorsa')),
			'required' => true,
			'max_lenght' => 200,
			'autofill' => array('name'),
		));
		$columns['description'] = new \Gino\TextField(array(
			'name' => 'description',
			'label' => _("Descrizione"),
		));
		$columns['image'] = new \Gino\ImageField(array(
			'name' => 'image',
			'label' =>array(_('Immagine'), _('Attenzione, l\'immagine inserita non viene ridimensionata')),
			'max_lenght' => 200,
			'extensions' => self::$_extension_img,
			'path' => null,
			'resize' => false
		));

        return $columns;
    }

    /**
     * @brief Path relativo dell'immagine associata 
     *
     * @param news $controller istanza del controller
     * @return path relativo dell'immagine
     */
    public function imagePath($controller) {
        return $controller->getBasePath().'/img/'.$this->image;
    }
}

Category::$columns=Category::columns();
