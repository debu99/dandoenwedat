<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sespwa
 * @package    Sespwa
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl  2018-11-24 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
 ?>
<?php include APPLICATION_PATH .  '/application/modules/Sespwa/views/scripts/dismiss_message.tpl';?>
<script type="text/javascript">
function multiDelete()
{
  return confirm("<?php echo $this->translate("Are you sure you want to delete the selected banners ?") ?>");
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
<h3><?php echo "Manage Banner Slideshows"; ?></h3>
<p>
	<?php echo $this->translate("This page lists all the Banner Slideshows created by you. Here, you can also add and manage any number of banner slideshows on your website. You can place these banners anywhere on your website including the Landing Page and any other widgetized page of your choice.<br /><br />You can add and manage any number of Photo Slides in each banner slideshow. Each photo slide is highly configurable and you can add title, description and additional button to each banner. Use “Create New Banner Slideshow” link below to create new banner slideshow.") ?>	
</p>
<br class="clear" />
<div class="sesbasic_search_reasult">
	<?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sespwa', 'controller' => 'manage-banner', 'action' => 'create-banner'), $this->translate("Create New Banner"), array('class'=>'smoothbox sesbasic_icon_add buttonlink')) ?>
</div>
<?php if( count($this->paginator) ): ?>
  <div class="sesbasic_search_reasult">
    <?php echo $this->translate(array('%s banner found.', '%s banners found', $this->paginator->getTotalItemCount()), $this->locale()->toNumber($this->paginator->getTotalItemCount())) ?>
  </div>
  <form id='multidelete_form' method="post" action="<?php echo $this->url();?>" onSubmit="return multiDelete()">
  <table class='admin_table'>
    <thead>
      <tr>
        <th class='admin_table_short'><input onclick='selectAll();' type='checkbox' class='checkbox' /></th>
        <th class='admin_table_short'>ID</th>
        <th><?php echo $this->translate("Title") ?></th>
        <th><?php echo $this->translate("Number of Photos") ?></th>
        <th><?php echo $this->translate("Creation Date") ?></th>
        <th align="center"><?php echo $this->translate("Status");?></th>
        <th><?php echo $this->translate("Options") ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($this->paginator as $item): ?>
        <tr>
          <td><input type='checkbox' class='checkbox' name='delete_<?php echo $item->banner_id;?>' value='<?php echo $item->banner_id ?>' /></td>
          <td><?php echo $item->banner_id ?></td>
          <td><?php echo $item->banner_name; ?></td>
          <?php $photos = Engine_Api::_()->getDbtable('slides', 'sespwa')->getSlides($item->banner_id, 'show_all'); ?>
          <td><?php echo count($photos); //->getTotalItemCount(); ?></td>
          <td><?php echo $item->creation_date; ?></td>          
          <td class="admin_table_centered"><?php echo ( $item->enabled ? $this->htmlLink(array('route' => 'admin_default', 'module' => 'sespwa', 'controller' => 'manage-banner', 'action' => 'enabled', 'id' => $item->banner_id), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/images/icons/check.png', '', array('title' => $this->translate('Disable'))), array()) : $this->htmlLink(array('route' => 'admin_default', 'module' => 'sespwa', 'controller' => 'manage-banner', 'action' => 'enabled', 'id' => $item->banner_id), $this->htmlImage('application/modules/Sesbasic/externals/images/icons/error.png', '', array('title' => $this->translate('Enable')))) ) ?></td>
          <td>
          	<?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sespwa', 'controller' => 'manage-banner', 'action' => 'manage', 'id' => $item->banner_id), $this->translate("Manage Photos"), array()) ?>
            |
            <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sespwa', 'controller' => 'manage-banner', 'action' => 'create-banner', 'id' => $item->banner_id), $this->translate("Edit"), array('class' => 'smoothbox')) ?>
            |
            <?php echo $this->htmlLink(
                array('route' => 'admin_default', 'module' => 'sespwa', 'controller' => 'manage-banner', 'action' => 'delete-banner', 'id' => $item->banner_id),
                $this->translate("Delete"),
                array('class' => 'smoothbox')) ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <br />

  <div class='buttons'>

    <button type='submit'><?php echo $this->translate("Delete Selected") ?></button>
  </div>
  </form>

  <br />

  <div>
    <?php echo $this->paginationControl($this->paginator); ?>
  </div>

<?php else: ?>
  <br />
  <div class="tip">
    <span>
      <?php echo $this->translate("There are no banners created by you yet.") ?>
    </span>
  </div>
<?php endif; ?>
