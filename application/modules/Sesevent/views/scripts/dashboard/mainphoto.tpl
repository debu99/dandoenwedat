<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: mainphoto.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/multiupload.css'); ?>
<?php if(!$this->is_ajax){ 
echo $this->partial('dashboard/left-bar.tpl', 'sesevent', array(
	'event' => $this->event,
      ));	
?>
	<div class="sesbasic_dashboard_content sesbm sesbasic_clearfix">
<?php } 
	echo $this->partial('dashboard/event_expire.tpl', 'sesevent', array(
	'event' => $this->event,
      ));	
?>
    	<div class="sesbasic_dashboard_form sesevent_dashboard_photo_form">
    		<?php echo $this->form->render() ?>
      </div>
<?php if(!$this->is_ajax){ ?>
  </div>
</div>
</div>
</div>
<?php  } ?>
<script type="application/javascript">
sesJqueryObject  (document).ready(function()
{
	sesJqueryObject('#execute').hide();
	var removehtml=sesJqueryObject('#removeimage-wrapper').html();
	sesJqueryObject('#removeimage-wrapper').remove();
	sesJqueryObject('#event_main_photo_preview-element').append('<div id="removeimage-wrapper">'+removehtml+'</div>');
	
var obj = sesJqueryObject('#dragandrophandlerbackground');
obj.click(function(e){
  sesJqueryObject('#background').trigger('click');
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
     handleFileBackgroundUpload(files,'event_main_photo_preview');
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
<?php
if ($this->event->photo_id !== null && $this->event->photo_id && Engine_Api::_()->storage()->get($this->event->photo_id, '') != null){ 
 $backgroundImage =	Engine_Api::_()->storage()->get($this->event->photo_id, '')->getPhotoUrl();?>
ShowhandleFileBackgroundUpload('<?php echo $backgroundImage ?>','event_main_photo_preview');
<?php }else{ ?>
sesJqueryObject  (document).ready(function()
{
	$('dragdropbackground-wrapper').style.display = 'block';
	$('event_main_photo_preview-wrapper').style.display = 'none';
	$('background-wrapper').style.display = 'none';
});
<?php } ?>
function ShowhandleFileBackgroundUpload(input,id) {
  var url = input; 
		$('background-wrapper').style.display = 'none';
    $('dragdropbackground-element').style.display = 'none';
    $('removeimage-wrapper').style.display = 'block';
    $('removeimage1').style.display = 'inline-block';
    $('event_main_photo_preview').style.display = 'block';
    $('event_main_photo_preview-wrapper').style.display = 'block';
  }

function handleFileBackgroundUpload(input,id) {
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
    $('dragdropbackground-element').style.display = 'none';
    $('removeimage-wrapper').style.display = 'block';
    $('removeimage1').style.display = 'inline-block';
    $('event_main_photo_preview').style.display = 'block';
    $('event_main_photo_preview-wrapper').style.display = 'block';
		sesJqueryObject('#execute').show();
    reader.readAsDataURL(input.files[0]);
  }
}
function removeImage() {
	$('dragdropbackground-element').style.display = 'block';
	$('removeimage-wrapper').style.display = 'none';
	$('removeimage1').style.display = 'none';
	$('event_main_photo_preview').style.display = 'none';
	$('event_main_photo_preview-wrapper').style.display = 'none';
	$('event_main_photo_preview').src = '';
	$('MAX_FILE_SIZE').value = '';
	$('removeimage2').value = '';
	sesJqueryObject('#execute').hide();
}
function uploadBackgroundPhoto(){
	document.getElementById("EditPhoto").submit();
}
function removePhotoEvent(url) {
		window.location.href = url;
}
</script>
<?php if($this->is_ajax) die; ?>