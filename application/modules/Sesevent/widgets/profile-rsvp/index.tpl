<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php
	if(isset($this->nonlogedin)){ ?>
    <div class="sesevent_sidebar_rsvp_block sesbasic_clearfix sesbasic_bxs sesbasic_sidebar_block">
      <div class="sesevent_sidebar_rsvp_block_msg sesbasic_clearfix"><a href="login" class="sesbasic_link_btn floatL">Login</a> <span>to give your RSVP</span></div>
    </div>
<?php 	}else{
?>
<style>
.sesevent_attending<?php echo $this->identity ?>.selected{
	background-color:<?php echo $this->attnbagcolor ?> !important;
	color:<?php echo  $this->attntextColor ?> !important;
}
.sesevent_nattending<?php echo $this->identity ?>.selected{
	background-color:<?php echo $this->nattnbagcolor ?> !important;
	color:<?php echo  $this->nattntextColor ?> !important;
}
.sesevent_mbattending<?php echo $this->identity ?>.selected{
	background-color:<?php echo $this->mbattnbagcolor ?> !important;
	color:<?php echo  $this->mbattntextColor ?> !important;
}
</style>
<?php if ($this->viewer_id): ?>
<?php if(empty($this->noRsvp)){ ?>
  <script type="text/javascript">
    en4.core.runonce.add(function(){
     sesJqueryObject(document).on('click','.sesevent-rsvp-opn',function(e){
			 e.preventDefault();
        var option_id = sesJqueryObject(this).attr('data-url');
				var elem = sesJqueryObject('.sesevent_sidebar_rsvp_options > a');
				for(var i =0;i<elem.length;i++){
					sesJqueryObject(elem[i]).removeClass('selected');	
				}
				sesJqueryObject(this).addClass('selected');	
        new Request.JSON({
            url: '<?php echo $this->url(array('module' => 'sesevent', 'controller' => 'widget', 'action'=>'profile-rsvp', 'subject' => $this->subject()->getGuid()), 'default', true); ?>',
            method: 'post',
            data : {
              format: 'json',
              'event_id': <?php echo $this->subject()->event_id ?>,
              'option_id' : option_id
            },
            onComplete: function(responseJSON, responseText)
            {
              if (responseJSON.error) {
                alert(responseJSON.error);
              }
            }
        }).send();
      });
    });
  </script>
<?php } ?>
  <h3><?php echo $this->translate('Your RSVP');?></h3>
  <div class="sesevent_sidebar_rsvp_block sesbasic_clearfix sesbasic_bxs sesbasic_sidebar_block">
    <div class="sesevent_sidebar_rsvp_options">
      <a class="sesevent-rsvp-opn sesevent_attending<?php echo $this->identity ?> centerT <?php if ($this->rsvp == 2): ?>selected <?php endif; ?>" data-url="2" title="<?php echo $this->translate('Attending');?>" href="javascript:void(0);"><?php echo $this->translate('Attending');?></a>
      <a class="sesevent-rsvp-opn sesevent_mbattending<?php echo $this->identity ?> centerT <?php if ($this->rsvp == 1): ?>selected <?php endif; ?>" data-url="1" title="<?php echo $this->translate('Maybe Attending');?>" href="javascript:void(0);"><?php echo $this->translate('Maybe');?></a>
      <a class="sesevent-rsvp-opn sesevent_nattending<?php echo $this->identity ?> centerT <?php if ($this->rsvp == 0): ?>selected <?php endif; ?>" data-url="0" title="<?php echo $this->translate('Not Attending');?>" href="javascript:void(0);"><?php echo $this->translate('Not');?></a>
    </div>
	</div>
<?php endif; ?>
<?php } ?>