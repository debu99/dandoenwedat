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
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesblog/externals/styles/styles.css'); ?>

<?php if((!$this->is_ajax) && $this->canCreate):?>
  <li>
    <a href="<?php echo $this->url(array('action' => 'create', 'parent_type' => 'sesevent_blog', 'event_id' => $this->event_id), 'sesblog_general', 'true');?>" class="buttonlink icon_sesblog_new menu_sesblog_quick sesblog_quick_create"><?php echo $this->translate('Write New Entry');?></a>
  </li>
<?php endif;?>

<?php include APPLICATION_PATH . '/application/modules/Sesblog/views/scripts/_showBlogListGrid.tpl'; ?>

<?php if(!$this->is_ajax){ ?>
<script type="application/javascript">
var tabId_pB = <?php echo $this->identity; ?>;
window.addEvent('domready', function() {
	tabContainerHrefSesbasic(tabId_pB);	
});
</script>
<?php } ?>