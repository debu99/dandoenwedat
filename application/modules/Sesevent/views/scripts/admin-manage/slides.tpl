<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: slides.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php include APPLICATION_PATH .  '/application/modules/Sesevent/views/scripts/dismiss_message.tpl';?>
<script type="application/javascript">

  function multiDelete() {
    return confirm("<?php echo $this->translate('Are you sure that you want to delete this slide? It will not be recoverable after being deleted.');?>");
  }

  function selectAll() {
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
<div class='clear'>
  <div class='settings'>
     <form id='multidelete_form' method="post" action="<?php echo $this->url();?>" onSubmit="return multiDelete()">
      <div>
        <h3><?php echo $this->translate("Manage Slides for Slideshow") ?></h3>
        <p class="description">
          <?php echo $this->translate('This page lists all the slides uploaded by you. Below, you can manage slides which will be shown in the "Slideshow" widget. Here, you can add new slides by using the "Upload New Slide" link, edit and delete them. You can drag the slides vertically and click on "Save Order" button to change their order.'); ?>
        </p>        
        <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesevent', 'controller' => 'manage', 'action' => 'add-slide'), $this->translate('Upload New Slide'), array('class' => 'buttonlink sesbasic_icon_add')) ?><br /><br />        
        <?php if(count($this->slides)>0):?>
	  <div class="sesbasic_manage_table">
	    <div class="sesbasic_manage_table_head">
      <div style="width:5%">
				<input onclick='selectAll();' type='checkbox' class='checkbox' />
	      </div>
        <div style="width:25%">
		<?php echo "Slides Preview";?>
	      </div>
	      <div style="width:30%">
		<?php echo "Enabled";?>
	      </div>
	      <div style="width:40%" class="">
		<?php echo "Options";?>
	      </div>   
	    </div>
	    <ul class="sesbasic_manage_table_list" id='menu_list'>
	      <?php foreach ($this->slides as $slide):?>
		<li class="item_label" id="columns_<?php echo $slide->slidephoto_id ?>">
    <div style="width:5%;">
    	<input type='checkbox' class='checkbox' name='delete_<?php echo $slide->slidephoto_id;?>' value="<?php echo $slide->slidephoto_id; ?>" />
    </div>
      <div style="width:25%;">
		    <?php $photo = Engine_Api::_()->storage()->get($slide->photo_id, '')->getPhotoUrl(); ?>
        <img src="<?php echo $photo; ?>" style="height:100px; width:100px;" />
		  </div>
		  <div style="width:30%;">
            <?php if($slide->active == 1): ?>
              <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'sesevent', 'controller' => 'admin-manage', 'action' => 'slideactive', 'id' => $slide->slidephoto_id), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/images/icons/check.png', '', array('title'=> $this->translate('Disable Slide')))) ?>
            <?php else: ?>
              <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'sesevent', 'controller' => 'admin-manage', 'action' => 'slideactive', 'id' => $slide->slidephoto_id), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/images/icons/error.png', '', array('title'=> $this->translate('Enable Slide')))) ?>
          <?php endif; ?>
		  </div>
		  <div style="width:40%;">
		    <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesevent', 'controller' => 'manage', 'action' => 'edit-slide', 'id' => $slide->slidephoto_id), $this->translate('Edit'), array()) ?>
		    |
		    <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesevent', 'controller' => 'manage', 'action' => 'delete-slide', 'id' => $slide->slidephoto_id), $this->translate('Delete'), array('class' => 'smoothbox')); ?>   
		  </div>
		</li>
	      <?php endforeach; ?>
	    </ul>
	  </div>
        <?php else:?>
	  <br/>
	  <div class="tip">
	    <span><?php echo $this->translate("You have not created any slide photos yet.") ?></span>
	  </div>
        <?php endif;?>
        <br/>
      
    <div class='buttons'>
      <button type='submit'><?php echo $this->translate("Delete Selected") ?></button>
    </div>
    </div>
    </form>
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

    //Send request
    var url = '<?php echo $this->url(array("action" => "order")) ?>';
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
