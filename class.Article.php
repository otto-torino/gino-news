<?php
/**
 * @file class.Article.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.News.Article.
 *
 * @version 2.1.1
 * @copyright 2012-2016 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
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
use \Gino\GTag;

/**
 * \ingroup news
 * @brief Classe tipo Gino.Model che rappresenta una singola news
 *
 * @version 2.1.1
 * @copyright 2012-2016 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author Marco Guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Article extends \Gino\Model {

    public static $table = 'news_article';
    public static $table_ctgs = 'news_article_category';
    public static $columns;
    
    protected static $_extension_img = array('jpg', 'jpeg', 'png');
    protected static $_extension_attachment = array('pdf', 'doc', 'xdoc', 'odt', 'xls', 'csv', 'txt');

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
     * @see Gino.Model::properties()
     */
    protected static function properties($model, $controller) {
    	
    	$instance = $controller->getInstance();
    	$base_path = $controller->getBaseAbsPath();
    	 
    	$property['tags'] = array(
			'model_controller_instance' => $instance,
		);
    	$property['img'] = array(
    		'path' => $base_path.OS.'img',
    		'width' => $controller->getImageWidth()
    	);
    	$property['attachment'] = array(
    		'path' => $base_path.OS.'attachment'
    	);
    	$property['categories'] = array(
    		'm2m_where' => 'instance=\''.$instance.'\'',
    		'm2m_controller' => $controller,
    		'add_related_url' => $controller->linkAdmin(array(), 'block=ctg&insert=1')
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
			'name'=>'id',
			'primary_key'=>true,
			'auto_increment'=>true,
			'max_lenght'=>11,
		));
		$columns['instance'] = new \Gino\IntegerField(array(
			'name'=>'instance',
			'required'=>true,
			'max_lenght'=>11,
		));
		$columns['insertion_date'] = new \Gino\DatetimeField(array(
			'name' => 'insertion_date',
			'label' => _('Data inserimento'),
			'required' => true,
			'auto_now' => FALSE,
			'auto_now_add' => TRUE
		));
		$columns['last_edit_date'] = new \Gino\DatetimeField(array(
			'name' => 'last_edit_date',
			'label' => _('Data ultima modifica'),
			'required'=>true,
			'auto_now' => TRUE,
			'auto_now_add' => TRUE
		));
		$columns['date'] = new \Gino\DateField(array(
			'name' => 'date',
			'label' => _('Data'),
			'required' => TRUE,
		));
		$columns['title'] = new \Gino\CharField(array(
			'name'=>'title',
			'label'=>_("Titolo"),
			'required'=>true,
			'max_lenght'=>200,
		));
		$columns['slug'] = new \Gino\SlugField(array(
			'name' => 'slug',
			'unique_key' => true,
			'label' => array(_("Slug"), _('utilizzato per creare un permalink alla risorsa')),
			'required' => true,
			'max_lenght' => 200,
			'autofill' => array('date', 'title'),
		));
		$columns['text'] = new \Gino\TextField(array(
			'name' => 'text',
			'label' => _("Testo"),
			'required' => false
		));
		$columns['tags'] = new \Gino\TagField(array(
			'name' => 'tags',
			'label' => array(_('Tag'), _("elenco separato da virgola")),
			'required' => false,
			'max_lenght' => 255,
			'model_controller_class' => 'news',
			'model_controller_instance' => null,
		));
		$columns['img'] = new \Gino\ImageField(array(
			'name' => 'img',
			'label' => _('Immagine'),
			'extensions' => self::$_extension_img,
			'path' => null,
			'resize' => TRUE,
			'thumb' => FALSE,
			'width' => null
		));
		$columns['attachment'] = new \Gino\FileField(array(
			'name' => 'attachment',
			'label' => _('Allegato'),
			'extensions' => self::$_extension_attachment,
			'path' => null,
			'check_type' => FALSE
		));
		$columns['private'] = new \Gino\BooleanField(array(
			'name' => 'private',
			'label' => array(_('Privata'), _('le news private sono visualizzabili solamente dagli utenti che hanno il permesso \'visualizzazione news private\'')),
			'required' => true,
			'default' => 0
		));
		$columns['social'] = new \Gino\BooleanField(array(
			'name' => 'social',
			'label' => _('Condivisione social networks'),
			'required' => true,
			'default' => 0
		));
		$columns['slideshow'] = new \Gino\BooleanField(array(
			'name' => 'slideshow',
			'label' => _('News da mostrare nello slideshow'),
			'required' => true,
			'default' => 0
		));
		$columns['published'] = new \Gino\BooleanField(array(
			'name' => 'published',
			'label' => _('Pubblicata'),
			'required' => true,
			'default' => 0
		));
        $columns['categories'] = new \Gino\ManyToManyField(array(
            'name' => 'categories',
            'label' => _("Categorie"),
            'm2m' => '\Gino\App\News\Category',
            'm2m_where' => null,
            'm2m_controller' => null,
            'join_table' => self::$table_ctgs,
            'add_related' => TRUE,
            'add_related_url' => null
        ));

        return $columns;
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
     * @brief Restituisce il numero di record che soddisfano le condizioni date
     *
     * @param \Gino\App\News\news $controller istanza del controller Gino.App.News.news
     * @param array $options array associativo di opzioni
     * @return integer
     */
    public static function getCount($controller, $options = null) {

        $res = 0;

        $db = Db::instance();
        $selection = 'COUNT(id) AS tot';
        $table = self::$table;
        
        $where = self::setConditionWhere($controller, $options);

        $rows = $db->select($selection, $table, $where);

        if($rows && count($rows)) {
            $res = $rows[0]['tot'];
        }

        return $res;
    }
    
    public function setConditionWhere($controller, $options = null) {
    	
    	$private = \Gino\gOpt('private', $options, FALSE);
    	$published = \Gino\gOpt('published', $options, TRUE);
    	$ctg = \Gino\gOpt('ctg', $options, false);
    	$tag = \Gino\gOpt('tag', $options, null);
    	
    	$where = array("instance='".$controller->getInstance()."'");
    	
    	if(!$private) {
    		$where[] = "private='0'";
    	}
    	if($published) {
    		$where[] = "published='1'";
    	}
    	if($ctg) {
    		$where[] = "id IN (SELECT article_id FROM ".self::$table_ctgs." WHERE category_id='".$ctg."')";
    	}
    	if($tag) {
    		$where[] = "id IN (SELECT sys_tag_taggeditem.content_id FROM sys_tag_taggeditem, sys_tag 
    		WHERE sys_tag.tag='$tag' AND sys_tag_taggeditem.tag_id=sys_tag.id
    		AND sys_tag_taggeditem.content_controller_class='news'
    		AND sys_tag_taggeditem.content_controller_instance='".$controller->getInstance()."')";
    	}
    	
    	return implode(' AND ', $where);
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
     * Dimensioni immagine
     * @return array ('width' => WIDTH, 'height' => HEIGHT)
     */
    public function getSize() {
    	
    	list($width, $height, $type, $attr) = getimagesize(\Gino\absolutePath($this->getImgPath()));
    	return array('width' => $width, 'height' => $height);
    }
    
    /**
     * Elenco tag di una news
     * @return string
     */
    public function viewTags() {
    	
    	$buffer = '';
    	
    	if($this->tags)
    	{
    		$cleaned_tags = array_map('trim', explode(',', $this->tags));
    		
    		foreach ($cleaned_tags AS $tag)
    		{
    			$link = $this->_controller->link($this->_controller->getInstanceName(), 'archive', array('tag' => $tag));
    			$buffer .= "<a href=\"".$link."\" onclick=\"\">$tag</a>";
    		}
    	}
    	
    	return $buffer;
    }

    /**
     * @brief Data in formato iso 8601
     *
     * @return data iso 8601
     */
    public function dateIso() {
    	
        $datetime = new \Datetime($this->date);
        return $datetime->format('c');
    }
    
    /**
     * @see Gino.Model::delete()
     */
    public function delete() {
    	
    	\Gino\GTag::deleteTaggedItem($this->_controller->getClassName(), $this->_controller->getInstance(), get_name_class($this), $this->id);
    	
    	return parent::delete();
    }
}

Article::$columns=Article::columns();