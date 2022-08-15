<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: edit-photo.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesadvancedactivity/externals/styles/styles.css'); ?>
<div class="sesadvancedactivity_edit_photo_popup sesbasic_bxs">
  <form class="global_form">
    <div>
      <div>
        <h3><?php echo $this->translate('Edit Photo'); ?></h3>
        <div class="form-elements">
          <div id="title-wrapper" class="form-wrapper">
            <div id="title-label" class="form-label">
              <label for="title" class="optional"><?php echo $this->translate('Title');?></label>
            </div>
            <div id="title-element" class="form-element">
              <input type="text" name="title" id="title" value="<?php  echo $this->photo->title;  ?>">
            </div>
          </div>
          <div id="description-wrapper" class="form-wrapper">
            <div id="description-label" class="form-label">
              <label for="description" class="optional"><?php echo $this->translate('Image Description');?></label>
            </div>
            <div id="description-element" class="form-element">
              <textarea name="description" id="description" cols="120" rows="2"><?php echo $this->photo->description;  ?></textarea>
            </div>
          </div>
          <div class="form-wrapper" id="buttons-wrapper">
            <fieldset id="fieldset-buttons">
              <button name="execute" id="execute" ><?php echo $this->translate('Save Changes');?></button>
              or <a name="cancel" id="cancel" type="button" href="javascript:void(0);" onclick="parent.Smoothbox.close();"><?php echo $this->translate('cancel'); ?></a>
            </fieldset>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>
<script type="text/javascript">
  sesJqueryObject(document).on('click','#execute',function(e){
		e.preventDefault();
    var photo_id = '<?php echo $this->photo_id;?>';
    request = new Request.JSON({
      'format' : 'json',
      'url' : '<?php echo $this->url(Array('module' => 'sesadvancedactivity', 'controller' => 'album', 'action' => 'save-information','photo_id'=>$this->photo_id), 'default') ?>',
      'data': {
        'photo_id' : photo_id,
        'title' : document.getElementById('title').value,
        'description' : document.getElementById('description').value,
      },
     'onSuccess' : function(responseJSON) {
       parent.Smoothbox.close();
       return false;
      }
    });
    request.send();
		return false;		
  });
</script> 