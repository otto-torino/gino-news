<?php
/**
 * @file class_news.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.News.news
 *
 * @version 2.1.0
 * @copyright 2012-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author Marco Guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @namespace Gino.App.News
 * @description Namespace dell'applicazione News
 */
namespace Gino\App\News;

use \Gino\Registry;
use \Gino\Loader;
use \Gino\Error;
use \Gino\View;
use \Gino\GTag;
use \Gino\Options;
use \Gino\AdminTable;
use \Gino\App\Module\ModuleInstance;

require_once('class.Article.php');
require_once('class.Category.php');

/**
 * @brief Classe di tipo Gino.Controller per la gestione di news categorizzate.
 *
 * Gli output disponibili sono:
 *
 * - ultime n news, n da opzioni (template)
 * - archivio news paginato (vista)
 * - vetrina news (template)
 * - vista singola news (vista)
 * - feed RSS (vista)
 * 
 * @version 2.1.0
 * @copyright 2012-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authorrMarco Guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class news extends \Gino\Controller {

    /**
     * @brief numero di ultime news
     */
    private $_last_news_number;

    /**
     * @brief numero di news per pagina nella vista elenco news
     */
    private $_list_nfp;

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
     * @brief Massima larghezza immagini
     */
    private $_image_width;

    /**
     * @brief Numero ultime news esportate in lista newsletter
     */
    private $_newsletter_news_number;

    /**
     * @brief Tabella di opzioni 
     */
    private $_tbl_opt;

    /**
     * @brief Costruisce un'istanza di tipo news
     *
     * @param int $mdlId id dell'istanza di tipo news
     * @return istanza di Gino.App.News.news
     */
    function __construct($mdlId) {

        parent::__construct($mdlId);

        $this->_tbl_opt = 'news_opt';

        $this->_optionsValue = array(
            'last_news_number'=>3,
            'list_nfp'=>5,
            'showcase_news_number'=>5,
            'showcase_auto_start'=>0,
            'showcase_auto_interval'=>5000,
            'image_width'=>600,
            'newsletter_news_number'=>10,
        );

        $this->_last_news_number = $this->setOption('last_news_number', array('value'=>$this->_optionsValue['last_news_number']));
        $this->_list_nfp = $this->setOption('list_nfp', array('value'=>$this->_optionsValue['list_nfp']));
        $this->_showcase_news_number = $this->setOption('showcase_news_number', array('value'=>$this->_optionsValue['showcase_news_number']));
        $this->_showcase_auto_start = $this->setOption('showcase_auto_start', array('value'=>$this->_optionsValue['showcase_auto_start']));
        $this->_showcase_auto_interval = $this->setOption('showcase_auto_interval', array('value'=>$this->_optionsValue['showcase_auto_interval']));
        $this->_image_width = $this->setOption('image_width', array('value'=>$this->_optionsValue['image_width']));
        $this->_newsletter_news_number = $this->setOption('newsletter_news_number', array('value'=>$this->_optionsValue['newsletter_news_number']));

        $this->_options = new Options($this);
        $this->_optionsLabels = array(
            "last_news_number"=>array(
                'label'=>_("Numero ultime news"),
                'value'=>$this->_optionsValue['last_news_number'],
                'section'=>true,
                'section_title'=>_('Ultime news')
            ),
            "list_nfp"=>array(
                'label'=>_("Numero news per pagina"),
                'value'=>$this->_optionsValue['list_nfp'],
                'section'=>true,
                'section_title'=>_('Archivio news')
            ),
            "showcase_news_number"=>array(
                'label'=>_("Numero news"),
                'value'=>$this->_optionsValue['showcase_news_number'],
                'section'=>true,
                'section_title'=>_('Vetrina news')
            ),
            "showcase_auto_start"=>array(
                'label'=>_("Animazione automatica"),
                'value'=>$this->_optionsValue['showcase_auto_start']
            ),
            "showcase_auto_interval"=>array(
                'label'=>_("Intervallo animazione automatica (ms)"),
                'value'=>$this->_optionsValue['showcase_auto_start']
            ),
            "image_width"=>array(
                'label'=>_("Larghezza massima immagini"),
                'value'=>$this->_optionsValue['image_width'],
                'section'=>true,
                'section_title'=>_('Media')
            ),
            "newsletter_news_number"=>array(
                'label'=>_("Numero news esportate nella lista"),
                'value'=>$this->_optionsValue['newsletter_news_number'],
                'section'=>true,
                'section_title'=>_('Newsletter')
            ),
        );
    }

    /**
     * @brief Restituisce alcune proprietà della classe utili per la generazione di nuove istanze
     *
     * @return array associativo di proprietà utilizzate per la creazione di istanze di tipo news (tabelle, css, viste, folders)
     */
    public static function getClassElements() {

        return array(
            "tables"=>array(
                'news_article',
                'news_article_category',
                'news_category',
                'news_opt',
            ),
            "css"=>array(
                'news.css'
            ),
            "views" => array(
                'archive.php' => _('Archivio news'),
                'detail.php' => _('Dettaglio news'),
                'last.php' => _('Lista ultime news'),
                'showcase.php' => _('Vetrina news'),
                'feed_rss.php' => _('Feed RSS'),
                'newsletter.php' => _('News esportate in newsletter')
            ),
            "folderStructure"=>array (
                CONTENT_DIR.OS.'news'=> array(
                    'img' => null,
                    'attachment' => null
                )
            )
        );

    }

    /**
     * @brief Eliminazione istanza
     *
     * Si esegue la cancellazione dei dati da db e l'eliminazione di file e directory
     *
     * @return TRUE
     */
    public function deleteInstance() {

        $this->requirePerm('can_admin');

        /* eliminazione items */
        Article::deleteInstance($this);
        /* eliminazione categorie */
        Category::deleteInstance($this);

        /* eliminazione da tabella opzioni */
        $opt_id = $this->_db->getFieldFromId($this->_tbl_opt, "id", "instance", $this->_instance);
        \Gino\Translation::deleteTranslations($this->_tbl_opt, $opt_id);
        $result = $this->_db->delete($this->_tbl_opt, "instance=".$this->_instance);

        /* eliminazione file css */
        $classElements = $this->getClassElements();
        foreach($classElements['css'] as $css) {
            unlink(APP_DIR.OS.$this->_class_name.OS.\Gino\baseFileName($css)."_".$this->_instance_name.".css");
        }

        /* eliminazione views */
        foreach($classElements['views'] as $k => $v) {
            unlink($this->_view_dir.OS.\Gino\baseFileName($k)."_".$this->_instance_name.".php");
        }

        /* eliminazione cartelle contenuti */
        foreach($classElements['folderStructure'] as $fld=>$fldStructure) {
            \Gino\deleteFileDir($fld.OS.$this->_instance_name, true);
        }

        return TRUE;
    }

    /**
     * @brief Definizione dei metodi pubblici che forniscono un output per il front-end
     *
     * Questo metodo viene letto dal motore di generazione dei layout (metodi non presenti nel file news.ini) e dal motore di generazione
     * di voci di menu (metodi presenti nel file news.ini) per presentare una lista di output associati all'istanza di classe.
     *
     * @return array associativo NOME_METODO => array('label' => LABEL, 'permissions' => PERMESSI)
     */
    public static function outputFunctions() {

        $list = array(
            "last" => array("label"=>_("Lista utime news"), "permissions"=>array()),
            "archive" => array("label"=>_("Lista news paginata"), "permissions"=>array()),
            "showcase" => array("label"=>_("Vetrina"), "permissions"=>array()),
            "feedRSS" => array("label"=>_("Feed RSS"), "permissions"=>array())
        );

        return $list;
    }

    /**
     * @brief Getter larghezza di ridimensionamenteo delle immagini 
     * @return largheza di ridimensionamento
     */
    public function getImageWidth() {
        return $this->_image_width;
    }

    /**
     * @brief Esegue il download clientside del documento indicato da url ($doc_id)
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @throws Gino.Exception.Exception404 se il documento non viene trovato
     * @throws Gino.Exception.Exception403 se il documento è associato ad una news che non si può visualizzare
     * @return Gino.Http.ResponseFile
     */
    public function download(\Gino\Http\Request $request) {

        $doc_id = cleanVar($request->GET, 'id', 'int');
        if($doc_id) {
            $n = new Article($doc_id, $this);
            if(!$n->id) {
                throw new \Gino\Exception\Exception404();
            }
            if($n->private && !$this->userHasPerm('can_view_private')) {
                throw new \Gino\Exception\Exception403();
            }
            $attachment = $n->attachment;
            if($attachment) {
                $full_path = $this->getBaseAbsPath().OS.'attachment'.OS.$attachment;
                return \Gino\download($full_path); // restituisce un \Gino\Http\ResponseFile
            }
            else {
                throw new \Gino\Exception\Exception404();
            }
        }
        else {
            throw new \Gino\Exception\Exception404();
        }
    }

    /**
     * @brief Frontend vetrina news
     * @return html, vetrina ultime news
     */
    public function showcase() {

        if($this->userHasPerm('can_view_private')) {
            $private = TRUE;
        }
        else {
            $private = FALSE;
        }

        $this->_registry->addCss($this->_class_www."/news_".$this->_instance_name.".css");
        $this->_registry->addJs($this->_class_www."/news.js");

        $where_arr = array("instance='".$this->_instance."' AND published='1'");
        if(!$private) {
            $where_arr[] = "private='0'";
        }

        $news = Article::objects($this, array('where' => implode(' AND ', $where_arr), 'order'=>'date DESC, insertion_date DESC', 'limit'=>array(0, $this->_showcase_news_number)));

        $view = new View($this->_view_dir);

        $view->setViewTpl('showcase_'.$this->_instance_name);
        $dict = array(
            'instance_name' => $this->_instance_name,
            'feed_url' => $this->link($this->_instance_name, 'feedRSS'),
            'news' => $news,
            'autostart' => $this->_showcase_auto_start,
            'autointerval' => $this->_showcase_auto_interval
        );

        return $view->render($dict);
    }

    /**
     * @brief Front end ultime news
     * @return html, lista ultime news
     */
    public function last() {

        $request = \Gino\Http\Request::instance();

        $private = $this->userHasPerm('can_view_private') ? TRUE : FALSE;

        $title_site = $this->_registry->sysconf->head_title;
        $module = new ModuleInstance($this->_instance);
        $title = $module->label.' | '.$title_site;

        $this->_registry->addCss($this->_class_www."/news_".$this->_instance_name.".css");
        $this->_registry->addHeadLink(array(
            'rel' => 'alternate',
            'type' => 'application/rss+xml',
            'title' => \Gino\jsVar($title),
            'href' => $request->root_absolute_url.$this->link($this->_instance_name, 'feedRSS')
        ));

        $where_arr = array("published='1'");
        if(!$private) {
            $where_arr[] = "private='0'";
        }

        $news = Article::objects($this, array('where' => implode(' AND ', $where_arr), 'order'=>'date DESC, insertion_date DESC', 'limit'=>array(0, $this->_last_news_number)));

        $view = new View($this->_view_dir, 'last_'.$this->_instance_name);

        $dict = array(
            'instance_name' => $this->_instance_name,
            'news' => $news,
            'feed_url' => $this->link($this->_instance_name, 'feedRSS'),
            'archive_url' => $this->link($this->_instance_name, 'archive')
        );

        return $view->render($dict);

    }

    /**
     * @brief Front end dettaglio news
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @throws Gino.Exception.Exception404 se lo slug ricavato dalle GET non corrisponde ad alcuna news
     * @throws Gino.Exception.Exception403 se l'utente non ha i permessi per visualizzare la news
     * @return Gino.Http.Response, dettaglio news
     */
    public function detail(\Gino\Http\Request $request) {

        $slug = \Gino\cleanVar($request->GET, 'id', 'string');

        $item = Article::getFromSlug($slug, $this);

        if(!$item || !$item->id || !$item->published) {
            throw new \Gino\Exception\Exception404();
        }
        if($item->private && !$this->userHasPerm('can_view_private')) {
            throw new \Gino\Exception\Exception403();
        }

        $this->_registry->addCss($this->_class_www."/news_".$this->_instance_name.".css");

        $view = new view($this->_view_dir, 'detail_'.$this->_instance_name);

        $dict = array(
            'instance_name' => $this->_instance_name,
            'news' => $item,
            'related_contents_list' => $this->relatedContentsList($item),
            'social' => \Gino\shareAll('st_all_large', $this->link($this->_instance_name, 'detail', array('id' => $item->slug))),
        );

        $document = new \Gino\Document($view->render($dict));
        return $document();

    }

    /**
     * @brief Lista di contenuti correlati per tag
     * @param \Gino\App\News\Article $item oggetto @ref Gino.App.News.Article
     * @return html, lista contenuti correlati
     */
    public function relatedContentsList($item)
    {
        $related_contents = GTag::getRelatedContents('Article', $item->id);
        if(count($related_contents)) {
            $view = new View(null, 'related_contents_list');
            return $view->render(array('related_contents' => $related_contents));
        }
        else return '';
    }

    /**
     * @brief Frontend archivio news
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Response, archivio news
     */
    public function archive(\Gino\Http\Request $request) {

        $this->_registry->addCss($this->_class_www."/news_".$this->_instance_name.".css");

        $ctgslug = \Gino\cleanVar($request->GET, 'ctg', 'string');

        if($this->userHasPerm('can_view_private')) {
            $private = TRUE;
        }
        else {
            $private = FALSE;
        }

        if($ctgslug) {
            $ctg = Category::getFromSlug($ctgslug, $this);
            $ctg_id = $ctg ? $ctg->id : 0;
        }
        else {
            $ctg = null;
            $ctg_id = 0;
        }

        $news_number = Article::getCount($this, array('published' => TRUE, 'private'=>$private, 'ctg'=>$ctg_id));

        $paginator = Loader::load('Paginator', array($news_number, $this->_list_nfp));
        $limit = $paginator->limitQuery();

        $where_arr = array("instance='".$this->_instance."' AND published='1'");
        if($ctg_id) {
            $where_arr[] = "id IN (SELECT article_id FROM ".Article::$table_ctgs." WHERE category_id='".$ctg_id."')";
        }
        if(!$private) {
            $where_arr[] = "private='0'";
        }

        $news = Article::objects($this, array('where'=>implode(' AND ', $where_arr), 'order'=>'date DESC, insertion_date DESC', 'limit'=>$limit));

        $view = new View($this->_view_dir);
        $view->setViewTpl('archive_'.$this->_instance_name);
        $dict = array(
            'instance_name' => $this->_instance_name,
            'feed_url' => $this->link($this->_instance_name, 'feedRSS'),
            'ctg' => $ctg,
            'news' => $news,
            'pagination' => $paginator->pagination()
        );

        $document = new \Gino\Document($view->render($dict));
        return $document();

    }

    /**
     * @brief Interfaccia di amministrazione del modulo
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Response, interfaccia amministrazione
     */
    public function manageDoc(\Gino\Http\Request $request) {

        $this->requirePerm(array('can_admin', 'can_publish', 'can_write'));

        $block = \Gino\cleanVar($request->GET, 'block', 'string');

        $link_frontend = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=frontend'), _('Frontend'));
        $link_options = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=options'), _('Opzioni'));
        $link_ctg = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=ctg'), _('Categorie'));
        $link_dft = sprintf('<a href="%s">%s</a>', $this->linkAdmin(), _('Contenuti'));
        $sel_link = $link_dft;

        if($block == 'frontend' && $this->userHasPerm('can_admin')) {
            $backend = $this->manageFrontend();
            $sel_link = $link_frontend;
        }
        elseif($block == 'options' && $this->userHasPerm('can_admin')) {
            $backend = $this->manageOptions();
            $sel_link = $link_options;
        }
        elseif($block == 'ctg') {
            $backend = $this->manageCategory($request);
            $sel_link = $link_ctg;
        }
        else {
            $backend = $this->manageNews($request);
        }

        if(is_a($backend, '\Gino\Http\Response')) {
            return $backend;
        }

        // groups privileges
        if($this->userHasPerm('can_admin')) {
            $links_array = array($link_frontend, $link_options, $link_ctg, $link_dft);
        }
        else {
            $links_array = array($link_ctg, $link_dft);
        }

        $module = ModuleInstance::getFromName($this->_instance_name);

        $view = new View(null, 'tab');
        $dict = array(
            'title' => \Gino\htmlChars($module->label),
            'links' => $links_array,
            'selected_link' => $sel_link,
            'content' => $backend
        );

        $document = new \Gino\Document($view->render($dict));
        return $document();

    }

    /**
     * @brief Interfaccia di amministrazione delle categorie
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Redirect oppure html, interfaccia di back office
     */
    private function manageCategory(\Gino\Http\Request $request) {

        $admin_table = new AdminTable($this, array());

        $backend = $admin_table->backOffice(
            'Category',
            array(
                'list_display' => array('id', 'name', 'slug'),
                'list_title' => _("Elenco categorie"),
                'list_description' => "<p>"._('Ciascuna news inserita potrà essere associata ad una o più categorie qui definite.')."</p>",
                 ),
            array(),
            array(
                'description' => array(
                    'widget' => 'editor',
                    'notes' => FALSE,
                    'img_preview' => TRUE,
                ),
                'image' => array(
                    'preview' => TRUE
                )
            )
        );

        return $backend;
    }

    /**
     * @brief Interfaccia di amministrazione delle news 
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Redirect oppure html, interfaccia di back office
     */
    private function manageNews(\Gino\Http\Request $request) {

        if(!$this->userHasPerm('can_admin') and !$this->userHasPerm('can_publish')) {
            $remove_fields = array('published');
            $delete_deny = 'all';
        }
        else {
            $remove_fields = array();
            $delete_deny = array();
        }

        $admin_table = new AdminTable($this, array('delete_deny'=>$delete_deny));

        $backend = $admin_table->backOffice(
            'Article',
            array(
                'list_display' => array('id', 'date', 'categories', 'title', 'published', 'private'),
                'list_title' => _("Elenco news"),
                'filter_fields' => array('categories', 'title', 'published')
            ),
            array(
                'removeFields' => $remove_fields
            ),
            array(
                'text' => array(
                    'widget' => 'editor',
                    'notes' => FALSE,
                    'img_preview' => FALSE,
                ),
                'img' => array(
                    'preview' => TRUE
                )
            )
        );

        return $backend;
    }

    /**
     * @brief Metodo per la definizione di parametri da utilizzare per il modulo "Ricerca nel sito"
     *
     * Il modulo "Ricerca nel sito" di Gino base chiama questo metodo per ottenere informazioni riguardo alla tabella, campi, pesi etc...
     * per effettuare la ricerca dei contenuti.
     *
     * @return array[string]mixed array associativo contenente i parametri per la ricerca
     */
    public function searchSite() {

        $private = $this->userHasPerm('can_view_private') ? TRUE : FALSE;

        return array(
            "table"=>Article::$table, 
            "selected_fields"=>array("id", "slug", "date", array("highlight"=>true, "field"=>"title"), array("highlight"=>true, "field"=>"text")), 
            "required_clauses"=>$private ? array("instance"=>$this->_instance, 'published'=>1) : array("instance"=>$this->_instance, 'private'=>0, 'published'=>1), 
            "weight_clauses"=>array("title"=>array("weight"=>3), "text"=>array("weight"=>1))
        );
    }

    /**
     * @brief Definisce la presentazione del singolo item trovato a seguito di ricerca (modulo "Ricerca nel sito")
     *
     * @param array $results array associativo contenente i risultati della ricerca
     * @return html, presentazione item tra i risultati della ricerca
     */
    public function searchSiteResult($results) {

        $obj = new Article($results['id'], $this);

        $buffer = "<div>".\Gino\dbDatetimeToDate($results['date'], "/")." <a href=\"".$this->link($this->_instance_name, 'detail', array('id'=>$results['slug']))."\">";
        $buffer .= $results['title'] ? \Gino\htmlChars($results['title']) : \Gino\htmlChars($obj->ml('title'));
        $buffer .= "</a></div>";

        if($results['text']) {
            $buffer .= "<div class=\"search_text_result\">...".\Gino\htmlChars($results['text'])."...</div>";
        }
        else {
            $buffer .= "<div class=\"search_text_result\">".\Gino\htmlChars(\Gino\cutHtmlText($obj->ml('text'), 120, '...', false, false, false, array('endingPosition'=>'in')))."</div>";
        }

        return $buffer;

    }

    /**
     * @brief Adattatore per la classe newsletter 
     * @return array di elementi esportabili nella newsletter
     */
    public function systemNewsletterList() {

        $news = Article::objects($this, array('where' => "instance='".$this->_instance."'", 'order'=>'date DESC, insertion_date DESC', 'limit'=>array(0, $this->_newsletter_news_number)));

        $items = array();
        foreach($news as $n) {
            $items[] = array(
                _('id') => $n->id,
                _('titolo') => \Gino\htmlChars($n->ml('title')),
                _('privata') => $n->private ? _('si') : _('no'),
                _('pubblicata') => $n->published ? _('si') : _('no'),
                _('data') => \Gino\dbDateToDate($n->date),
            );
        }

        return $items;
    }

    /**
     * @brief Contenuto di una news quanto inserita in una newsletter 
     * @param int $id identificativo della news
     * @return html, contenuto news
     */
    public function systemNewsletterRender($id) {

        $n = new Article($id, $this);

        $view = new View($this->_view_dir, 'newsletter_'.$this->_instance_name);
        $dict = array(
            'news' => $n,
        );

        return $view->render($dict);

    }

    /**
     * @brief Genera un feed RSS standard che presenta le ultime 50 news pubblicate
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return \Gino\Http\Response, feed RSS
     */
    public function feedRSS(\Gino\Http\Request $request) {

        $title_site = $this->_registry->sysconf->head_title;
        $module = new ModuleInstance($this->_instance);
        $title = $module->label.' | '.$title_site;
        $description = $module->description;

        $news = Article::objects($this, array('where' => "instance='".$this->_instance."' AND private='0' AND published='1'", 'order'=>'date DESC, insertion_date DESC', 'limit'=>array(0, 50)));

        $view = new \Gino\View($this->_view_dir, 'feed_rss_'.$this->_instance_name);
        $dict = array(
            'title' => $title,
            'description' => $description,
            'request' => $request,
            'news' => $news
        );

        $response = new \Gino\Http\Response($view->render($dict));
        $response->setContentType('text/xml');
        return $response;
    }

}
