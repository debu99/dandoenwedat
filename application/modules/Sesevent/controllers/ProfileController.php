<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: ProfileController.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_ProfileController extends Core_Controller_Action_Standard {
  public function init() {
    // @todo this may not work with some of the content stuff in here, double-check
    $subject = null;
    if (!Engine_Api::_()->core()->hasSubject() &&
            ($id = $this->_getParam('id'))) {
		$event_id = Engine_Api::_()->getDbtable('events', 'sesevent')->getEventId($id);
    if ($event_id) {
      $event = Engine_Api::_()->getItem('sesevent_event', $event_id);
      if ($event)
        Engine_Api::_()->core()->setSubject($event);
      else
        return $this->_forward('requireauth', 'error', 'core');
    }else
      return $this->_forward('requireauth', 'error', 'core');
		}

    $this->_helper->requireSubject();
    $this->_helper->requireAuth()->setNoForward()->setAuthParams(
            $subject, Engine_Api::_()->user()->getViewer(), 'view'
    );
  }
  public function indexAction() {
    $subject = Engine_Api::_()->core()->getSubject();
    $viewer = Engine_Api::_()->user()->getViewer();
		if((!$subject->is_approved || !$subject->draft || $subject->is_delete == 1) && ($viewer->getIdentity() != $subject->getOwner()->getIdentity() && !$viewer->isAdmin())){
			return $this->_forward('notfound', 'error', 'core');
		}

    if (!$this->_helper->requireAuth()->setAuthParams($subject, $viewer, 'view')->isValid()) {
      return;
    }

    // Check block
    if ($viewer->isBlockedBy($subject)) {
      return $this->_forward('requireauth', 'error', 'core');
    }

    //Privacy: networks and member level based
    if (Engine_Api::_()->authorization()->isAllowed('sesevent_event', $subject->getOwner(), 'allow_levels') || Engine_Api::_()->authorization()->isAllowed('sesevent_event', $subject->getOwner(), 'allow_networks')) {
        $returnValue = Engine_Api::_()->sesevent()->checkPrivacySetting($subject->getIdentity());
        if ($returnValue == false) {
            return $this->_forward('requireauth', 'error', 'core');
        }
    }

    // Increment view count
    if (!$subject->getOwner()->isSelf($viewer)) {
      $subject->view_count++;
      $subject->save();
    }

    /* Insert data for recently viewed widget */
    if ($viewer->getIdentity() != 0) {
      $dbObject = Engine_Db_Table::getDefaultAdapter();
      $dbObject->query('INSERT INTO engine4_sesevent_recentlyviewitems (resource_id, resource_type,owner_id,creation_date ) VALUES ("' . $subject->getIdentity() . '", "'.$subject->getType().'","' . $viewer->getIdentity() . '",NOW())	ON DUPLICATE KEY UPDATE	creation_date = NOW()');
    }
    
    // Get styles
    $table = Engine_Api::_()->getDbtable('styles', 'core');
    $select = $table->select()
            ->where('type = ?', $subject->getType())
            ->where('id = ?', $subject->getIdentity())
            ->limit();
    $row = $table->fetchRow($select);
    if (null !== $row && !empty($row->style)) {
      $this->view->headStyle()->appendStyle($row->style);
    }
		$view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
		$getmodule = Engine_Api::_()->getDbTable('modules', 'core')->getModule('core');
		if (!empty($getmodule->version) && version_compare($getmodule->version, '4.8.8') >= 0){
			$view->doctype('XHTML1_RDFA');
			if($subject->seo_title)
			$view->headTitle($subject->seo_title, 'SET');
			if($subject->seo_keywords)
			$view->headMeta()->appendName('keywords', $subject->seo_keywords);
			if($subject->seo_description)
			$view->headMeta()->appendName('description', $subject->seo_description);
		}

		$view->headLink()->appendStylesheet($view->layout()->staticBaseUrl
								. 'application/modules/Sesevent/externals/styles/styles.css');
		$script =
              "
							sesJqueryObject(document).click(function(event){
								var moreTab = sesJqueryObject(event.target).parent('.more_tab');
								if(moreTab.length == 0){
												sesJqueryObject('.more_tab').removeClass('tab_open').addClass('tab_closed');
								}
							});
";
		$view->headScript()->appendScript($script);
    // Render
		$hayStack = array('1','2','3','4');
		if(isset($_GET['layout']) && in_array($_GET['layout'],$hayStack)){
			$letters = range('a','z');
			$letter = $letters[$_GET['layout']-1];
			$this->_helper->content->setContentName("sesevent_custom_layout".$letter."")->setNoRender()->setEnabled();
		}else{
			$this->_helper->content->setNoRender()->setEnabled();
		}
  }
}
