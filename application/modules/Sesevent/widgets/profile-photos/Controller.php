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
class Sesevent_Widget_ProfilePhotosController extends Engine_Content_Widget_Abstract {
  protected $_childCount;
  public function indexAction() {

	  // Default param options
    if(isset($_POST['params']))
    $params = json_decode($_POST['params'],true);    
    if(isset($_POST['searchParams']) && $_POST['searchParams'])
    parse_str($_POST['searchParams'], $searchArray);
    $this->view->is_ajax = $value['is_ajax'] = isset($_POST['is_ajax']) ? true : false;
    $value['page'] = isset($_POST['page']) ? $_POST['page'] : 1 ;
    $this->view->album_parent_id =  $id = isset($params['album_parent_id']) ? $params['album_parent_id'] : Zend_Controller_Front::getInstance()->getRequest()->getParam('id', null);
    $value['event_id'] = $this->view->event_id =  $event_id = Engine_Api::_()->getDbtable('events', 'sesevent')->getEventId($id);
    $this->view->event  = $subject = Engine_Api::_()->getItem('sesevent_event', $event_id);
    $value['identityForWidget'] = $this->view->identityForWidget = isset($_POST['identity']) ? $_POST['identity'] : '';
    $this->view->view_type =$view_type =isset($params['view_type']) ? $params['view_type'] : $this->_getParam('view_type', '1');
    $this->view->height = $value['defaultHeight'] =isset($params['height']) ? $params['height'] : $this->_getParam('height', '200');
    
    $this->view->socialshare_enable_plusicon = $value['socialshare_enable_plusicon'] =isset($params['socialshare_enable_plusicon']) ? $params['socialshare_enable_plusicon'] : $this->_getParam('socialshare_enable_plusicon', 1);
    $this->view->socialshare_icon_limit = $value['socialshare_icon_limit'] = isset($params['socialshare_icon_limit']) ? $params['socialshare_icon_limit'] : $this->_getParam('socialshare_icon_limit', 2);
    
    $this->view->width = $value['defaultWidth'] =isset($params['width']) ? $params['width'] : $this->_getParam('width', '200');
    $this->view->limit_data = $value['limit_data'] = isset($params['limit_data']) ? $params['limit_data'] : $this->_getParam('limit_data', '20');
    $this->view->load_content = $value['load_content'] = isset($params['load_content']) ? $params['load_content'] : $this->_getParam('load_content', 'auto_load');	
    $this->view->title_truncation = $value['title_truncation'] = isset($params['title_truncation']) ? $params['title_truncation'] : $this->_getParam('title_truncation', '45');
    $value['show_criterias'] = isset($params['show_criterias']) ? $params['show_criterias'] : $this->_getParam('show_criteria',array('like','comment','by','title','socialSharing','view','photoCount','featured','sponsored','likeButton'));
    $this->view->fixHover = $fixHover = isset($params['fixHover']) ? $params['fixHover'] :$this->_getParam('fixHover', 'fix');
    $this->view->insideOutside =  $insideOutside = isset($params['insideOutside']) ? $params['insideOutside'] : $this->_getParam('insideOutside', 'inside');
		if(count($value['show_criterias']) > 0){
    	foreach($value['show_criterias'] as $show_criteria)
    		$this->view->$show_criteria = $show_criteria;
		}
    if(isset($value['sort']) && $value['sort'] != '')
    $value['getParamSort'] = str_replace('SP','_',$value['sort']);
    else
    $value['getParamSort'] = 'creation_date';
    switch($value['getParamSort']) {
      case 'most_viewed':
        $value['order'] = 'view_count';
        break;
      case 'most_liked':
			$value['order'] = 'like_count';
			break;
					case 'most_commented':
			$value['order'] = 'comment_count';
			break;
					case 'creation_date':
			default:
			$value['order'] = 'creation_date';
			break;
    }
    $this->view->viewer = Engine_Api::_()->user()->getViewer();
    $params = $this->view->params = array('album_parent_id' => $id,'width'=>$value['defaultWidth'],'height'=>$value['defaultHeight'],'limit_data' =>$value['limit_data'],'load_content'=>$value['load_content'],'show_criterias'=>$value['show_criterias'],'title_truncation'=>$value['title_truncation'],'insideOutside'=>$insideOutside,'fixHover'=>$fixHover,'view_type'=>$view_type, 'socialshare_enable_plusicon' => $value['socialshare_enable_plusicon'], 'socialshare_icon_limit' => $value['socialshare_icon_limit']);
    $this->view->paginator = $paginator = Engine_Api::_()->getDbTable('albums', 'sesevent')->getAlbumSelect($value);
    // Set item count per page and current page number
    $paginator->setItemCountPerPage($value['limit_data']);
    $this->view->page = $value['page'] ;
    $paginator->setCurrentPageNumber($value['page']);
    if($value['is_ajax'])
    $this->getElement()->removeDecorator('Container');  
		$viewer = Engine_Api::_()->user()->getViewer();
    $this->view->canUpload = $canUpload = $subject->authorization()->isAllowed(null, 'photo');

    // Do not render if nothing to show and cannot upload
    if ($paginator->getTotalItemCount() <= 0 && !$canUpload) {
      return $this->setNoRender();
    }

    // Add count to title if configured
    if ($paginator->getTotalItemCount() > 0) {
      $this->_childCount = $paginator->getTotalItemCount();
    }
  }
  public function getChildCount() {
    return $this->_childCount;
  }
}
