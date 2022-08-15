<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: _hostCreate.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php $isedit = $this->isEdit;
			$host_id = $this->host_id;
 ?>
<?php $viewer = Engine_Api::_()->user()->getViewer(); ?>
<input type="hidden" name="host_type" id="host_type" value="<?php if($isedit) { echo $this->translate('nochange'); }else{ echo $this->translate('myself');} ?>" />
<input type="hidden" value="" id="toValues" name="toValues" />
<div id="hostname-wrapper" class="form-wrapper">
  <div id="hostname-label" class="form-label">
    <label for="hostname" class="required"><?php echo $this->translate("Organizer Name") ?></label>
  </div>
</div>
<div id="cancel_new_host-wrapper" class="form-wrapper" style="display:none">
  <div id="cancel_new_host-element" class="form-element"><a class="host_new_detail form-link" href="javascript:;" id="cancel_new_host" type="button" name="cancel_new_host" ><i class="fa fa-times"></i><?php echo $this->translate("Cancel") ?></a> </span></div>
</div>
<?php 
			$viewer = Engine_Api::_()->user()->getViewer();
			$isAdmin = $viewer->isAdmin();
			if($isAdmin) {
	?>
    <div id="host-wrapper" class="form-wrapper">
      <div id="host-element" class="form-element">
        <a href="javascript:;" id="choosehost"><?php echo $this->translate("Choose Host"); ?></a>&nbsp;&nbsp;|&nbsp;&nbsp;
        <a href="javascript:;" id="addnewhost">+ <?php echo $this->translate("Add New"); ?></a>
      </div>
    </div>
<?php } ?>


<div id="myself-wrapper" class="form-wrapper" <?php if($isedit){ ?> style="display:none" <?php } ?>>
  <div id="myself-element" class="form-element">
  	<span id="tospan_<?php echo $this->string()->escapeJavascript($viewer->getTitle()) ?>_<?php echo sprintf("%d", $viewer->getIdentity()) ?>" class="sesevent_create_host sesevent_host_default_detail sesbm"><?php echo ($this->itemPhoto($viewer, "thumb.icon")); ?> <a href="<?php echo $viewer->getHref(); ?>"  target="_blank"> <?php echo $this->string()->escapeJavascript($viewer->getTitle()) ?> </a> </span> </div>
</div>
<div id="offsitehosts-wrapper" class="form-wrapper">
  <div id="offsitehosts-element" class="form-element"> 
  	<?php if($isedit){ ?> 
    	<?php $host = Engine_Api::_()->getItem('sesevent_host', $host_id); ?>
    	<span id="tospan_<?php echo $this->string()->escapeJavascript($host->getTitle()) ?>_<?php echo sprintf("%d", $host->getIdentity()) ?>" class="sesevent_create_host sesevent_host_default_detail sesbm"><?php echo ($this->itemPhoto($host, "thumb.icon")); ?> <a href="<?php echo $host->getHref(); ?>"  target="_blank"> <?php echo $this->string()->escapeJavascript($host->getTitle()) ?> </a> </span>
    <?php } ?>
  </span> </div>
</div>


<div id="selecthost-wrapper" class="form-wrapper" style="display:none">
  <div id="selecthost-element" class="form-element">
    <div id="selecthost">
      <select id="selecthost_id" name="selecthost" onchange="changeHostTrigger(this.value)">
        <option value="0"><?php echo $this->translate("Please select type"); ?></option>
        <option value="1"><?php echo $this->translate("Off-Site"); ?></option>
        <option value="2"><?php echo $this->translate("On-Site"); ?></option>
        <option value="3"><?php echo $this->translate("Myself"); ?></option>
      </select>
    </div>
    <div id="selecthost_offsite" style="display:none">
      <?php if(isset($this->offsitehost) && $this->offsitehost != ''){ ?>
        <select id="selectoffsitehost_id" name="selectoffsitehost">
          <?php echo $this->offsitehost; ?>
        </select>
        <button id="selectedoffsitehost">ok</button>
      <?php }else{ ?>
      	<div class="tip"><span><?php echo $this->translate("No off site host created by you yet."); ?></span></div>
      <?php  } ?>
    </div>
    <div id="selecthost_onsite" style="display:none">
    	<input type="text" name="selectonsitehost" id="selectonsitehost" value="" placeholder="<?php echo $this->translate("Start typing ...") ?>" autocomplete="off" />
    </div>
  </div>
</div>

<div id="add_new_host" style="display:none;">
  <div id="host_name-wrapper" class="form-wrapper hideF" style="display:none">
    <div id="host_name-label" class="form-label">
      <label for="host_name" class="required"><?php echo $this->translate("Host Name") ?></label>
    </div>
    <div id="host_name-element" class="form-element">
      <input type="text" name="host_name" id="host_name" value="" class="host_new_detail" autocomplete="off" placeholder="<?php echo $this->translate("Host Name") ?>" />
    </div>
  </div>
  <div id="host_email-wrapper" class="form-wrapper hideF" style="display:none">
    <div id="host_email-label" class="form-label">
      <label for="host_email" class="required"><?php echo $this->translate("Host Email") ?></label>
    </div>
    <div id="host_email-element" class="form-element">
      <input type="text" name="host_email" id="host_email" value="" class="host_new_detail" autocomplete="off" placeholder="<?php echo $this->translate("Host Email") ?>" />
    </div>
  </div>
  <div id="host_phone-wrapper" class="form-wrapper">
    <div id="host_phone-label" class="form-label">
      <label for="host_phone" class="optional"><?php echo $this->translate("Host Phone") ?></label>
    </div>
    <div id="host_phone-element" class="form-element">
      <input type="text" name="host_phone" id="host_phone" value="" class="host_new_detail" autocomplete="off" placeholder="<?php echo $this->translate("Host Phone") ?>" />
    </div>
  </div>
  <div id="host_description-wrapper" class="form-wrapper">
    <div id="host_description-label" class="form-label">
      <label for="host_description" class="optional"><?php echo $this->translate("Host Description") ?></label>
    </div>
    <div id="host_description-element" class="form-element">
      <textarea name="host_description" id="host_description" cols="45" rows="6" autocomplete="off" class="host_new_detail tinymce"></textarea>
    </div>
  </div>
  
  <input type="file" name="host_photo" id="host_photo" onclick='javascript:sesJqueryObject("#host_photo").val("")' onchange="handleFileBackgroundUploadhost(this,'event_main_photo_previewhost')" style="display:none" />
  <div id="photouploaderhost-wrapper" class="form-wrapper">
    <div id="photouploaderhost-label" class="form-label">
      <label for="photouploaderhost" class="optional"><?php echo $this->translate("Host Photo") ?></label>
    </div>
    <div id="photouploaderhost-element" class="form-element">
      <div id="dragandrophandlerbackgroundhost" class="sesevent_upload_dragdrop_content sesbasic_bxs requiredClass">
        <div class="sesevent_upload_dragdrop_content_inner"><i class="fa fa-camera"></i><span class="sesevent_upload_dragdrop_content_txt"><?php echo $this->translate("Add host photo for this event") ?></span></div>
      </div>
    </div>
  </div>
  <div id="event_main_photo_previewhost-wrapper" class="form-wrapper" style="display:none">
    <div id="event_main_photo_previewhost-element" class="form-element">
      <input type="image" name="event_main_photo_previewhost" id="event_main_photo_previewhost" src="" disabled="disabled" alt="" src="" width="300" height="200" />
    </div>
  </div>
  <div id="removeimagehost-wrapper" class="form-wrapper" style="display:none">
    <div id="removeimagehost-element" class="form-element"><a class="icon_cancel form-link" id="removeimage1host" style="display:none; " href="javascript:void(0);" onclick="removeImagehost();"><i class="far fa-trash"></i><?php echo $this->translate('Remove');?></a></div>
  </div>  
  <div id="checked_socialshare-wrapper" class="form-wrapper">
    <div id="checked_socialshare-label" class="form-label">
      <input type="checkbox" name="checked_socialshare" id="checked_socialshare" />
      <label for="checked_socialshare" class="optional"><?php echo $this->translate("Include Social Links") ?></label>
    </div>
  </div>
  <div id="social_share_options_host" class="sesevent_create_host_social_wrapper" style="display:none">
    <div id="facebook_url-wrapper" class="form-wrapper">
      <div id="facebook_url-label" class="form-label">
        <label for="facebook_url" class="optional"><i class="fab fa-facebook-square"></i><?php echo $this->translate("Host Facebook URL") ?></label>
      </div>
      <div id="facebook_url-element" class="form-element">
        <input type="text" name="facebook_url" id="facebook_url" value="" class="host_new_detail" autocomplete="off" />
      </div>
    </div>
    <div id="twitter_url-wrapper" class="form-wrapper">
      <div id="twitter_url-label" class="form-label">
        <label for="twitter_url" class="optional"><i class="fab fa-twitter-square"></i><?php echo $this->translate("Host Twitter URL") ?></label>
      </div>
      <div id="twitter_url-element" class="form-element">
        <input type="text" name="twitter_url" id="twitter_url" value="" class="host_new_detail" autocomplete="off" />
      </div>
    </div>
    <div id="website_url-wrapper" class="form-wrapper">
      <div id="website_url-label" class="form-label">
        <label for="website_url" class="optional"><i class="fa fa-globe"></i><?php echo $this->translate("Host Website URL") ?></label>
      </div>
      <div id="website_url-element" class="form-element">
        <input type="text" name="website_url" id="website_url" value="" class="host_new_detail" autocomplete="off" />
      </div>
    </div>
    <div id="linkdin_url-wrapper" class="form-wrapper">
      <div id="linkdin_url-label" class="form-label">
        <label for="linkdin_url" class="optional"><i class="fab fa-linkedin-square"></i><?php echo $this->translate("Host LinkedIn URL") ?></label>
      </div>
      <div id="linkdin_url-element" class="form-element">
        <input type="text" name="linkdin_url" id="linkdin_url" value="" class="host_new_detail" autocomplete="off" />
      </div>
    </div>
    <div id="googleplus_url-wrapper" class="form-wrapper">
      <div id="googleplus_url-label" class="form-label">
        <label for="googleplus_url" class="optional"><i class="fab fa-google-plus-square"></i><?php echo $this->translate("Host Google Plus URL") ?></label>
      </div>
      <div id="googleplus_url-element" class="form-element">
        <input type="text" name="googleplus_url" id="googleplus_url" value="" class="host_new_detail" autocomplete="off" />
      </div>
    </div>
  </div>
</div>
<script type="application/javascript">
 en4.core.runonce.add(function() {
sesJqueryObject('#checked_socialshare').change(function() {
		if(sesJqueryObject(this).is(':checked')){
			sesJqueryObject('#social_share_options_host').show();
		}else
			sesJqueryObject('#social_share_options_host').hide();
});
<?php if($isedit){ ?>
var toHostVal = {
      id : <?php echo sprintf("%d", $host->getIdentity()) ?>,
      type : '<?php echo $host->getType() ?>',
      guid : '<?php echo $host->getGuid() ?>',
      title : '<?php echo $this->string()->escapeJavascript($host->getTitle()) ?>',
			photo : '<?php echo ($this->itemPhoto($host, "thumb.icon")); ?>',
			url : '<?php echo $host->getHref(); ?>',
    };
<?php } ?>
sesJqueryObject('#choosehost').click(function(){
	sesJqueryObject("#myself-wrapper").hide();
	sesJqueryObject("#selecthost-wrapper").show();
	sesJqueryObject('#cancel_new_host-wrapper').show();
	sesJqueryObject('#host-wrapper').hide();
	sesJqueryObject('#offsitehosts-element').hide();
});
sesJqueryObject('#cancel_new_host').click(function(){
	<?php if($isedit){ ?>
		var html = '<span id="tospan_'+toHostVal.title+'_'+toHostVal.id+'" class="sesevent_create_host sesevent_host_default_detail sesbm">'+toHostVal.photo+'<a href="'+toHostVal.url+'"  target="_blank">'+toHostVal.label+'</a></span>';
		sesJqueryObject('#host_type').val('nochange');
		sesJqueryObject('#toValues').val('');
		sesJqueryObject("#offsitehosts-element").show();
	<?php }else{ ?>
	sesJqueryObject('#host_type').val('myself');
	sesJqueryObject('#toValues').val('');
	sesJqueryObject("#myself-wrapper").show();
<?php } ?>
	sesJqueryObject("#selecthost-wrapper").hide();
	sesJqueryObject("#cancel_new_host-wrapper").hide();
	sesJqueryObject('#host-wrapper').show();
	sesJqueryObject('#add_new_host').hide();
	sesJqueryObject('.hideF').hide();	
	removeImagehost();
});
sesJqueryObject('#addnewhost').click(function(){
	sesJqueryObject('.hideF').show();
	sesJqueryObject("#add_new_host").show();
	sesJqueryObject("#myself-wrapper").hide();
	sesJqueryObject("#selecthost-wrapper").hide();
	sesJqueryObject('#cancel_new_host-wrapper').show();
	sesJqueryObject('#host-wrapper').hide();
	sesJqueryObject('#offsitehosts-element').hide();
	sesJqueryObject('#host_type').val('upload');
	sesJqueryObject('#toValues').val('');
});

sesJqueryObject('#selectedoffsitehost').click(function(e){
   e.preventDefault();
		var to = sesJqueryObject('#selectoffsitehost_id option:selected').data('src');
		var html = '<span id="tospan_'+to.title+'_'+to.id+'" class="sesevent_create_host sesevent_host_default_detail sesbm">'+to.photo+'<a href="'+to.url+'"  target="_blank">'+to.title+'</a></span>';
		sesJqueryObject('#offsitehosts-element').show();
		sesJqueryObject('#offsitehosts-element').html(html);
		sesJqueryObject("#myself-wrapper").hide();
		sesJqueryObject("#selecthost-wrapper").hide();
		sesJqueryObject('#cancel_new_host-wrapper').hide();
		sesJqueryObject('#host-wrapper').show();
		sesJqueryObject('#selecthost_id').val("0");
		sesJqueryObject("#selecthost_offsite").hide();
		sesJqueryObject("#selecthost_onsite").hide();	
		sesJqueryObject('#host_type').val('offsite');
		sesJqueryObject('#toValues').val(to.id);
});
	 var contentAutocomplete = new Autocompleter.Request.JSON('selectonsitehost', '<?php echo $this->url(array('module' => 'sesevent', 'controller' => 'ajax', 'action' => 'sitemember'), 'default', true) ?>', {
        'postVar': 'text',
        'minLength': 1,
        'selectMode': 'pick',
        'autocompleteType': 'tag',
        'customChoices': true,
        'filterSubset': true,
        'multiple': false,
        'className': 'sesbasic-autosuggest',
        'injectChoice': function(token) {
          var choice = new Element('li', {
            'class': 'autocompleter-choices', 
            'html': token.photo, 
            'id':token.label
          });
          new Element('div', {
            'html': this.markQueryValue(token.label),
            'class': 'autocompleter-choice'
          }).inject(choice);
          this.addChoiceEvents(choice).inject(this.choices);
          choice.store('autocompleteChoice', token);
        }
      });
      contentAutocomplete.addEvent('onSelection', function(element, selected, value, input) {
       	var to =  selected.retrieve('autocompleteChoice');
				var html = '<span id="tospan_'+to.title+'_'+to.id+'" class="sesevent_create_host sesevent_host_default_detail sesbm">'+to.photo+'<a href="'+to.url+'"  target="_blank">'+to.label+'</a></span>';
				sesJqueryObject('#offsitehosts-element').show();
				sesJqueryObject('#offsitehosts-element').html(html);
				sesJqueryObject("#myself-wrapper").hide();
				sesJqueryObject("#selecthost-wrapper").hide();
				sesJqueryObject('#cancel_new_host-wrapper').hide();
				sesJqueryObject('#host-wrapper').show();
				sesJqueryObject('#selecthost_id').val("0");
				sesJqueryObject("#selecthost_offsite").hide();
				sesJqueryObject("#selecthost_onsite").hide();	
				sesJqueryObject('#selectonsitehost').val('');
				sesJqueryObject('#host_type').val('site');
				sesJqueryObject('#toValues').val(to.id);
      });
  
//drag drop photo upload
sesJqueryObject (document).ready(function()
{
	if(sesJqueryObject('#dragandrophandlerbackgroundhost').hasClass('requiredClass')){
		sesJqueryObject('#dragandrophandlerbackgroundhost').parent().parent().find('#photouploader-label').find('label').addClass('required');	
	}
var obj = sesJqueryObject('#dragandrophandlerbackgroundhost');
obj.click(function(e){
  sesJqueryObject('#host_photo').trigger('click');
});
obj.on('dragenter', function (e) 
{
    e.stopPropagation();
    e.preventDefault();
    sesJqueryObject (this).addClass("sesbd");
});
obj.on('dragover', function (e) 
{
     e.stopPropagation();
     e.preventDefault();
});
obj.on('drop', function (e) 
{
		 sesJqueryObject (this).removeClass("sesbd");
		 sesJqueryObject (this).addClass("sesbm");
     e.preventDefault();
     var files = e.originalEvent.dataTransfer;
     handleFileBackgroundUploadhost(files,'event_main_photo_previewhost');
});
sesJqueryObject (document).on('dragenter', function (e) 
{
    e.stopPropagation();
    e.preventDefault();
});
sesJqueryObject (document).on('dragover', function (e) 
{
  e.stopPropagation();
  e.preventDefault();
});
	sesJqueryObject (document).on('drop', function (e) 
	{
			e.stopPropagation();
			e.preventDefault();
	});
});
});
function changeHostTrigger(value){
	if(value == 3){
		sesJqueryObject("#myself-wrapper").show();
		sesJqueryObject("#selecthost-wrapper").hide();
		sesJqueryObject('#cancel_new_host-wrapper').hide();
		sesJqueryObject('#host-wrapper').show();
		sesJqueryObject('#selecthost_id').val("0");
		sesJqueryObject("#selecthost_offsite").hide();
		sesJqueryObject("#selecthost_onsite").hide();	
		sesJqueryObject('#host_type').val('myself');
		sesJqueryObject('#toValues').val('');
	}else if(value == 1){
		sesJqueryObject("#selecthost_offsite").show();
		sesJqueryObject("#selecthost_onsite").hide();	
			
	}else if(value == 2){
		sesJqueryObject("#selecthost_onsite").show();	
		sesJqueryObject("#selecthost_offsite").hide();	
	}else{
		sesJqueryObject("#selecthost_onsite").hide();	
		sesJqueryObject("#selecthost_offsite").hide();
	}
}
function handleFileBackgroundUploadhost(input,id) {
  var url = input.value; 
  if(typeof url == 'undefined')
    url = input.files[0]['name'];
  var ext = url.substring(url.lastIndexOf('.') + 1).toLowerCase();
  if (input.files && input.files[0] && (ext == "png" || ext == "jpeg" || ext == "jpg" || ext == 'PNG' || ext == 'JPEG' || ext == 'JPG')){
    var reader = new FileReader();
    reader.onload = function (e) {
     // $(id+'-wrapper').style.display = 'block';
      $(id).setAttribute('src', e.target.result);
    }
		$('photouploaderhost-element').style.display = 'none';
    $('removeimagehost-wrapper').style.display = 'block';
    $('removeimage1host').style.display = 'inline-block';
    $('event_main_photo_previewhost').style.display = 'block';
    $('event_main_photo_previewhost-wrapper').style.display = 'block';
    reader.readAsDataURL(input.files[0]);
  }
}
function removeImagehost() {
	$('photouploaderhost-element').style.display = 'block';
	$('removeimagehost-wrapper').style.display = 'none';
	$('removeimage1host').style.display = 'none';
	$('event_main_photo_previewhost').style.display = 'none';
	$('event_main_photo_previewhost-wrapper').style.display = 'none';
	$('event_main_photo_previewhost').src = '';
	$('host_photo').value = '';
}
 
</script>