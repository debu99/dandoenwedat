<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: _composeSeseventAlbum.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>

<?php  if (Engine_Api::_()->core()->hasSubject('sesevent_event')):
				 $subject = Engine_Api::_()->core()->getSubject();
			  else:
        	return;
			 endif; 
?>
<style type="text/css">
/*REMOVE SE VIDEO COMPOSER FROM FEED */
#compose-video-menu span,
#compose-video-activator,
.tool_i_video
{
 display: none !important;
}
#compose-photo-menu span,
#compose-photo-activator,
.tool_i_photo
{
 display: none !important;
}
</style>
<style type="text/css">
/*REMOVE SES MUSIC COMPOSER FROM FEED */
#compose-sesmusic-menu span,
#compose-sesmusic-activator,
.tool_i_sesmusic
{
 display: none !important;
}
/*REMOVE SE MUSIC COMPOSER FROM FEED */
#compose-music-menu span,
#compose-music-activator,
.tool_i_sesmusic
{
 display: none !important;
}
</style>
<?php
   //CHECK PRIVACY
   $viewer = Engine_Api::_()->user()->getViewer();
   if(!$subject->authorization()->isAllowed(null, 'photo') )
      return;
?>
<?php
  $this->headScript()
    ->appendFile($this->layout()->staticBaseUrl . 'externals/fancyupload/Swiff.Uploader.js')
    ->appendFile($this->layout()->staticBaseUrl . 'externals/fancyupload/Fx.ProgressBar.js')
    ->appendFile($this->layout()->staticBaseUrl . 'externals/fancyupload/FancyUpload2.js');
  $this->headLink()
    ->appendStylesheet($this->layout()->staticBaseUrl . 'externals/fancyupload/fancyupload.css');
  $this->headTranslate(array(
    'Overall Progress ({total})', 'File Progress', 'Uploading "{name}"',
    'Upload: {bytesLoaded} with {rate}, {timeRemaining} remaining.', '{name}',
    'Remove', 'Click to remove this entry.', 'Upload failed',
    '{name} already added.',
    '{name} ({size}) is too small, the minimal file size is {fileSizeMin}.',
    '{name} ({size}) is too big, the maximal file size is {fileSizeMax}.',
    '{name} could not be added, amount of {fileListMax} files exceeded.',
    '{name} ({size}) is too big, overall filesize of {fileListSizeMax} exceeded.',
    'Server returned HTTP-Status <code>#{code}</code>',
    'Security error occurred ({text})',
    'Error caused a send or load operation to fail ({text})',
  ));
?>
<style>
#compose-photo-error{ display:none;}
</style>
<script type="text/javascript">
  if(window.location.href.indexOf("messages/compose") > -1 || window.location.href.indexOf("messages/view/id") > -1) {
    var isMessageEvent = true;
  }else{
    var isMessageEvent = false;
  }
<?php if(!Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesadvancedactivity')) { ?>
  en4.core.runonce.add(function() {
    if (Composer.Plugin.Seseventphoto)
      return;

    Asset.javascript('<?php echo $this->layout()->staticBaseUrl ?>application/modules/Sesevent/externals/scripts/composer_photo.js', {
      onLoad:  function() {
        var type = 'wall';
        if (composeInstance.options.type) type = composeInstance.options.type;
        composeInstance.addPlugin(new Composer.Plugin.Seseventphoto({
          title : '<?php echo $this->string()->escapeJavascript($this->translate('Add Photo')) ?>',
          lang : {
            'Add Photo' : '<?php echo $this->string()->escapeJavascript($this->translate('Add Photo')) ?>',
            'Select File' : '<?php echo $this->string()->escapeJavascript($this->translate('Select File')) ?>',
            'cancel' : '<?php echo $this->string()->escapeJavascript($this->translate('cancel')) ?>',
            'Loading...' : '<?php echo $this->string()->escapeJavascript($this->translate('Loading...')) ?>',
            'Unable to upload photo. Please click cancel and try again': ''
          },
					event_id : <?php echo $subject->getIdentity(); ?>,
          requestOptions : {
            'url'  : en4.core.baseUrl + 'sesevent/album/upload-photo/type/'+type+'/event_id/<?php echo $subject->getIdentity(); ?>',
          },
          fancyUploadOptions : {
            'url'  : en4.core.baseUrl + 'sesevent/album/upload-photo/format/json/type/'+type+'/event_id/<?php echo $subject->getIdentity(); ?>',
            'path' : en4.core.basePath + 'externals/fancyupload/Swiff.Uploader.swf'
          }
        }));
      }});

});
<?php }else{  ?>  
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/scripts/composer_photo.js'); ?>

en4.core.runonce.add(function() {

 composeInstance.addPlugin(new Composer.Plugin.Seseventphoto({
      title: '<?php echo $this->string()->escapeJavascript($this->translate('Add Photo')) ?>',
      lang : {
        'Add Photo' : '<?php echo $this->string()->escapeJavascript($this->translate('Add Photo')) ?>',
        'cancel' : '<?php echo $this->string()->escapeJavascript($this->translate('cancel')) ?>',
      }
    }));
  });
      
     
sesJqueryObject (document).ready(function()
{
var obj = sesJqueryObject('#dragandrophandlerenvent');
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
   var files = e.originalEvent.dataTransfer.files;
   //We need to send dropped files to Server
   handleFileUploadSesevent(files,obj);
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
var rowCount=0;
sesJqueryObject(document).on('click','div[id^="abortPhoto_"]',function(){
		var id = sesJqueryObject(this).attr('id').match(/\d+/)[0];
		if(typeof jqXHR[id] != 'undefined'){
				jqXHR[id].abort();
				delete filesArray[id];	
				execute = true;
				sesJqueryObject(this).parent().remove();
				executeuploadSesevent();
		}else{
				delete filesArray[id];	
				sesJqueryObject(this).parent().remove();
		}
    if(isMessageEvent && (sesJqueryObject('#toValues-wrapper').length > 0 || sesJqueryObject('#submit-wrapper').length > 0)){
      sesJqueryObject('#dragandrophandlerenvent').show();
    }
});
function createStatusbar(obj,file)
{
     rowCount++;
     var row="odd";
     if(rowCount %2 ==0) row ="even";
		  var checkedId = sesJqueryObject("input[name=cover]:checked");
			this.objectInsert = sesJqueryObject('<div class="advact_compose_photo_item sesbm '+row+'"></div>');
			this.overlay = sesJqueryObject("<div class='overlay advact_compose_photo_item_overlay'></div>").appendTo(this.objectInsert);
			this.abort = sesJqueryObject('<div class="abort sesevent_upload_item_abort" id="abortPhoto_'+countUploadSes+'"><span><?php echo $this->translate("Cancel Uploading"); ?></span></div>').appendTo(this.objectInsert);
			this.progressBar = sesJqueryObject('<div class="overlay_image progressBar"><div></div></div>').appendTo(this.objectInsert);
			this.imageContainer = sesJqueryObject('<div class="advact_compose_photo_item_photo"></div>').appendTo(this.objectInsert);
			this.src = sesJqueryObject('<img src="'+en4.core.baseUrl+'application/modules/Sesevent/externals/images/blank-img.gif">').appendTo(this.imageContainer);
			this.infoContainer = sesJqueryObject('<div class=advact_compose_photo_item_info sesbasic_clearfix"></div>').appendTo(this.objectInsert);
			 this.size = sesJqueryObject('<span class="sesevent_upload_item_size sesbasic_text_light"></span>').appendTo(this.infoContainer);
			 this.filename = sesJqueryObject('<span class="sesevent_upload_item_name"></span>').appendTo(this.infoContainer);
			this.option = sesJqueryObject('<div class="sesevent_upload_item_options clear sesbasic_clearfix"><span class="sesevent_upload_item_radio"></span><a class="edit_image_upload" href="javascript:void(0);"><i class="fa fa-edit"></i></a><a class="delete_image_upload" href="javascript:void(0);"><i class="fa fa-times"></i></a></div>').appendTo(this.objectInsert);
		  var objectAdd = sesJqueryObject(this.objectInsert).appendTo('#show_photo');
			jqueryObjectOfSes(".sesbasic_custom_horizontal_scroll").mCustomScrollbar("scrollTo",jqueryObjectOfSes('.sesbasic_custom_horizontal_scroll').find('.mCSB_container').find('#advact_compose_photo_container_inner').find('#dragandrophandlerenvent'));
    this.setFileNameSize = function(name,size)
    {
				if(typeof size != 'undefined'){
					var sizeStr="";
					var sizeKB = size/1024;
					if(parseInt(sizeKB) > 1024)
					{
							var sizeMB = sizeKB/1024;
							sizeStr = sizeMB.toFixed(2)+" MB";
					}
					else
					{
							sizeStr = sizeKB.toFixed(2)+" KB";
					}
					this.size.html(sizeStr);
				}
					this.filename.html(name);
    }
    this.setProgress = function(progress)
    {       
        var progressBarWidth =progress*this.progressBar.width()/ 100;  
        this.progressBar.find('div').animate({ width: progressBarWidth }, 10).html(progress + "% ");
        if(parseInt(progress) >= 100)
        {
						sesJqueryObject(this.progressBar).remove();
        }
    }
    this.setAbort = function(jqxhr)
    {
        var sb = this.objectInsert;
				
        this.abort.click(function()
        {
            jqxhr.abort();
            sb.hide();
						executeuploadSesevent();
        });
    }
}

var selectedFileLength = 0;
var statusArray =new Array();
var filesArray = [];
var countUploadSes = 0;
var fdSes = new Array();
function handleFileUploadSesevent(files,obj)
{ 
	 selectedFileLength = files.length;
   for (var i = 0; i < files.length; i++) 
   {
			var url = files[i].name;
    	var ext = url.substring(url.lastIndexOf('.') + 1).toLowerCase();
			if((ext == "png" || ext == "jpeg" || ext == "jpg" || ext == 'PNG' || ext == 'JPEG' || ext == 'JPG' || ext == 'gif' || ext == 'GIF')){
				var status = new createStatusbar(obj,files[i]); //Using this we can set progress.
				status.setFileNameSize(files[i].name,files[i].size);
				statusArray[countUploadSes] =status;
				filesArray[countUploadSes] = files[i];
				countUploadSes++;
			}
   }
	 executeuploadSesevent();
}
var execute = true;
function executeuploadSesevent(){
	if(Object.keys(filesArray).length == 0 && sesJqueryObject('#show_photo').html() != ''){
   if(isMessageEvent && (sesJqueryObject('#toValues-wrapper').length > 0 || sesJqueryObject('#submit-wrapper').length > 0)){}else{
		sesJqueryObject('#compose-menu').show();
   }
	}
	if(execute == true){
	 for (var i in filesArray) {
		if (filesArray.hasOwnProperty(i))
    {
     	sendFileToServerSesevent(filesArray[i],statusArray[i],filesArray[i],'upload',i);
			break;
    }			
	 }
	}
}
var jqXHR = new Array();
function sendFileToServerSesevent(formData,status,file,isURL,i)
{
		execute = false;
		var formData = new FormData();
		formData.append('Filedata', file);
		if(isURL == 'upload'){
			var reader = new FileReader();
			reader.onload = function (e) {
				status.src.attr('src', e.target.result);
			}
			reader.readAsDataURL(file);
			var urlIs = '';
		}else{
			status.src.attr('src', file);
			var urlIs = true;
		}
    var type = 'wall';
    
    if(isMessageEvent && (sesJqueryObject('#toValues-wrapper').length > 0 || sesJqueryObject('#submit-wrapper').length > 0)){
      sesJqueryObject('#dragandrophandlerenvent').hide();
    }
    
    if (composeInstance.options.type) type = composeInstance.options.type;
		sesJqueryObject('#show_photo_container').addClass('iscontent');
		var url = '&isURL='+urlIs;
		
    var uploadURL =en4.core.baseUrl + 'sesevent/album/upload-photo/type/'+type+'/event_id/<?php echo $subject->getIdentity(); ?>';
    var extraData ={}; //Extra Data.
    jqXHR[i]=sesJqueryObject.ajax({
		xhr: function() {
		var xhrobj = sesJqueryObject.ajaxSettings.xhr();
		if (xhrobj.upload) {
				xhrobj.upload.addEventListener('progress', function(event) {
						var percent = 0;
						var position = event.loaded || event.position;
						var total = event.total;
						if (event.lengthComputable) {
								percent = Math.ceil(position / total * 100);
						}
						//Set progress
						status.setProgress(percent);
				}, false);
		}
		return xhrobj;
		},
    url: uploadURL,
    type: "POST",
    contentType:false,
    processData: false,
		cache: false,
		data: formData,
		success: function(response){
					execute = true;
					delete filesArray[i];
					//sesJqueryObject('#submit-wrapper').show();
          response = sesJqueryObject.parseJSON(response);
					if (response.status) {
              if(document.getElementById('fancyalbumuploadfileids')) {
                var fileids = document.getElementById('fancyalbumuploadfileids');
                fileids.value = fileids.value + response.photo_id + " ";
							}
							status.src.attr('src',response.url);
							status.option.attr('data-src',response.photo_id);
							status.overlay.css('display','none');
							status.setProgress(100);
							status.abort.remove();
              if(isMessageEvent && (sesJqueryObject('#toValues-wrapper').length > 0 || sesJqueryObject('#submit-wrapper').length > 0)){
                sesJqueryObject('#submit').show();
                sesJqueryObject('#dragandrophandlerenvent').hide();
              }
              composeInstance.signalPluginReady(true);
 					}else
							status.abort.html('<span>Error In Uploading File</span>');
					executeuploadSesevent();
       }
    }); 
}
function readImageUrlSesevent(input) {
	handleFileUploadSesevent(input.files,sesJqueryObject('#dragandrophandlerenvent'));
}
sesJqueryObject(document).on('click','#dragandrophandlerenvent',function(){
  setTimeout(function(){ document.getElementById('file_multi').click(); }, 100);
});
var isUploadUrl = false;
sesJqueryObject(document).on('click','.edit_image_upload',function(e){
	e.preventDefault();
	var photo_id = sesJqueryObject(this).closest('.sesevent_upload_item_options').attr('data-src');
	if(photo_id){
		editImage(photo_id);
	}else
		return false;
});
sesJqueryObject(document).on('click','.delete_image_upload',function(e){
	e.preventDefault();
	sesJqueryObject(this).parent().parent().find('.sesevent_upload_item_overlay').css('display','block');
	var sesthat = this;
	var photo_id = sesJqueryObject(this).closest('.sesevent_upload_item_options').attr('data-src');
	if(photo_id){
		request = new Request.JSON({
    'format' : 'json',
    'url' : '<?php echo $this->url(Array('module' => 'sesevent', 'controller' => 'index', 'action' => 'remove'), 'default') ?>',
    'data': {
      'photo_id' : photo_id
    },
   'onSuccess' : function(responseJSON) {
			sesJqueryObject(sesthat).parent().parent().remove();
			var fileids = document.getElementById('fancyalbumuploadfileids');
			sesJqueryObject('#fancyalbumuploadfileids').val(fileids.value.replace(photo_id + " ",''));
			if(sesJqueryObject('#show_photo').html() == ''){
         if(isMessageEvent && (sesJqueryObject('#toValues-wrapper').length > 0 || sesJqueryObject('#submit-wrapper').length > 0)){
            sesJqueryObject('#submit').hide();
            sesJqueryObject('#dragandrophandlerenvent').show();
          }
				sesJqueryObject('#show_photo_container').removeClass('iscontent');
			}
     return false;
    }
    });
    request.send();
	}else
		return false;
});
<?php if(isset($_POST['file']) && $_POST['file'] != ''){ ?>
		sesJqueryObject('#fancyalbumuploadfileids').val("<?php echo $_POST['file'] ?>");    	
<?php } ?>
  function editImage(photo_id) {
    var url = '<?php echo $this->url(Array('module' => 'sesevent', 'controller' => 'index', 'action' => 'edit-photo'), 'default') ?>' + '/photo_id/'+ photo_id;
    Smoothbox.open(url);
  }
</script>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/styles/customscrollbar.css'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/jquery.min.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/customscrollbar.concat.min.js'); ?>
<?php } ?>
</script>
