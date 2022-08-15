<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Seseventreview
 * @package    Seseventreview
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: review-settings.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php include APPLICATION_PATH .  '/application/modules/Sesevent/views/scripts/dismiss_message.tpl';?>
<div class='sesbasic-form sesbasic-categories-form'>
  <div>
    <?php if( count($this->subNavigation) ): ?>
      <div class='sesbasic-admin-sub-tabs'>
        <?php echo $this->navigation()->menu()->setContainer($this->subNavigation)->render();?>
      </div>
    <?php endif; ?>
    <div class='sesbasic-form-cont'>
    <div class='clear'>
		  <div class='settings sesbasic_admin_form'>
		    <?php echo $this->form->render($this); ?>
		  </div>
		</div>
		</div>
  </div>
</div>
<style type="text/css">
.sesbasic_back_icon{
  background-image: url(<?php echo $this->layout()->staticBaseUrl ?>application/modules/Core/externals/images/back.png);
}
</style>
<script>  
  window.addEvent('domready', function() {
    showEditor("<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview.review.summary', 1) ?>");
	allowReview("<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview.allow.review', 1) ?>");
  });
  
function showEditor(value) {
  if(value == 1) {
    if($('seseventreview_show_tinymce-wrapper'))
      $('seseventreview_show_tinymce-wrapper').style.display = 'block';
  } else {
    if($('seseventreview_show_tinymce-wrapper'))
    $('seseventreview_show_tinymce-wrapper').style.display = 'none';
  }
  
}
function allowReview(value) {
  if(value == 1) {
    if($('seseventreview_allow_owner-wrapper'))
      $('seseventreview_allow_owner-wrapper').style.display = 'block';
	if($('seseventreview_show_pros-wrapper'))
      $('seseventreview_show_pros-wrapper').style.display = 'block';
	if($('seseventreview_show_cons-wrapper'))
      $('seseventreview_show_cons-wrapper').style.display = 'block';
	if($('seseventreview_review_title-wrapper'))
      $('seseventreview_review_title-wrapper').style.display = 'block';
	if($('seseventreview_review_summary-wrapper'))
      $('seseventreview_review_summary-wrapper').style.display = 'block';
	if($('seseventreview_show_tinymce-wrapper'))
      $('seseventreview_show_tinymce-wrapper').style.display = 'block';
	if($('seseventreview_show_recommended-wrapper'))
      $('seseventreview_show_recommended-wrapper').style.display = 'block';
	if($('seseventreview_allow_share-wrapper'))
      $('seseventreview_allow_share-wrapper').style.display = 'block';
	if($('seseventreview_show_report-wrapper'))
      $('seseventreview_show_report-wrapper').style.display = 'block';
	showEditor("<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview.review.summary', 1) ?>");
  } else {
    if($('seseventreview_allow_owner-wrapper'))
      $('seseventreview_allow_owner-wrapper').style.display = 'none';
	if($('seseventreview_show_pros-wrapper'))
      $('seseventreview_show_pros-wrapper').style.display = 'none';
	if($('seseventreview_show_cons-wrapper'))
      $('seseventreview_show_cons-wrapper').style.display = 'none';
	if($('seseventreview_review_title-wrapper'))
      $('seseventreview_review_title-wrapper').style.display = 'none';
	if($('seseventreview_review_summary-wrapper'))
      $('seseventreview_review_summary-wrapper').style.display = 'none';
	if($('seseventreview_show_tinymce-wrapper'))
      $('seseventreview_show_tinymce-wrapper').style.display = 'none';
	if($('seseventreview_show_recommended-wrapper'))
      $('seseventreview_show_recommended-wrapper').style.display = 'none';
	if($('seseventreview_allow_share-wrapper'))
      $('seseventreview_allow_share-wrapper').style.display = 'none';
	if($('seseventreview_show_report-wrapper'))
      $('seseventreview_show_report-wrapper').style.display = 'none';
	showEditor(0);
  }
}
</script>