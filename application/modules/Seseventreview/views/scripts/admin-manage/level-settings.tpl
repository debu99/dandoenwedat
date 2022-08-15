<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Seseventreview
 * @package    Seseventreview
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: level-settings.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
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

<script type="text/javascript">
  $('level_id').addEvent('change', function() {
    var url = '<?php echo $this->url(array("id" => null)) ?>';
    window.location.href = url + '/id/' + this.get('value');
  });
</script>