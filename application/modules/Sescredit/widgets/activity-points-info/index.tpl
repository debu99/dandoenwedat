<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
 ?>

<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/styles/styles.css'); ?>
<?php $languageColumn = 'en';?>
<?php include APPLICATION_PATH .  '/application/modules/Sescredit/views/scripts/_langugae.tpl';?>
<?php if( 1 !== count($languageNameList)):?>
 <?php $languageColumn = $_COOKIE['en4_language'];?>
<?php endif;?>
<?php $randonNumber = $this->widgetId; ?>
<?php if(!$this->is_ajax) { ?>
  <div class="sescredit_activitypoints_info sesbasic_bxs sesbasic_clearfix">
    <?php echo $this->form->setAttrib('class', 'sescredit_activitypoints_info_search')->render($this) ?>
<?php } ?>
    <?php if($this->paginator->getTotalItemCount() > 0):?>
<?php if(!$this->is_ajax) { ?>
      <div class="_pointstable_wrapper">
        <div class="_pointstable">
          <div class="_pointstable_header sesbasic_lbg">
            <div class="_left _label"><?php echo $this->translate("Activity Type");?></div>
            <div class="_right">
              <div class="_label sesbasic_text_hl"><?php echo $this->translate("Credit Points");?></div>
              <div class="sesbasic_text_hl"><?php echo $this->translate("First Time");?></div>
              <div class="sesbasic_text_hl"><?php echo $this->translate("Next Time");?></div>
              <div class="sesbasic_text_hl"><?php echo $this->translate("Max Points/Day");?></div>
              <div class="sesbasic_text_hl"><?php echo $this->translate("Deduction Points");?></div>
            </div>
          </div>
          <div class="_pointstable_content clear" id="activity-info_<?php echo $randonNumber; ?>">
  <?php } ?>
          <?php $count = 1;$oldHeading = '';?>
          <?php foreach($this->paginator as $activity):?>
           <?php if($count == 1 || ($activity->custom_module != $oldHeading)):?>
              <div class="sescredit_module_<?php echo $activity->custom_module;?> _pointstable_content_heading">
                <?php echo ucfirst(!empty($activity->module_title) ? $activity->module_title : $activity->custom_module);?>
              </div>
              <?php $oldHeading = $activity->custom_module;?>
            <?php endif;?>
            <div class="_pointstable_content_item">
              <div class="_action"><?php echo empty($activity->$languageColumn) ? (str_replace(array('(subject)','(object)'),'',$this->translate($this->translate("ADMIN_ACTIVITY_TYPE_".strtoupper($activity->type))))) : $activity->$languageColumn;?></div>
              <div class="_fapoint"><?php if($activity->firstactivity):?><?php echo $activity->firstactivity;?><?php else:?>-<?php endif;?></div>
              <div class="_sapoint"><?php if($activity->nextactivity):?><?php echo $activity->nextactivity;?><?php else:?>-<?php endif;?></div>
              <div class="_dpoint"><?php if($activity->maxperday):?><?php echo $activity->maxperday;?><?php else:?>-<?php endif;?></div>
              <div class="_mapoint"><?php if($activity->deduction):?><?php echo $activity->deduction;?><?php else:?>-<?php endif;?></div>
            </div>
            <?php $count++;?>
          <?php endforeach;?>
          <?php if(!$this->is_ajax): ?>
        </div>
      </div>
      <div class="sesbasic_load_btn" id="view_more_<?php echo $randonNumber;?>" onclick="viewMore_<?php echo $randonNumber; ?>();" >
        <a href="javascript:void(0);" class="sesbasic_animation sesbasic_link_btn" id="feed_viewmore_link_<?php echo $randonNumber; ?>"><i class="fa fa-sync"></i><span><?php echo $this->translate('View More');?></span></a>
      </div>  
      <div class="sesbasic_load_btn sesbasic_view_more_loading_<?php echo $randonNumber;?>" id="loading_image_<?php echo $randonNumber; ?>" style="display: none;">
        <span class="sesbasic_link_btn"><i class="fa fa-spinner fa-spin"></i></span>
      </div>
    </div>
    <?php endif;?>
  <?php else:?>
    <div class="sesbasic_tip clearfix">
      <img src="<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('sescredit_contest_no_photo', 'application/modules/Sescredit/externals/images/no-credit.png'); ?>" alt="" />
      <span class="sesbasic_text_light"><?php echo $this->translate("No credit points found.");?></span>
    </div>
    <?php endif;?>
    <?php if(!$this->is_ajax): ?>
  </div>
 <?php endif;?>
 <script type="text/javascript">
  var fetchLevelSettings =function(obj){
    sesJqueryObject(obj).closest('form').trigger('submit');
  }
</script>
<script type="text/javascript">
  var requestViewMore_<?php echo $randonNumber; ?>;
  var params<?php echo $randonNumber; ?> = <?php echo json_encode($this->params); ?>;
  var identity<?php echo $randonNumber; ?>  = '<?php echo $randonNumber; ?>';
  var page<?php echo $randonNumber; ?> = '<?php echo $this->page + 1; ?>';
  var searchParams<?php echo $randonNumber; ?> ;
  var is_search_<?php echo $randonNumber;?> = 0;
  viewMoreHide_<?php echo $randonNumber; ?>();	
  function viewMoreHide_<?php echo $randonNumber; ?>() {
      if ($('view_more_<?php echo $randonNumber; ?>'))
      $('view_more_<?php echo $randonNumber; ?>').style.display = "<?php echo ($this->paginator->count() == 0 ? 'none' : ($this->paginator->count() == $this->paginator->getCurrentPageNumber() ? 'none' : '' )) ?>";
  }
  function hideRepeatedHeading() {
    var alum = sesJqueryObject('div[class^="sescredit_module_"]');
    var prevousModule = '';
    alum.each(function() {
      var module = sesJqueryObject(this).attr('class').replace(' _pointstable_content_heading','');
      if(prevousModule == module) {
        sesJqueryObject(this).hide();
      }
      prevousModule = module;
    });
    console.log(alum);
  }
  function viewMore_<?php echo $randonNumber; ?> (){
    sesJqueryObject('#view_more_<?php echo $randonNumber; ?>').hide();
    sesJqueryObject('#loading_image_<?php echo $randonNumber; ?>').show(); 
    requestViewMore_<?php echo $randonNumber; ?> = new Request.HTML({
      method: 'post',
      'url': en4.core.baseUrl + "widget/index/mod/sescredit/id/<?php echo $this->widgetId; ?>/name/<?php echo $this->widgetName; ?>",
      'data': {
        format: 'html',
        page: page<?php echo $randonNumber; ?>,    
        params : params<?php echo $randonNumber; ?>, 
        is_ajax : 1,
        is_search:is_search_<?php echo $randonNumber;?>,
        moduleName:"<?php echo $this->module;?>",
        view_more:1,
        searchParams:searchParams<?php echo $randonNumber; ?> ,
        widget_id: '<?php echo $this->widgetId;?>',
      },
      onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
        if($('loading_images_browse_<?php echo $randonNumber; ?>'))
        sesJqueryObject('#loading_images_browse_<?php echo $randonNumber; ?>').remove();
        if($('loadingimgsescontest-wrapper'))
        sesJqueryObject('#loadingimgsescontest-wrapper').hide();
        document.getElementById('activity-info_<?php echo $randonNumber; ?>').innerHTML = document.getElementById('activity-info_<?php echo $randonNumber; ?>').innerHTML + responseHTML;
        hideRepeatedHeading();
        document.getElementById('loading_image_<?php echo $randonNumber; ?>').style.display = 'none';
      }
    });
    requestViewMore_<?php echo $randonNumber; ?>.send();
    return false;
  }
</script>
