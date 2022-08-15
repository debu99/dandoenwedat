<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Activity.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvancedactivity_View_Helper_Activity extends Zend_View_Helper_Abstract
{
  public function activity(Sesadvancedactivity_Model_Action $action = null, array $data = array(), $method = null, $show_all_comments = false)
  {
    if( null === $action )
    {
      return '';
    }
    
    $viewer = Engine_Api::_()->user()->getViewer();
    $activity_moderate = Engine_Api::_()->getDbtable('permissions', 'authorization')
        ->getAllowed('user', $viewer, 'activity');
    $form = new Sesadvancedactivity_Form_Comment();
    $data = array_merge($data, array(
      'actions' => array($action),
      'commentForm' => $form,
      'user_limit' => Engine_Api::_()->getApi('settings', 'core')->getSetting('activity_userlength'),
      'allow_delete' => Engine_Api::_()->getApi('settings', 'core')->getSetting('activity_userdelete'),
      'activity_moderate' =>$activity_moderate,
      'viewAllComments' => $show_all_comments,
      'ulInclude'=> empty($data['ulInclude']) ? true : false,
      'onlyComment'=> empty($data['onlyComment']) ? true : false,
      'userphotoalign' => !empty($data['userphotoalign']) ? $data['userphotoalign'] : 'left',
      
    ));
    if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sespage')){
      $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
      $module = Engine_Api::_()->getDbTable('actionTypes','sesadvancedactivity')->getActionType($action->type);
      $moduleName = $module->module;
      if($moduleName != "sespage" && $action->object_type != "sespage_page"){}else{
        if($viewer->getIdentity() && $view->subject() && $view->subject()->getType() == "sespage_page"){
            if($view->subject()){
                  if(Engine_Api::_()->getDbTable('pageroles','sespage')->toCheckUserPageRole($viewer->getIdentity(),$view->subject()->getIdentity(),'manage_dashboard','delete')){
                    $attributionType = Engine_Api::_()->getDbTable('postattributions','sespage')->getPagePostAttribution(array('page_id' => $view->subject()->getIdentity()));        
                    $pageAttributionType = Engine_Api::_()->authorization()->isAllowed('sespage_page', $viewer, 'page_attribution');
                    $allowUserChoosePageAttribution = Engine_Api::_()->authorization()->isAllowed('sespage_page', $viewer, 'page_attribution_allowuser');
                    if($pageAttributionType && $allowUserChoosePageAttribution && $attributionType == 1) {
                       $data['isPageSubject'] = $view->subject();
                    }
                  }            
            }
        }
      }
    }
    
    if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesbusiness')){
      $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
      $module = Engine_Api::_()->getDbTable('actionTypes','sesadvancedactivity')->getActionType($action->type);
      $moduleName = $module->module;
      if($moduleName != "sesbusiness" && $action->object_type != "businesses"){}else{
        if($viewer->getIdentity() && $view->subject() && $view->subject()->getType() == "businesses"){
            if($view->subject()){
                  if(Engine_Api::_()->getDbTable('businessroles','sesbusiness')->toCheckUserBusinessRole($viewer->getIdentity(),$view->subject()->getIdentity(),'manage_dashboard','delete')){
                    $attributionType = Engine_Api::_()->getDbTable('postattributions','sesbusiness')->getBusinessPostAttribution(array('business_id' => $view->subject()->getIdentity()));        
                    $pageAttributionType = Engine_Api::_()->authorization()->isAllowed('businesses', $viewer, 'seb_attribution');
                    $allowUserChoosePageAttribution = Engine_Api::_()->authorization()->isAllowed('businesses', $viewer, 'seb_attribution_allowuser');
                    if($pageAttributionType && $allowUserChoosePageAttribution && $attributionType == 1) {
                       $data['isPageSubject'] = $view->subject();
                    }
                  }            
            }
        }
      }
    }
    
    if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesgroup')){
      $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
      $module = Engine_Api::_()->getDbTable('actionTypes','sesadvancedactivity')->getActionType($action->type);
      $moduleName = $module->module;
      if($moduleName != "sesgroup" && $action->object_type != "sesgroup_group"){}else{
        if($viewer->getIdentity() && $view->subject() && $view->subject()->getType() == "sesgroup_group"){
            if($view->subject()){
                  if(Engine_Api::_()->getDbTable('grouproles','sesgroup')->toCheckUserGroupRole($viewer->getIdentity(),$view->subject()->getIdentity(),'manage_dashboard','delete')){
                    $attributionType = Engine_Api::_()->getDbTable('postattributions','sesgroup')->getGroupPostAttribution(array('group_id' => $view->subject()->getIdentity()));        
                    $pageAttributionType = Engine_Api::_()->authorization()->isAllowed('sesgroup_group', $viewer, 'seg_attribution');
                    $allowUserChoosePageAttribution = Engine_Api::_()->authorization()->isAllowed('sesgroup_group', $viewer, 'seg_attribution_allowuser');
                    if($pageAttributionType && $allowUserChoosePageAttribution && $attributionType == 1) {
                       $data['isPageSubject'] = $view->subject();
                    }
                  }            
            }
        }
      }
    }
    if($method == 'update'){
     if(!Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedcomment')){
      return $this->view->partial(
        '_activityComments.tpl',
        'sesadvancedactivity',
        $data
      );
     }else{
       $type = !empty($data['type']) ? $data['type'] : '';
       // If has a page, display oldest to newest
        if( null !== ( @$page = $data['page']) ) {
          $comments = $action->getComments('0',$page,$type);
          $data['comments'] = $comments;
          $data['page'] = $page;
        } else {
          // If not has a page, show the
          $comments = $action->getComments(0,'zero',$type);
          $data['comments'] = $comments;
          $data['page'] = 0;
        }
       
        return $this->view->partial(
        '_activityComments.tpl',
        'sesadvancedcomment',
        $data
      );
       
     }
    }
    else{
      return $this->view->partial(
        '_activityText.tpl',
        'sesadvancedactivity',
        $data
        );
      }
    }
}