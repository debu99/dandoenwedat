<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedcomment
 * @package    Sesadvancedcomment
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: gallery.tpl 2017-01-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

?>
<?php include APPLICATION_PATH .  '/application/modules/Sesadvancedactivity/views/scripts/dismiss_message.tpl';?>
<script type="text/javascript">
function multiDelete()
{
  return confirm("<?php echo $this->translate("Are you sure you want to delete the selected galleries ?") ?>");
}
function selectAll()
{
  var i;
  var multidelete_form = $('multidelete_form');
  var inputs = multidelete_form.elements;
  for (i = 1; i < inputs.length; i++) {
    if (!inputs[i].disabled) {
      inputs[i].checked = inputs[0].checked;
    }
  }
}
</script>
<div class='sesbasic_admin_form'>
 <div>
    <?php if( count($this->subnavigation) ): ?>
      <div class='sesbasic-admin-sub-tabs'>
        <?php echo $this->navigation()->menu()->setContainer($this->subnavigation)->render();?>
      </div>
    <?php endif; ?>
  </div>
</div>
<div class='sesbasic_admin_form'>
 <div>
    <?php if( count($this->subsubNavigation) ): ?>
      <div class='sesbasic-admin-sub-tabs'>
        <?php echo $this->navigation()->menu()->setContainer($this->subsubNavigation)->render();?>
      </div>
    <?php endif; ?>
  </div>
</div>
<p>Here, you can create new packs and upload stickers in them.</p><br />
<?php if( count($this->paginator) ): ?>
  <form id='multidelete_form' method="post" action="<?php echo $this->url();?>" onSubmit="return multiDelete()"> 
  <?php endif; ?>
  <div>
    <div>
     <div class="sesbasic_search_reasult"><?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesadvancedcomment', 'controller' => 'emotion', 'action' => 'create-gallery'), $this->translate("Create New Pack"), array('class'=>'sesbasic_button fa fa-plus smoothbox')); ?>
</div>
        </div>
        <?php if( count($this->paginator) ): ?>
  <div class="sesbasic_search_reasult">
    <?php echo $this->translate(array('%s pack found.', '%s packs found.', $this->paginator->getTotalItemCount()), $this->locale()->toNumber($this->paginator->getTotalItemCount())) ?>
  </div><?php endif; ?>
        <?php if(count($this->paginator) > 0):?>
        	<table class="admin_table sesadvcomment_sticker_list" style="width:100%;">
          	<thead>
            	<tr>
                <th>
                  <input onclick='selectAll();' type='checkbox' class='checkbox' />
                </th>
                <th style="width:15%;">
                 <?php echo $this->translate("Title") ?>
                </th>
                <th class="admin_table_centered" style="width:40%;">
                	<?php echo $this->translate("Photo") ?>
                </th>
                <th class="admin_table_centered" style="width:10%;">
                	<?php echo $this->translate("Status") ?>
                </th>
                <th style="width:30%;">
                	<?php echo $this->translate("Options"); ?>
                </th>
                </tr>  
            </thead>
          	<tbody>
              <?php foreach ($this->paginator as $item) : ?>
                <tr class="item_label" id="slide_<?php echo $item->getIdentity(); ?>">
                  <td>
                    <input type='checkbox' class='checkbox' name='delete_<?php echo $item->getIdentity();?>' value='<?php echo $item->getIdentity() ?>' />
                  </td>
                  <td class="sesadvcomment_sticker_list_title">
                    <?php echo $item->title ?>
                  </td>
                  <td class="admin_table_centered sesadvcomment_sticker_list_img">
                    <?php $icon = Engine_Api::_()->storage()->get($item->file_id, ''); ?>
                    <?php if($icon) { ?>
                      <img alt="" src="<?php echo Engine_Api::_()->storage()->get($item->file_id, '')->getPhotoUrl(); ?>" />
                  	<?php } else { ?>
                      <?php echo "---"; ?>
                  	<?php } ?>
                  </td>
                  <td class='admin_table_centered'>
                    <?php echo ( $item->enabled ? $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesadvancedcomment', 'controller' => 'emotion', 'action' => 'enabled', 'gallery_id' => $item->getIdentity()), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/images/icons/check.png', '', array('title' => $this->translate('Disabled'))), array()) : $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesadvancedcomment', 'controller' => 'emotion', 'action' => 'enabled', 'gallery_id' => $item->getIdentity()), $this->htmlImage('application/modules/Sesbasic/externals/images/icons/error.png', '', array('title' => $this->translate('Enabled')))) ) ?>
                  </td>
                  <td>
                  	<?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesadvancedcomment', 'controller' => 'emotion', 'action' => 'create-gallery', 'id' => $item->getIdentity()), $this->translate("Edit"), array('class' => 'smoothbox')) ?>
                  	|
                    <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesadvancedcomment', 'controller' => 'emotion', 'action' => 'files', 'gallery_id' => $item->getIdentity()), $this->translate("Manage Stickers"), array()); ?>
              			|
                    <?php echo $this->htmlLink(
                    	array('route' => 'admin_default', 'module' => 'sesadvancedcomment', 'controller' => 'emotion', 'action' => 'delete-gallery', 'id' => $item->getIdentity()),
                    $this->translate("Delete"),
                    array('class' => 'smoothbox')) ?>
                  </td>
                </tr>
              <?php endforeach; ?>
          	</tbody>
          </table>
          <br />
          <div class='buttons'>
          	<button type='submit'><?php echo $this->translate('Delete Selected'); ?></button>
          </div>
        </div>
        <?php else:?>
          <div class="tip">
            <span>
              <?php echo "There are no galleries created by you yet.";?>
            </span>
          </div>
        <?php endif;?>
      </div>
  <br />
  </form>
  <br />
  <div>
    <?php echo $this->paginationControl($this->paginator); ?>
  </div>
