<?php
/**
 * @file class.Category.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.News.Category
 *
 * @version 2.1.0
 * @copyright 2012-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */

namespace Gino\App\News;

use \Gino\ImageField;
use \Gino\SlugField;

/**
 * @brief Classe di tipo Gino.Model che rappresenta una categoria di news.
 *
 * @version 2.1.0
 * @copyright 2012-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class Category extends \Gino\Model {

    protected static $_extension_img = array('jpg', 'jpeg', 'png');
    public static $table = 'news_category';

    /**
     * @brief Costruttore
     *
     * @param integer $id valore ID del record
     * @param \Gino\App\News\news $instance istanza del controller Gino.App.News.news
     * @return istanza di Gino.App.News.Category
     */
    function __construct($id, $instance) {

        $this->_controller = $instance;
        $this->_tbl_data = self::$table;

        $this->_fields_label = array(
            'name'=>_("Nome"),
            'slug'=>array(_("Slug"), _('utilizzato per creare un permalink alla risorsa')),
            'description'=>_('Descrizione'),
            'image'=>array(_('Immagine'), _('Attenzione, l\'immagine inserita non viene ridimensionata'))
        );

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
     * @brief Sovrascrive la struttura di default
     *
     * @see Gino.Model::structure()
     * @param integer $id
     * @return array, struttura
     */
    public function structure($id) {

        $structure = parent::structure($id);

        $structure['slug'] = new SlugField(array(
            'name' => 'slug',
            'model' => $this,
            'required' => TRUE,
            'autofill' => array('name')
        ));

        $base_path = $this->_controller->getBaseAbsPath() . OS . 'img';
        $structure['image'] = new ImageField(array(
            'name' => 'image',
            'model' => $this,
            'extensions' => self::$_extension_img, 
            'path' => $base_path,
            'resize' => FALSE
        ));

        return $structure;
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
