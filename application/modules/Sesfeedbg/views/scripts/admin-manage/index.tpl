<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesfeedbg
 * @package    Sesfeedbg
 * @copyright  Copyright 2017-2018 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl  2017-08-28 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php include APPLICATION_PATH .  '/application/modules/Sesadvancedactivity/views/scripts/dismiss_message.tpl';?>
<div class='sesbasic_admin_form'>
 <div>
    <?php if( count($this->subnavigation) ): ?>
      <div class='sesbasic-admin-sub-tabs'>
        <?php echo $this->navigation()->menu()->setContainer($this->subnavigation)->render();?>
      </div>
    <?php endif; ?>
  </div>
</div>
<script type="text/javascript">

  var SortablesInstance;

  window.addEvent('load', function() {
    SortablesInstance = new Sortables('menu_list', {
      clone: true,
      constrain: false,
      handle: '.item_label',
      onComplete: function(e) {
        reorder(e);
      }
    });
  });

  
 var reorder = function(e) {
     var menuitems = e.parentNode.childNodes;
     var ordering = {};
     var i = 1;
     for (var menuitem in menuitems)
     {
       var child_id = menuitems[menuitem].id;

       if ((child_id != undefined))
       {
         ordering[child_id] = i;
         i++;
       }
     }
    ordering['format'] = 'json';

    // Send request
    var url = '<?php echo $this->url(array('action' => 'order')) ?>';
    var request = new Request.JSON({
      'url' : url,
      'method' : 'POST',
      'data' : ordering,
      onSuccess : function(responseJSON) {
      }
    });

    request.send();
  }
</script>

<script type="text/javascript">
function multiDelete()
{
  return confirm("<?php echo $this->translate("Are you sure you want to delete the selected feed backgrounds?") ?>");
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
<?php if( count($this->paginator) ): ?>
  <form id='multidelete_form' method="post" action="<?php echo $this->url();?>" onSubmit="return multiDelete()"> 
<?php endif; ?>
  <div>
    <h3><?php echo "Manage Background Images for Status Updates"; ?></h3>
    <p><?php echo $this->translate("This page lists all the background images uploaded by you. You can add new background images individually or in zip folder for multiple images. To reorder the background images, click on and drag them up or down.<br /><br />You can also mark background images as Featured. These Featured images will always show in the status update boxes before other images. We recommend to mark maximum 5 images as Featured, so that users can see other images also. This will be helpful when you have chosen Random order for images to be displayed in the “Advanced Activity Feeds” widget in layout editor.<br /><br />Maximum 5 Featured images will shown in the status update box.") ?></p>
    <br />
    <div>
    <div class="sesbasic_search_reasult">
      <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesfeedbg', 'controller' => 'manage', 'action' => 'create'), $this->translate("<i style='vertical-align:middle' class='fa fa-plus'></i> Upload Image"), array('class'=>'sesbasic_button smoothbox')); ?>
      
      <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesfeedbg', 'controller' => 'manage', 'action' => 'upload-zip-file'), $this->translate("<i style='vertical-align:middle' class='fa fa-plus'></i> Upload Zipped Folder"), array('class'=>'sesbasic_button smoothbox')); ?>
    </div>
  </div>
  <?php if(count($this->paginator) > 0):?>
    <div>
      Select All.<input onclick="selectAll()" type='checkbox' class='checkbox'>
    </div>
  <?php endif; ?>
  <?php if( count($this->paginator) ): ?>
    <div class="sesbasic_search_reasult">
      <?php echo $this->translate(array('%s background image found.', '%s background images found.', $this->paginator->getTotalItemCount()), $this->locale()->toNumber($this->paginator->getTotalItemCount())) ?>
    </div>
  <?php endif; ?>
  <?php if(count($this->paginator) > 0):?>
    <div class="clear">
      <ul class="sesfeedbg_packs_list" id='menu_list'>
        <?php foreach ($this->paginator as $item) : ?>
          <li class="item_label" id="managebackgrounds_<?php echo $item->background_id ?>">
          	<div class="sesfeedbg_packs_item">
              <div class="sesfeedbg_packs_list_input">
                <input type='checkbox' class='checkbox' name='delete_<?php echo $item->background_id;?>' value='<?php echo $item->background_id ?>' />
              </div>
              <div class="sesfeedbg_packs_list_options">
                <?php echo ( $item->featured ? $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesfeedbg', 'controller' => 'manage', 'action' => 'featured', 'background_id' => $this->background_id, 'id' => $item->background_id), '', array('title'=> $this->translate('Remove From Featured'), 'class' => 'fa sesfeedbg_icon_featured')) : $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesfeedbg', 'controller' => 'manage', 'action' => 'featured', 'background_id' => $this->background_id, 'id' => $item->background_id), '', array('title'=> $this->translate('Mark Featured'), 'class' => 'fa sesfeedbg_icon_unfeatured')) ) ?>&nbsp;         
                <?php echo ($item->enabled ? $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesfeedbg', 'controller' => 'manage', 'action' => 'enabled', 'background_id' => $this->background_id, 'id' => $item->background_id), '', array('title' => $this->translate('Disable'), 'class' => 'fa sesfeedbg_icon_enabled')) : $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesfeedbg', 'controller' => 'manage', 'action' => 'enabled', 'background_id' => $this->background_id, 'id' => $item->background_id), '', array('title' => $this->translate('Enable'), 'class' => 'fa sesfeedbg_icon_disabled'))) ?>&nbsp;
                
                <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesfeedbg', 'controller' => 'manage', 'action' => 'create', 'id'=>$item->background_id), '', array('class' => 'smoothbox fa fa-edit', 'title' => $this->translate('Edit'))) ?>&nbsp;

                <?php echo $this->htmlLink(
                array('route' => 'admin_default', 'module' => 'sesfeedbg', 'controller' => 'manage', 'action' => 'delete', 'id' => $item->background_id), '', array('class' => 'smoothbox fa sesfeedbg_icon_delete', 'title' => $this->translate('Delete'))) ?>
              </div>
              <div class="sesfeedbg_packs_list_img">
                <?php $photo = Engine_Api::_()->storage()->get($item->file_id, ''); ?>
                <?php if($photo) { ?>
                  <?php $photo = $photo->map(); ?>
                  <img alt="" src="<?php echo $photo; ?>" />
                <?php } ?>
              </div>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
      <div class='buttons'>
        <button type='submit'><?php echo $this->translate('Delete Selected'); ?></button>
      </div>
    </div>
  <?php else:?>
    <div class="tip">
      <span>
	<?php echo "There are no images added by you.";?>
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
