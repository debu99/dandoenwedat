<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: create.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/style_album.css'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/styles/customscrollbar.css'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/jquery.min.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/customscrollbar.concat.min.js'); ?>
<div class="clear sesevent_order_view_top" style="margin:10px;">
      <a href="<?php echo $this->event->getHref(); ?>" class="buttonlink sesbasic_icon_back"><?php echo $this->translate("Back To Event"); ?></a>
    </div>
<div class="layout_middle">
	<div class="generic_layout_container ">
		<div class="sesevent_album_form"> <?php echo $this->form->render($this) ?> </div>
	</div>
</div>
<script type="text/javascript">
  var updateTextFields = function() {
    var fieldToggleGroup = ['#title-wrapper', '#description-wrapper'];
    fieldToggleGroup = $$(fieldToggleGroup.join(','))
    if ($('album').get('value') == 0)
    fieldToggleGroup.show();
    else 
    fieldToggleGroup.hide();
  }
  en4.core.runonce.add(updateTextFields);
</script>

<script type="text/javascript">
  jqueryObjectOfSes('#dragdrop-wrapper').show();
  jqueryObjectOfSes('#fromurl-wrapper').hide();
  jqueryObjectOfSes('#file_multi-wrapper').hide();
  jqueryObjectOfSes('#submit-wrapper').hide();
  jqueryObjectOfSes('#sesevent_create_form_tabs li a').on('click',function(){
    jqueryObjectOfSes('#dragdrop-wrapper').hide();
    jqueryObjectOfSes('#fromurl-wrapper').hide();
    jqueryObjectOfSes('#file_multi-wrapper').hide();
    if(jqueryObjectOfSes(this).hasClass('drag_drop'))
    	jqueryObjectOfSes('#dragdrop-wrapper').show();
		else if(jqueryObjectOfSes(this).hasClass('from_url'))
			jqueryObjectOfSes('#fromurl-wrapper').show();
    else if(jqueryObjectOfSes(this).hasClass('multi_upload'))
   	  document.getElementById('file_multi').click();			
  });
</script>

<script type="text/javascript">
 
jqueryObjectOfSes (document).ready(function() {
  var obj = jqueryObjectOfSes('#dragandrophandler');
  obj.on('dragenter', function (e) {
    e.stopPropagation();
    e.preventDefault();
    jqueryObjectOfSes (this).addClass("sesbd");
  });
  obj.on('dragover', function (e) {
    e.stopPropagation();
    e.preventDefault();
  });
  obj.on('drop', function (e) {
    jqueryObjectOfSes (this).removeClass("sesbd");
    jqueryObjectOfSes (this).addClass("sesbm");
    e.preventDefault();
    var files = e.originalEvent.dataTransfer.files;
    //We need to send dropped files to Server
    handleFileUpload(files,obj);
  });
  jqueryObjectOfSes (document).on('dragenter', function (e) {
    e.stopPropagation();
    e.preventDefault();
  });
  jqueryObjectOfSes (document).on('dragover', function (e) {
    e.stopPropagation();
    e.preventDefault();
  });
  jqueryObjectOfSes (document).on('drop', function (e) {
    e.stopPropagation();
    e.preventDefault();
  });
});
var rowCount=0;
jqueryObjectOfSes(document).on('click','div[id^="abortPhoto_"]',function(){
  var id = jqueryObjectOfSes(this).attr('id').match(/\d+/)[0];
  if(typeof jqXHR[id] != 'undefined'){
    jqXHR[id].abort();
    delete filesArray[id];	
    execute = true;
    jqueryObjectOfSes(this).parent().remove();
    executeupload();
  }else{
    delete filesArray[id];	
    jqueryObjectOfSes(this).parent().remove();
  }
});
function createStatusbar(obj,file)
{
     rowCount++;
     var row="odd";
     if(rowCount %2 ==0) row ="even";
		  var checkedId = jqueryObjectOfSes("input[name=cover]:checked");
			this.objectInsert = jqueryObjectOfSes('<div class="sesevent_upload_item sesbm '+row+'"></div>');
			this.overlay = jqueryObjectOfSes("<div class='overlay sesevent_upload_item_overlay'></div>").appendTo(this.objectInsert);
			this.abort = jqueryObjectOfSes('<div class="abort sesevent_upload_item_abort" id="abortPhoto_'+countUploadSes+'"><span><?php echo $this->translate("Cancel Uploading"); ?></span></div>').appendTo(this.objectInsert);
			this.progressBar = jqueryObjectOfSes('<div class="overlay_image progressBar"><div></div></div>').appendTo(this.objectInsert);
			this.imageContainer = jqueryObjectOfSes('<div class="sesevent_upload_item_photo"></div>').appendTo(this.objectInsert);
			this.src = jqueryObjectOfSes('<img src="'+en4.core.baseUrl+'application/modules/Sesevent/externals/images/blank-img.gif">').appendTo(this.imageContainer);
			this.infoContainer = jqueryObjectOfSes('<div class=sesevent_upload_photo_info sesbasic_clearfix"></div>').appendTo(this.objectInsert);
			this.size = jqueryObjectOfSes('<span class="sesevent_upload_item_size sesbasic_text_light"></span>').appendTo(this.infoContainer);
			this.filename = jqueryObjectOfSes('<span class="sesevent_upload_item_name"></span>').appendTo(this.infoContainer);
			this.option = jqueryObjectOfSes('<div class="sesevent_upload_item_options clear sesbasic_clearfix"><span class="sesevent_upload_item_radio"><input type="radio" id="main_photo_id'+rowCount+'" name="cover"><label for="main_photo_id'+rowCount+'"><?php echo $this->translate("Main Photo"); ?></label></span><a class="edit_image_upload" href="javascript:void(0);"><img src="'+en4.core.baseUrl+'application/modules/Sesevent/externals/images/edit.png"></a><a class="delete_image_upload" href="javascript:void(0);"><img src="'+en4.core.baseUrl+'application/modules/Sesevent/externals/images/error.png"></a></div>').appendTo(this.objectInsert);
		  var objectAdd = jqueryObjectOfSes(this.objectInsert).appendTo('#show_photo');
			jqueryObjectOfSes(this.objectInsert).css('width', widthSetImageContainer+'px');
		if ($('album').get('value') == 0) {
			if(jqueryObjectOfSes('#show_photo').children('div').length == 1) {
				var idPhoto = jqueryObjectOfSes('#show_photo').eq(0).find('.sesevent_upload_item_radio').find('input').attr('id');
				jqueryObjectOfSes('#'+idPhoto).prop('checked', true);
			}else{
				jqueryObjectOfSes(checkedId).prop('checked', true);
			}
		}
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
						jqueryObjectOfSes(this.progressBar).remove();
        }
    }
    this.setAbort = function(jqxhr)
    {
        var sb = this.objectInsert;
				
        this.abort.click(function()
        {
            jqxhr.abort();
            sb.hide();
						executeupload();
        });
    }
}
var widthSetImageContainer = 180;
jqueryObjectOfSes(document).ready(function(){
calculateWidthOfImageContainer();
});
function calculateWidthOfImageContainer(){
	var widthOfContainer = jqueryObjectOfSes('#uploadFileContainer-element').width();
	if(widthOfContainer>=740){
		widthSetImageContainer = 	(widthOfContainer/4)-12;
	}else if(widthOfContainer>=570){
			widthSetImageContainer = (widthOfContainer/3)-12;
	}else if(widthOfContainer>=380){
			widthSetImageContainer = (widthOfContainer/2)-12;
	}else {
			widthSetImageContainer = (widthOfContainer/1)-12;
	}
}
var selectedFileLength = 0;
var statusArray =new Array();
var filesArray = [];
var countUploadSes = 0;
var fdSes = new Array();
function handleFileUpload(files,obj)
{
	 selectedFileLength = files.length;
   for (var i = 0; i < files.length; i++) 
   {
			var url = files[i].name;
    	var ext = url.substring(url.lastIndexOf('.') + 1).toLowerCase();
			if((ext == "png" || ext == "jpeg" || ext == "jpg" || ext == 'PNG' || ext == 'JPEG' || ext == 'JPG')){
				var status = new createStatusbar(obj,files[i]); //Using this we can set progress.
				status.setFileNameSize(files[i].name,files[i].size);
				statusArray[countUploadSes] =status;
				filesArray[countUploadSes] = files[i];
				countUploadSes++;
			}
   }
	 executeupload();
}
var execute = true;
function executeupload(){
	if(Object.keys(filesArray).length == 0 && jqueryObjectOfSes('#show_photo').html() != ''){
		jqueryObjectOfSes('#submit-wrapper').show();
	}
	if(execute == true){
	 for (var i in filesArray) {
		if (filesArray.hasOwnProperty(i))
    {
     	sendFileToServer(filesArray[i],statusArray[i],filesArray[i],'upload',i);
			break;
    }			
	 }
	}
}
var jqXHR = new Array();
function sendFileToServer(formData,status,file,isURL,i)
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
		jqueryObjectOfSes('#show_photo_container').addClass('iscontent');
		var url = '&isURL='+urlIs;
		var event_id = '<?php  echo $this->event_id;?>';
    var uploadURL =$('form-upload').action + '?ul=1'+url+'&event_id='+event_id; //Upload URL
    var extraData ={}; //Extra Data.
    jqXHR[i]=jqueryObjectOfSes.ajax({
		xhr: function() {
		var xhrobj = jqueryObjectOfSes.ajaxSettings.xhr();
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
		                        response = jqueryObjectOfSes.parseJSON(response);
					execute = true;
					delete filesArray[i];
					//jqueryObjectOfSes('#submit-wrapper').show();
					if (response.status) {
							var fileids = document.getElementById('fancyuploadfileids');
							fileids.value = fileids.value + response.photo_id + " ";
							status.option.find('.sesevent_upload_item_radio').find('input').attr('value',response.photo_id);
							status.src.attr('src',response.url);
							status.option.attr('data-src',response.photo_id);
							status.overlay.css('display','none');
							status.setProgress(100);
							status.abort.remove();
					}else
							status.abort.html('<span>Error In Uploading File</span>');
					executeupload();
       }
    }); 
}
//Ajax error show before form submit
var error = false;
var objectError ;
var counter = 0;
function validateForm(){
		var errorPresent = false;
		jqueryObjectOfSes('#form-upload input, #form-upload select,#form-upload checkbox,#form-upload textarea,#form-upload radio').each(
				function(index){
						var input = jqueryObjectOfSes(this);
						if(jqueryObjectOfSes(this).closest('div').parent().css('display') != 'none' && jqueryObjectOfSes(this).closest('div').parent().find('.form-label').find('label').first().hasClass('required') && jqueryObjectOfSes(this).prop('type') != 'hidden' && jqueryObjectOfSes(this).closest('div').parent().attr('class') != 'form-elements'){	
						  if(jqueryObjectOfSes(this).prop('type') == 'checkbox'){
								value = '';
								if(jqueryObjectOfSes('input[name="'+jqueryObjectOfSes(this).attr('name')+'"]:checked').length > 0) { 
										value = 1;
								};
								if(value == '')
									error = true;
								else
									error = false;
							}else if(jqueryObjectOfSes(this).prop('type') == 'select-multiple'){
								if(jqueryObjectOfSes(this).val() === '' || jqueryObjectOfSes(this).val() == null)
									error = true;
								else
									error = false;
							}else if(jqueryObjectOfSes(this).prop('type') == 'select-one' || jqueryObjectOfSes(this).prop('type') == 'select' ){
								if(jqueryObjectOfSes(this).val() === '')
									error = true;
								else
									error = false;
							}else if(jqueryObjectOfSes(this).prop('type') == 'radio'){
								if(jqueryObjectOfSes("input[name='"+jqueryObjectOfSes(this).attr('name').replace('[]','')+"']:checked").val() === '')
									error = true;
								else
									error = false;
							}else if(jqueryObjectOfSes(this).prop('type') == 'textarea'){
								if(jqueryObjectOfSes(this).val() === '' || jqueryObjectOfSes(this).val() == null)
									error = true;
								else
									error = false;
							}else{
								if(jqueryObjectOfSes(this).val() === '' || jqueryObjectOfSes(this).val() == null)
									error = true;
								else
									error = false;
							}
							if(error){
							 if(counter == 0){
							 	objectError = this;
							 }
								counter++
							}else{
							}
							if(error)
								errorPresent = true;
							error = false;
						}
				}
			);
				
			return errorPresent ;
}
jqueryObjectOfSes(document).on('submit', '#form-upload',function(e) {
		var validation = validateForm();
		if(validation)
		{
			debugger
			alert('<?php echo $this->translate("Please fill the red mark fields"); ?>');
			if(typeof objectError != 'undefined'){
			 var errorFirstObject = jqueryObjectOfSes(objectError).parent().parent();
			 jqueryObjectOfSes('html, body').animate({
        scrollTop: errorFirstObject.offset().top
    	 }, 2000);
			}
			return false;	
		}else{
			jqueryObjectOfSes('#file_multi-wrapper').remove();
			jqueryObjectOfSes('#submit').attr('disabled',true);
			jqueryObjectOfSes('#submit').html(en4.core.language.translate('Submitting Form ...'));
			return true;
		}
});
function readImageUrl(input) {
	handleFileUpload(input.files,jqueryObjectOfSes('#dragandrophandler'));
}
jqueryObjectOfSes('#dragandrophandler').click(function(){
	document.getElementById('file_multi').click();	
});
var isUploadUrl = false;
jqueryObjectOfSes(document).on('click','#upload_from_url',function(e){
	e.preventDefault();
	var url = jqueryObjectOfSes('#from_url_upload').val();
	var ext = url.substring(url.lastIndexOf('.') + 1).toLowerCase();
	var name = url.split('/').pop();
	name = name.substr(0, name.lastIndexOf('.'));
		if((ext == "png" || ext == "jpeg" || ext == "jpg" || ext == 'PNG' || ext == 'JPEG' || ext == 'JPG')){
			var status = new createStatusbar(jqueryObjectOfSes('#dragandrophandler'),url,'url'); //Using this we can set progress.
			var fd = new FormData();
			fd.append('Filedata', url);
			status.setFileNameSize(name);
			isUploadUrl = true;
			jqueryObjectOfSes('#loading_image').html('uploading ...');
			sendFileToServer(fd,status,url,'url');
			isUploadUrl = false;
			jqueryObjectOfSes('#loading_image').html('');
			jqueryObjectOfSes('#from_url_upload').val('');
   }
	 return false;
});
jqueryObjectOfSes(document).on('click','.edit_image_upload',function(e){
	e.preventDefault();
	var photo_id = jqueryObjectOfSes(this).closest('.sesevent_upload_item_options').attr('data-src');
	if(photo_id){
		editImage(photo_id);
	}else
		return false;
});
jqueryObjectOfSes(document).on('click','.delete_image_upload',function(e){
	e.preventDefault();
	jqueryObjectOfSes(this).parent().parent().find('.sesevent_upload_item_overlay').css('display','block');
	var sesthat = this;
	var isCover = jqueryObjectOfSes(this).closest('.sesevent_upload_item_options').find('.sesevent_upload_item_radio').find('input').prop('checked');
	var photo_id = jqueryObjectOfSes(this).closest('.sesevent_upload_item_options').attr('data-src');
	if(photo_id){
		request = new Request.JSON({
    'format' : 'json',
    'url' : '<?php echo $this->url(Array('module' => 'sesevent', 'controller' => 'album', 'action' => 'remove'), 'default') ?>',
    'data': {
      'photo_id' : photo_id
    },
   'onSuccess' : function(responseJSON) {
			jqueryObjectOfSes(sesthat).parent().parent().remove();
			var fileids = document.getElementById('fancyuploadfileids');
			jqueryObjectOfSes('#fancyuploadfileids').val(fileids.value.replace(photo_id + " ",''));
		if ($('album').get('value') == 0) {
			if(isCover){
				var idPhoto = jqueryObjectOfSes('#show_photo').eq(0).find('.sesevent_upload_item_radio').find('input').attr('id');
				jqueryObjectOfSes('#'+idPhoto).prop('checked', true);	
			}
		}
			if(jqueryObjectOfSes('#show_photo').html() == ''){
				jqueryObjectOfSes('#submit-wrapper').hide();
				jqueryObjectOfSes('#show_photo_container').removeClass('iscontent');
			}
     return false;
    }
    });
    request.send();
	}else
		return false;
});
<?php if(isset($_POST['file']) && $_POST['file'] != ''){ ?>
		jqueryObjectOfSes('#fancyuploadfileids').val("<?php echo $_POST['file'] ?>");    	
<?php } ?>
  function editImage(photo_id) {
    var url = '<?php echo $this->url(Array('module' => 'sesevent', 'controller' => 'album', 'action' => 'edit-photo'), 'default') ?>' + '/photo_id/'+ photo_id;
    Smoothbox.open(url);
  }
</script>