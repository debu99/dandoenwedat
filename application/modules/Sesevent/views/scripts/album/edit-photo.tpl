<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: edit-photo.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<form class="global_form_popup">
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
<script type="text/javascript">
  sesJqueryObject(document).on('click','#execute',function(e){
		e.preventDefault();
    var photo_id = '<?php echo $this->photo_id;?>';
    request = new Request.JSON({
      'format' : 'json',
      'url' : '<?php echo $this->url(Array('controller' => 'album', 'action' => 'save-information'), 'sesevent_extended',true) ?>/photo_id/'+photo_id,
      'data': {
        'photo_id' : photo_id,
        'title' : document.getElementById('title').value,
        'description' : document.getElementById('description').value
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