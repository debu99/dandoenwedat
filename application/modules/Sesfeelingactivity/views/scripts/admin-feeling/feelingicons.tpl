<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesfeelingactivity
 * @package    Sesfeelingactivity
 * @copyright  Copyright 2017-2018 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: feelingicons.tpl  2017-08-28 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

?>
<?php include APPLICATION_PATH .  '/application/modules/Sesfeelingactivity/views/scripts/dismiss_message.tpl';?>
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
    var url = '<?php echo $this->url(array('action' => 'order-manage-feelingicons')) ?>';
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
  return confirm("<?php echo $this->translate("Are you sure you want to delete the selected feeling icons?") ?>");
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
<div class="sesbasic_search_reasult">
<?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesfeelingactivity', 'controller' => 'feeling', 'action' => 'index'), $this->translate("<i class='fa fa-long-arrow-alt-left'></i> Back to Manage Categories"), array('class'=>'buttonlink back_manage_cate')); ?>

</div>
<?php if($this->feeling->type == 1) { ?>
  <h3><?php echo $this->translate("Manage Lists for %s category", $this->feeling->title); ?></h3>
  <p><?php echo $this->translate("Below, you can add new feelings or activities for %s category. Users will see this list on selecting this category from the Feeling/Activity option in the status updates box.", $this->feeling->title); ?></p><br />
<?php } else { ?>
  <h3><?php echo $this->translate("Manage Modules for %s category", $this->feeling->title); ?></h3>
  <p><?php echo $this->translate("Below, you can add modules on website to be shown as feelings or activities for %s category. Users will see content from selected modules in auto-suggest box on selecting this category from the Feeling/Activity option in the status updates box.", $this->feeling->title); ?></p><br />
<?php } ?>
 <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesfeelingactivity', 'controller' => 'feeling', 'action' => 'add-feelingicon','feeling_id' => $this->feeling_id, 'type' => $this->type), $this->translate("<i class='fa fa fa-plus'></i> Add New Feeling/Activity List Item"), array('class'=>'buttonlink add_new_feeling smoothbox')); ?>

<?php //echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesfeelingactivity', 'controller' => 'feeling', 'action' => 'upload-zip-file','feeling_id'=>$this->feeling_id), $this->translate("<i class='fa fa fa-plus'></i> Upload Stickers in Zip"), array('class'=>'buttonlink upload_zip_feeling smoothbox')); ?>

<?php if( count($this->paginator) ): ?>
  <form id='multidelete_form' method="post" action="<?php echo $this->url();?>" onSubmit="return multiDelete()"> 
  <?php endif; ?>
        <div>
      
        <?php if( count($this->paginator) ): ?>
        <div class="sesbasic_search_reasult">
          <?php if($this->feeling->type == 1) { ?>
            <?php echo $this->translate(array('%s feeling/activity list item found.', '%s feeling/activity list items found.', $this->paginator->getTotalItemCount()), $this->locale()->toNumber($this->paginator->getTotalItemCount())) ?>
          <?php } else { ?>
            <?php echo $this->translate(array('%s module found.', '%s modules found.', $this->paginator->getTotalItemCount()), $this->locale()->toNumber($this->paginator->getTotalItemCount())) ?>
          <?php } ?>
        </div><?php endif; ?>
        <?php if(count($this->paginator) > 0):?>
        
					<div class="sesfeelingactivity_packs_listing" id='menu_list'>
          	 <?php foreach ($this->paginator as $item): ?>
             	<div class="sesfeelingactivity_packs_list sesfeelingactivity_manage_packs_list" id="managefeelingicons_<?php echo $item->feelingicon_id ?>">
                <input type='hidden'  name='order[]' value='<?php echo $item->feelingicon_id; ?>'>
              	<div>
                	<div class="_input">
                		<input type='checkbox' class='checkbox' name='delete_<?php echo $item->getIdentity();?>' value='<?php echo $item->getIdentity() ?>' />
                  </div>
                  <?php if($item->type == 1){ ?>
                    <div class="_icon">
                    	<img style="width:32px;" alt="" src="<?php echo Engine_Api::_()->storage()->get($item->feeling_icon, '')->getPhotoUrl(); ?>" />
                    </div>
                  <?php } ?>
                  <div class="_cont">
                  	<div class="_title" title="<?php echo $item->title ?>">
                    	<?php echo $item->title ?>
                    </div>
                    <div class="_options">
                      <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesfeelingactivity', 'controller' => 'feeling', 'action' => 'add-feelingicon', 'id' => $item->getIdentity(),'feeling_id'=>$this->feeling_id, 'type' => $this->type), $this->translate("Edit"), array('title'=> $this->translate("Edit"), 'class' => 'smoothbox')) ?>
                      |
                      <?php echo $this->htmlLink(
                          array('route' => 'admin_default', 'module' => 'sesfeelingactivity', 'controller' => 'feeling', 'action' => 'delete-feelingicon', 'id' => $item->getIdentity(), 'type' => $this->type), $this->translate("Delete"), array('title'=> $this->translate("Delete"), 'class' => 'smoothbox')) ?>
                    </div>
                  </div>
                </div>
              </div>
             <?php endforeach; ?>
          </div>
          <div class='buttons'>
          	<button type='submit'><?php echo $this->translate('Delete Selected'); ?></button>
        	</div>
        <?php else: ?>
          <div class="tip">
            <span>
              <?php echo "There are no feeling icon created by you yet.";?>
            </span>
          </div>
        <?php endif;?>
      </div>
  </form>
  <br />
  <div>
    <?php echo $this->paginationControl($this->paginator); ?>
  </div>