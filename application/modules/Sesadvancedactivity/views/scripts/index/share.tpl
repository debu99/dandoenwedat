<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: share.tpl  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/styles/styles.css'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesadvancedactivity/externals/styles/styles.css'); ?>
<div class="sesbasic_share_popup sesbasic_bxs">
  <?php echo $this->form->setAttrib('class', 'global_form_popup')->render($this) ?>
  <br />
  <div class="sharebox">
   <?php if($this->attachment->getType() == 'sesadvancedactivity_event'){ 
      echo $this->partial('_events.tpl','sesadvancedactivity',array('events'=>$this->attachment,'share'=>false));
    }else if (!empty($this->action)){
        $previousAction = '1';
        $previousAttachment = '1';
        $action = $this->action;
        echo "<div class='sesact_feed'><ul class='feed'>";
        include('application/modules/Sesadvancedactivity/views/scripts/_activity.tpl');
        echo "</ul></div>";
    }else{ ?>
    <?php if( $this->attachment->getPhotoUrl() ): ?>
      <div class="sharebox_photo">
        <?php echo $this->htmlLink($this->attachment->getHref(), $this->itemPhoto($this->attachment, 'thumb.icon'), array('target' => '_parent')) ?>
      </div>
    <?php endif; ?>
    <div>
      <div class="sharebox_title">
        <?php echo $this->htmlLink($this->attachment->getHref(), $this->attachment->getTitle(), array('target' => '_parent')) ?>
      </div>
      <div class="sharebox_description">
        <?php 
          if($this->attachment->getType() == 'activity_action' || $this->attachment->getType() == 'sesadvacancedtivity_action') {
            $content =  $this->getContent($this->attachment);
            echo $content[0].': '.$content[1];
          }
          else
            echo $this->attachment->getDescription();
       ?>
      </div>
    </div>
    <?php } ?>
    
  </div>
</div>
<script type="text/javascript">
sesJqueryObject('.sharebox_description > a').attr('href','javascript:;');
sesJqueryObject('.sharebox_description').find('.ses_tooltip').removeClass('ses_tooltip');
//<![CDATA[
var toggleFacebookShareCheckbox, toggleTwitterShareCheckbox;
(function($$) {
  toggleFacebookShareCheckbox = function(){
      $$('span.composer_facebook_toggle').toggleClass('composer_facebook_toggle_active');
      $$('input[name=post_to_facebook]').set('checked', $$('span.composer_facebook_toggle')[0].hasClass('composer_facebook_toggle_active'));
  }
  toggleTwitterShareCheckbox = function(){
      $$('span.composer_twitter_toggle').toggleClass('composer_twitter_toggle_active');
      $$('input[name=post_to_twitter]').set('checked', $$('span.composer_twitter_toggle')[0].hasClass('composer_twitter_toggle_active'));
  }
})($$)
//]]>
</script>