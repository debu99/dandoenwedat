<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedcomment
 * @package    Sesadvancedcomment
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: _estore_content.tpl 2017-01-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
  
?>
<style>
.feed_item_date ul{width:100%;}
.sespage_switcher_active{background-color:red;}
</style>
<?php if(!$this->viewer()->getIdentity()) return; ?>
<?php $isPageSubject = empty($this->isPageSubject) ? $this->viewer() : $this->isPageSubject; ?>
<?php $action = $this->action; 
      $module = Engine_Api::_()->getDbTable('actionTypes','sesadvancedactivity')->getActionType($action->type);
      $moduleName = $module->module;
      if($moduleName != "estore" && $action->object_type != "stores"){
        return;
      }
?>
<?php 
      $subjectPage = $this->subject();
      if($subjectPage && empty($this->isPageSubject)){
        if(Engine_Api::_()->getDbTable('storeroles','estore')->toCheckUserStoreRole($this->viewer()->getIdentity(),$subjectPage->getIdentity(),'manage_dashboard','delete')){
          $attributionType = Engine_Api::_()->getDbTable('postattributions','estore')->getStorePostAttribution(array('store_id' => $subjectPage->getIdentity()));
          $pageAttributionType = Engine_Api::_()->authorization()->isAllowed('stores', $viewer, 'page_attribution');
          $allowUserChoosePageAttribution = Engine_Api::_()->authorization()->isAllowed('stores', $this->viewer(), 'page_attribution_allowuser');
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
<li class="estore_switcher_cnt sesact_owner_selector sesact_owner_selector_c">
  <a href="javascript:;" class="estore_feed_change_option_a _st" data-subject="<?php echo !empty($isPageSubject) ? $isPageSubject->getGuid() : $this->viewer()->getGuid(); ?>" data-actionid="<?php echo $action->getIdentity(); ?>" data-rel="<?php echo $isPageSubject->getGuid(); ?>" data-src="<?php echo $isPageSubject->getPhotoUrl(); ?>">
    <img class="estore_elem_cnt" src="<?php echo $isPageSubject->getPhotoUrl(); ?>" />
    <i class="fa fa-caret-down estore_elem_cnt"></i>
  </a>
  <a href="javascript:;" class="estore_feed_change_option _lin" style="left:0; top:0; height:100%; width:100%; position:absolute;"></a>
</li>
<script type="application/javascript">
en4.core.runonce.add(function() {
    if(typeof changePageCommentUser == "function"){
      changePageCommentUser(<?php echo $action->getIdentity() ?>);
    }
});
  
</script>