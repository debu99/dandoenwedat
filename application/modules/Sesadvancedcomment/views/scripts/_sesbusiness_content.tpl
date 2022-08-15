<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedcomment
 * @package    Sesadvancedcomment
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: _sesbusiness_content.tpl 2017-01-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
  
?>
<style>
.feed_item_date ul{width:100%;}
.sespage_switcher_active{background-color:red;}
</style>
<?php 
$action = $this->action;
if(!$action || !$this->viewer()->getIdentity()) return; ?>
<?php $isPageSubject = empty($this->isPageSubject) ? $this->viewer() : $this->isPageSubject; ?>
<?php  
      $module = Engine_Api::_()->getDbTable('actionTypes','sesadvancedactivity')->getActionType($action->type);
      $moduleName = $module->module;
      if($moduleName != "sesbusiness" && $action->object_type != "businesses"){
        return;
      }
?>
<?php 
      $subjectPage = $this->subject();
      if($subjectPage && empty($this->isPageSubject)){
        if(Engine_Api::_()->getDbTable('businessroles','sesbusiness')->toCheckUserBusinessRole($this->viewer()->getIdentity(),$subjectPage->getIdentity(),'manage_dashboard','delete')){
          $attributionType = Engine_Api::_()->getDbTable('postattributions','sesbusiness')->getBusinessPostAttribution(array('business_id' => $subjectPage->getIdentity()));        
          $pageAttributionType = Engine_Api::_()->authorization()->isAllowed('businesses', $viewer, 'seb_attribution');
          $allowUserChoosePageAttribution = Engine_Api::_()->authorization()->isAllowed('businesses', $this->viewer(), 'seb_attribution_allowuser');
          if (!$pageAttributionType || $attributionType == 0) {
            $isPageSubject = $this->viewer();
          }
          if($pageAttributionType && !$allowUserChoosePageAttribution) {
            $isPageSubject = $this->viewer();
          }
          if($pageAttributionType && $allowUserChoosePageAttribution && $attributionType == 1) {
             $isPageSubject = $subjectPage;
          }
        }
      }
?>
<li class="sespage_switcher_cnt sesact_owner_selector sesact_owner_selector_c">
  <a href="javascript:;" class="sesbusiness_feed_change_option_a _st" data-subject="<?php echo !empty($isPageSubject) ? $isPageSubject->getGuid() : $this->viewer()->getGuid(); ?>" data-actionid="<?php echo $action->getIdentity(); ?>" data-rel="<?php echo $isPageSubject->getGuid(); ?>" data-src="<?php echo $isPageSubject->getPhotoUrl(); ?>">
    <img class="sesbusiness_elem_cnt" src="<?php echo $isPageSubject->getPhotoUrl(); ?>" />
    <i class="fa fa-caret-down sespage_elem_cnt"></i>
  </a>
  <a href="javascript:;" class="sesbusiness_feed_change_option _lin" style="left:0; top:0; height:100%; width:100%; position:absolute;"></a>
</li>
<script type="application/javascript">
en4.core.runonce.add(function() {
    if(typeof changePageCommentUser == "function"){
      changePageCommentUser(<?php echo $action->getIdentity() ?>);
    }
});
  
</script>