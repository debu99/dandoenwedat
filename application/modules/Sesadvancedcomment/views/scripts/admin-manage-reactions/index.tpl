<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedcomment
 * @package    Sesadvancedcomment
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl 2017-01-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

?>
<?php include APPLICATION_PATH .  '/application/modules/Sesadvancedactivity/views/scripts/dismiss_message.tpl';?>
<script type="text/javascript">
function multiDelete()
{
  return confirm("<?php echo $this->translate("Are you sure you want to delete the selected reactions ?") ?>");
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
<h3>Manage Reactions</h3>
<p>Here, you can manage reactions for the feeds and content on your website. You can edit, delete or create new reactions from below.</p><br />
<?php if( count($this->paginator) ): ?>
  <form id='multidelete_form' method="post" action="<?php echo $this->url();?>" onSubmit="return multiDelete()"> 
  <?php endif; ?>
  <div>
    <div>
     <div class="sesbasic_search_reasult"><?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesadvancedcomment', 'controller' => 'manage-reactions', 'action' => 'add-reaction'), $this->translate("Add a New Reaction"), array('class'=>'sesbasic_button fa fa-plus smoothbox')); ?>
</div>
        </div>
        <?php if( count($this->paginator) ): ?>
  <div class="sesbasic_search_reasult">
    <?php echo $this->translate(array('%s reaction found.', '%s reactions found.', $this->paginator->getTotalItemCount()), $this->locale()->toNumber($this->paginator->getTotalItemCount())) ?>
  </div><?php endif; ?>
        <?php if(count($this->paginator) > 0):?>
        	<table class="admin_table sesadvcomment_sticker_list" style="width:50%;">
          	<thead>
            	<tr>
<!--                <th>
                  <input onclick='selectAll();' type='checkbox' class='checkbox' />
                </th>-->
                <th>
                 <?php echo $this->translate("Name") ?>
                </th>
                <th class="admin_table_centered">
                 <?php echo $this->translate("Photo") ?>
                </th>   
                <!--<th class="admin_table_centered"><?php //echo $this->translate("Status") ?></th>-->
                <th>
                 <?php echo $this->translate("Options"); ?>
                </th>  
              </tr>
            </thead>  
          	<tbody>
              <?php foreach ($this->paginator as $item) : ?>
                <tr class="item_label" id="slide_<?php echo $item->getIdentity(); ?>">
<!--                  <td>
                    <input type='checkbox' class='checkbox' name='delete_<?php //echo $item->getIdentity();?>' value='<?php //echo $item->getIdentity() ?>' />
                  </td>-->
                  <td class="sesadvcomment_sticker_list_title">
                    <?php echo $item->title ?>
                  </td>
                  <td class="admin_table_centered sesadvcomment_sticker_list_img">
                  	<img alt="" src="<?php echo Engine_Api::_()->storage()->get($item->file_id, '') ? Engine_Api::_()->storage()->get($item->file_id, '')->getPhotoUrl() : ""; ?>" />
                  </td>
                  <?php if(0): ?>
                    <?php if($item->reaction_id != 1): ?>
                      <td class="admin_table_centered">
                        <?php if($item->enabled == 1):?>
                          <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesadvancedcomment', 'controller' => 'manage-reactions', 'action' => 'status', 'id' => $item->reaction_id), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/images/icons/check.png', '', array('title'=> $this->translate('Disable')))) ?>
                        <?php else: ?>
                          <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesadvancedcomment', 'controller' => 'manage-reactions', 'action' => 'status', 'id' => $item->reaction_id), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/images/icons/error.png', '', array('title'=> $this->translate('Enable')))) ?>
                        <?php endif; ?>
                      </td>
                    <?php else: ?>
                    <td class="admin_table_centered">
                      <?php echo "---"; ?>
                    </td>
                    <?php endif; ?>
                  <?php endif; ?>
                  <td>          
                  	<?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesadvancedcomment', 'controller' => 'manage-reactions', 'action' => 'add-reaction', 'id' => $item->getIdentity()), $this->translate("Edit"), array('class' => 'smoothbox')) ?>
                    <?php if($item->reaction_id != 1): ?>
                    |
                    <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesadvancedcomment', 'controller' => 'manage-reactions', 'action' => 'delete-reaction', 'id' => $item->getIdentity()), $this->translate("Delete"), array('class' => 'smoothbox')); ?>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
          	</tbody>
        	</table>
          <br />
<!--          	<div class='buttons'>
            <button type='submit'><?php //echo $this->translate('Delete Selected'); ?></button>
          </div>
          </div>-->
        <?php else:?>
          <div class="tip">
            <span>
              <?php echo "There are no reaction added by you yet.";?>
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
