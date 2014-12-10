<?php
/**
 * @file class.Article.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.News.Article.
 *
 * @version 2.1.0
 * @copyright 2012-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author Marco Guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino\App\News;

use \Gino\ManyToManyField;
use \Gino\BooleanField;
use \Gino\TagField;
use \Gino\DatetimeField;
use \Gino\ImageField;
use \Gino\FileField;
use \Gino\SlugField;
use \Gino\Db;
use \Gino\Link;

/**
 * @brief Classe tipo Gino.Model che rappresenta una singola news
 *
 * @version 2.1.0
 * @copyright 2012-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author Marco Guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Article extends \Gino\Model {

    protected static $_extension_img = array('jpg', 'jpeg', 'png');
    protected static $_extension_attachment = array('pdf', 'doc', 'xdoc', 'odt', 'xls', 'csv', 'txt');
    public static $table = 'news_article';
    public static $table_ctgs = 'news_article_category';

    /**
     * @brief Costruttore
     *
     * @param integer $id valore ID del record
     * @param \Gino\App\News\news $instance istanza del controller Gino.App.News.news
     */
    function __construct($id, $instance) {

        $this->_controller = $instance;
        $this->_tbl_data = self::$table;

        $this->_fields_label = array(
            'insertion_date'=>_('Data inserimento'),
            'last_edit_date'=>_('Data ultima modifica'),
            'date'=>_('Data'),
            'categories'=>_("Categorie"),
            'title'=>_("Titolo"),
            'slug'=>array(_("Slug"), _('utilizzato per creare un permalink alla risorsa')),
            'text'=>_('Testo'),
            'tags'=>array(_('Tag'), _("elenco separato da virgola")),
            'img'=>_('Immagine'),
            'attachment'=>_('Allegato'),
            'private'=>array(_('Privata'), _('le news private sono visualizzabili solamente dagli utenti che hanno il permesso \'visualizzazione news private\'')),
            'social'=>_('Condivisione social networks'),
            'published'=>_('Pubblicata'),
        );

        parent::__construct($id);

        $this->_model_label = _('Articolo');
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return titolo news
     */
    function __toString() {
        return (string) $this->title;
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

        $structure['categories'] = new ManyToManyField(array(
            'name' => 'categories',
            'model' => $this,
            'm2m' => '\Gino\App\News\Category',
            'm2m_where' => 'instance=\''.$this->_controller->getInstance().'\'',
            'm2m_controller' => $this->_controller,
            'join_table' => self::$table_ctgs,
            'add_related' => TRUE,
            'add_related_url' => $this->_controller->linkAdmin(array(), 'block=ctg&insert=1')
        ));

        $structure['slug'] = new SlugField(array(
            'name' => 'slug',
            'model' => $this,
            'required' => TRUE,
            'autofill' => array('date', 'title')
        ));

        $structure['tags'] = new TagField(array(
            'name' => 'tags',
            'model' => $this,
            'model_controller_class' => 'news',
            'model_controller_instance' => $this->_controller->getInstance()
        ));

        $structure['published'] = new BooleanField(array(
            'name' => 'published',
            'model' => $this,
            'enum' => array(1 => _('si'), 0 => _('no')),
        ));

        $structure['private'] = new BooleanField(array(
            'name' => 'private',
            'model' => $this,
            'enum' => array(1 => _('si'), 0 => _('no'))
        ));

        $structure['social'] = new BooleanField(array(
            'name' => 'social',
            'model' => $this,
            'enum' => array(1 => _('si'), 0 => _('no'))
        ));

        $structure['insertion_date'] = new DatetimeField(array(
            'name' => 'insertion_date',
            'model' => $this,
            'auto_now' => FALSE,
            'auto_now_add' => TRUE
        ));

        $structure['last_edit_date'] = new DatetimeField(array(
            'name' => 'last_edit_date',
            'model' => $this,
            'auto_now' => TRUE,
            'auto_now_add' => TRUE
        ));

        $base_path = $this->_controller->getBaseAbsPath() . OS . 'img';
        $structure['img'] = new ImageField(array(
            'name' => 'img',
            'model' => $this,
            'extensions' => self::$_extension_img,
            'path' => $base_path,
            'resize' => TRUE,
            'thumb' => FALSE,
            'width' => $this->_controller->getImageWidth()
        ));

        $base_path = $this->_controller->getBaseAbsPath() . OS . 'attachment';
        $structure['attachment'] = new FileField(array(
            'name' => 'attachment',
            'model' => $this,
            'extensions' => self::$_extension_attachment,
            'path' => $base_path,
            'check_type' => FALSE
        ));

        return $structure;
    }

    /**
     * @brief Lista di oggetti categoria associati alla news
     * @return array di istanze di Gino.App.News.Category
     */
    public function objCategories() {

        $res = array();
        foreach($this->categories as $ctgid) {
            $res[] = new Category($ctgid, $this->_controller);
        }

        return $res;

    }

    /**
     * @brief Restituisce il numero di news che soddisfano le condizioni date
     *
     * @param \Gino\App\News\news $controller istanza del controller Gino.App.News.news
     * @param array $options array associativo di opzioni
     * @return numero di news
     */
    public static function getCount($controller, $options = null) {

        $res =0;

        $private = \Gino\gOpt('private', $options, FALSE);
        $published = \Gino\gOpt('published', $options, TRUE);
        $ctg = \Gino\gOpt('ctg', $options, false);

        $db = Db::instance();
        $selection = 'COUNT(id) AS tot';
        $table = self::$table;
        $where_arr = array("instance='".$controller->getInstance()."'");
        if(!$private) {
            $where_arr[] = "private='0'";
        }
        if($published) {
            $where_arr[] = "published='1'";
        }
        if($ctg) {
            $where_arr[] = "id IN (SELECT article_id FROM ".self::$table_ctgs." WHERE category_id='".$ctg."')";
        }
        $where = implode(' AND ', $where_arr);

        $rows = $db->select($selection, $table, $where, null, null);

        if($rows && count($rows)) {
            $res = $rows[0]['tot'];
        }

        return $res;

    }

    /**
     * @brief Url relativo al dettaglio della news
     * @return url
     */
    public function getUrl() {

        return $this->_controller->link($this->_controller->getInstanceName(), 'detail', array('id' => $this->slug));
    }

    /**
     * @brief Path relativo dell'immagine associata
     * @return path relativo dell'immagine
     */
    public function getImgPath() {

        return $this->_controller->getBasePath().'/img/'.$this->img;
    }

    /**
     * @brief Path relativo dell'allegato
     * @return path relativo dell'allegato
     */
    public function getAttachmentPath() {

        return $this->_controller->getBasePath().'/attachment/'.$this->attachment;
    }

    /**
     * @brief Path relativo al download dell'allegato
     * @return path relativo
     */
    public function attachmentDownloadUrl() {

        return $this->_controller->link($this->_controller->getInstanceName(), 'download', array('id' => $this->id));
    }

    /**
     * @brief Data in formato iso 8601
     *
     * @return data iso 8601
     */
    public function dateIso()
    {
        $datetime = new \Datetime($this->date);
        return $datetime->format('c');
    }
}
