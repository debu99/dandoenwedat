<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Controller.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Widget_ProfileBlogsController extends Engine_Content_Widget_Abstract {

  protected $_childCount;

  public function indexAction() {

    $viewer = Engine_Api::_()->user()->getViewer();

    if (!Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesblog'))
      return $this->setNoRender();
      
    $this->view->canCreate = 1;
    if( !Engine_Api::_()->authorization()->isAllowed('sesblog_blog', $viewer, 'create') ) 
    $this->view->canCreate = 0;
    
   // $id = Zend_Controller_Front::getInstance()->getRequest()->getParam('id', null);
  //  $this->view->event_id =  $event_id = Engine_Api::_()->getDbtable('events', 'sesevent')->getEventId($id);
	
    // Start work for grid, list and pinboard view
     // Default option
    if (isset($_POST['params']))
    $params = $_POST['params'];
    if (isset($_POST['searchParams']) && $_POST['searchParams'])
    parse_str($_POST['searchParams'], $searchArray);
    $this->view->is_ajax = $is_ajax = isset($_POST['is_ajax']) ? true : false;
    
    if(!$is_ajax) {
    //Get subject and check auth
    $subject = Engine_Api::_()->core()->getSubject();
    if (!Engine_Api::_()->core()->hasSubject())
      return $this->setNoRender();

    if (!$subject->authorization()->isAllowed($viewer, 'view'))
      return $this->setNoRender();
      
    }
    
    $page = isset($_POST['page']) ? $_POST['page'] : 1;
    $this->view->blog_parent_id =  $id = isset($params['blog_parent_id']) ? $params['blog_parent_id'] : Zend_Controller_Front::getInstance()->getRequest()->getParam('id', null);
    $this->view->event_id =  $event_id = Engine_Api::_()->getDbtable('events', 'sesevent')->getEventId($id);
    $this->view->identityForWidget = isset($_POST['identity']) ? $_POST['identity'] : '';
    $this->view->loadOptionData = $loadOptionData = isset($params['pagging']) ? $params['pagging'] : $this->_getParam('pagging', 'auto_load');
    $this->view->height_list = $defaultHeightList = isset($params['height_list']) ? $params['height_list'] : $this->_getParam('height_list','160');
    $this->view->width_list = $defaultWidthList = isset($params['width_list']) ? $params['width_list'] : $this->_getParam('width_list','140');
    $this->view->height_grid = $defaultHeightGrid = isset($params['height_grid']) ? $params['height_grid'] : $this->_getParam('height_grid','160');
    $this->view->width_grid = $defaultWidthGrid = isset($params['width_grid']) ? $params['width_grid'] : $this->_getParam('width_grid','140');
    $this->view->width_pinboard = $defaultWidthPinboard = isset($params['width_pinboard']) ? $params['width_pinboard'] : $this->_getParam('width_pinboard','300');
    $this->view->title_truncation_list = $title_truncation_list = isset($params['title_truncation_list']) ? $params['title_truncation_list'] : $this->_getParam('title_truncation_list', '100');
    $this->view->title_truncation_grid = $title_truncation_grid = isset($params['title_truncation_grid']) ? $params['title_truncation_grid'] : $this->_getParam('title_truncation_grid', '100');
    $this->view->title_truncation_pinboard = $title_truncation_pinboard = isset($params['title_truncation_pinboard']) ? $params['title_truncation_pinboard'] : $this->_getParam('title_truncation_pinboard', '100');
    $this->view->description_truncation_list = $description_truncation_list = isset($params['description_truncation_list']) ? $params['description_truncation_list'] : $this->_getParam('description_truncation_list', '100');
    $this->view->description_truncation_grid = $description_truncation_grid = isset($params['description_truncation_grid']) ? $params['description_truncation_grid'] : $this->_getParam('description_truncation_grid', '100');
    $this->view->description_truncation_pinboard = $description_truncation_pinboard = isset($params['description_truncation_pinboard']) ? $params['description_truncation_pinboard'] : $this->_getParam('description_truncation_pinboard', '100');
    $text =  isset($searchArray['search']) ? $searchArray['search'] : (!empty($params['search']) ? $params['search'] : (isset($_GET['search']) && ($_GET['search'] != '') ? $_GET['search'] : ''));
    //search data
    $value['category_id'] =  isset($searchArray['category_id']) ? $searchArray['category_id'] : (isset($_GET['category_id']) ? $_GET['category_id'] : (isset($params['category_id']) ? $params['category_id'] : ''));
    $value['sort'] = isset($searchArray['sort']) ? $searchArray['sort'] : (isset($_GET['sort']) ? $_GET['sort'] : (isset($params['sort']) ? $params['sort'] : $this->_getParam('sort', 'mostSPliked')));
    $value['subcat_id'] = isset($searchArray['subcat_id']) ? $searchArray['subcat_id'] :  (isset($_GET['subcat_id']) ? $_GET['subcat_id'] : (isset($params['subcat_id']) ? $params['subcat_id'] : ''));
    $value['subsubcat_id'] = isset($searchArray['subsubcat_id']) ? $searchArray['subsubcat_id'] : (isset($_GET['subsubcat_id']) ? $_GET['subsubcat_id'] : (isset($params['subsubcat_id']) ? $params['subsubcat_id'] : ''));    
    $value['alphabet'] = isset($_GET['alphabet']) ? $_GET['alphabet'] : (isset($params['alphabet']) ? $params['alphabet'] : '');
    $value['tag'] = isset($_GET['tag_id']) ? $_GET['tag_id'] : (isset($params['tag_id']) ? $params['tag_id'] : '');
    $show_criterias = isset($params['show_criterias']) ? $params['show_criterias'] : $this->_getParam('show_criteria', array('like', 'comment', 'rating', 'by', 'title', 'featuredLabel', 'sponsoredLabel', 'watchLater', 'category', 'description_list','description_grid','description_pinboard', 'duration', 'hotLabel', 'favouriteButton', 'listAdd', 'likeButton', 'socialSharing', 'view'));
    $value['location'] = isset($searchArray['location']) ? $searchArray['location'] :  (isset($_GET['location']) ? $_GET['location'] : (isset($params['location']) ?  $params['location'] : ''));
    $value['lat'] = isset($searchArray['lat']) ? $searchArray['lat'] :  (isset($_GET['lat']) ? $_GET['lat'] : (isset($params['lat']) ?  $params['lat'] : ''));
    $value['show'] = isset($searchArray['show']) ? $searchArray['show'] :  (isset($_GET['show']) ? $_GET['show'] : (isset($params['show']) ?  $params['show'] : ''));
    $value['lng'] = isset($searchArray['lng']) ? $searchArray['lng'] :  (isset($_GET['lng']) ? $_GET['lng'] : (isset($params['lng']) ?  $params['lng'] : ''));
    $value['miles'] = isset($searchArray['miles']) ? $searchArray['miles'] :  (isset($_GET['miles']) ? $_GET['miles'] : (isset($params['miles']) ?  $params['miles'] : ''));
    $value['user_id'] = isset($_GET['user_id']) ? $_GET['user_id'] : (isset($params['user_id']) ?  $params['user_id'] : '');
    if(is_array($show_criterias)){
      foreach ($show_criterias as $show_criteria)
      $this->view->{$show_criteria . 'Active'} = $show_criteria;
    }
    $this->view->bothViewEnable = false;
    if (!$is_ajax) {
      $this->view->optionsEnable = $optionsEnable = $this->_getParam('enableTabs', array('list', 'grid', 'pinboard'));
      $view_type = $this->_getParam('openViewType', 'list');
      if (count($optionsEnable) > 1) {
	$this->view->bothViewEnable = true;
      }
    }
    $this->view->limit_data_pinboard = $limit_data_pinboard = isset($params['limit_data_pinboard']) ? $params['limit_data_pinboard'] : $this->_getParam('limit_data_pinboard', '10');
    $this->view->limit_data_grid = $limit_data_grid = isset($params['limit_data_grid']) ? $params['limit_data_grid'] : $this->_getParam('limit_data_grid', '10');
    $this->view->limit_data_list = $limit_data_list = isset($params['limit_data_list']) ? $params['limit_data_list'] : $this->_getParam('limit_data_list', '10');
    $this->view->view_type = $view_type = (isset($_POST['type']) ? $_POST['type'] : (isset($params['view_type']) ? $params['view_type'] : $view_type));
    $this->view->viewTypeStyle = $viewTypeStyle = (isset($_POST['viewTypeStyle']) ? $_POST['viewTypeStyle'] : (isset($params['viewTypeStyle']) ? $params['viewTypeStyle'] : $this->_getParam('viewTypeStyle','fixed')));
    $limit_data = $this->view->{'limit_data_'.$view_type};
    $params = array('blog_parent_id' => $id, 'height_list' => $defaultHeightList, 'width_list' => $defaultWidthList,'height_grid' => $defaultHeightGrid, 'width_grid' => $defaultWidthGrid,'width_pinboard' => $defaultWidthPinboard,'limit_data_pinboard' => $limit_data_pinboard,'limit_data_list'=>$limit_data_list,'limit_data_grid'=>$limit_data_grid,'pagging' => $loadOptionData, 'show_criterias' => $show_criterias, 'view_type' => $view_type,  'description_truncation_list' => $description_truncation_list, 'title_truncation_list' => $title_truncation_list, 'title_truncation_grid' => $title_truncation_grid,'title_truncation_pinboard'=>$title_truncation_pinboard,'description_truncation_grid'=>$description_truncation_grid,'description_truncation_pinboard'=>$description_truncation_pinboard,'category_id' => $value['category_id'], 'subcat_id' => $value['subcat_id'], 'subsubcat_id' => $value['subsubcat_id'], 'sort' => $value['sort'], 'tag' => $value['tag'],'location'=>$value['location'],'show'=>$value['show'],'lat'=>$value['lat'],'lng'=>$value['lng'] ,'miles'=>$value['miles'],'user_id'=>$value['user_id'],'viewTypeStyle'=>$viewTypeStyle );
    $this->view->loadMoreLink = $this->_getParam('openTab') != NULL ? true : false;
    $this->view->loadJs = true;
    // custom list grid view options
    $this->view->can_create = Engine_Api::_()->authorization()->isAllowed('sesblog_blog', null, 'create');
    if (!$is_ajax) {
      // Make form	
      if (!empty($_GET['tag_id'])) {
        $this->view->tag = Engine_Api::_()->getItem('core_tag', $_GET['tag_id'])->text;
      }
    }
    $this->view->category = $value['category_id'];
    $this->view->text = $value['text']  = stripslashes($text);
    if (isset($value['sort']) && $value['sort'] != '') {
      $value['getParamSort'] = str_replace('SP', '_', $value['sort']);
    } else
      $value['getParamSort'] = 'creation_date';
    if (isset($value['getParamSort'])) {
      switch ($value['getParamSort']) {
        case 'most_viewed':
          $value['popularCol'] = 'view_count';
          break;
        case 'most_liked':
          $value['popularCol'] = 'like_count';
          break;
        case 'most_commented':
          $value['popularCol'] = 'comment_count';
          break;
        case 'sponsored':
          $value['popularCol'] = 'is_sponsored';
	  $value['fixedData'] = 'is_sponsored';
          break;
	case 'featured':
          $value['popularCol'] = 'is_featured';
	  $value['fixedData'] = 'is_featured';
          break;
        case 'most_rated':
          $value['popularCol'] = 'rating';
          break;
        case 'recently_created':
        default:
          $value['popularCol'] = 'creation_date';
          break;
      }
    }
    $value['status'] = 1;
    $value['search'] = 1;
    $this->view->widgetName = 'profile-blogs';	
    $this->view->profile_blogs = true;	
    $viewer = Engine_Api::_()->user()->getViewer();
    $values = array();
    $params = array_merge($params, $value,array('search'=>addslashes($text)));
    $values = array_merge($_GET, $values);
    $this->view->formValues = array_filter($values);
    $values['draft'] = "0";
    $values['visible'] = "1";
    //End Work

    // Get paginator
    $blogTable = Engine_Api::_()->getItemTable('sesblog_blog');
    $blogTableName = $blogTable->info('name');
    $blogMapTable = Engine_Api::_()->getDbTable('mapevents', 'sesblog');
    $blogMapTableName = $blogMapTable->info('name');
    $selectBlogTable = $blogTable->select()
			->setIntegrityCheck(false)
			->from($blogTableName)
		        ->join($blogMapTableName, "$blogMapTableName.blog_id = $blogTableName.blog_id", null)
		        ->where($blogMapTableName.'.event_id =? and '. $blogMapTableName.'.mapevent_id IS NOT NULL', $event_id)
		        ->where($blogMapTableName.'.approved =?', 1)
		        ->group($blogTableName.'.blog_id');
    $this->view->paginator  = $paginator = Zend_Paginator::factory($selectBlogTable);
        // Set item count per page and current page number
    $paginator->setItemCountPerPage(10);
    $this->view->page = $page;
    $paginator->setCurrentPageNumber($page);
    unset($params['text']);
    $this->view->params = $params;
    if ($is_ajax) {
      $this->getElement()->removeDecorator('Container');
    } else {
      // Do not render if nothing to show
      if ($paginator->getTotalItemCount() <= 0) {
        //silence
      }
    }
  }

  public function getChildCount() {
    return $this->_childCount;
  }

}
