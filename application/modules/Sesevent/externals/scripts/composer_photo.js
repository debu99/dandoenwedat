/* $Id: composer_photo.js 9930 2013-02-18 21:02:11Z jung $ */
(function() { // START NAMESPACE
var $ = 'id' in document ? document.id : window.$;
Composer.Plugin.Seseventphoto = new Class({

  Extends : Composer.Plugin.Interface,

  name : 'sesevent_photo',

  options : {
    title : 'Add Photo',
    lang : {},
    requestOptions : false,
    fancyUploadEnabled : true,
    fancyUploadOptions : {}
  },
  allowToSetInInput: true,
  initialize : function(options) {
    this.elements = new Hash(this.elements);
    this.params = new Hash(this.params);
    this.parent(options);
    this.uploadedPhotos = [];
  },

  attach : function() {
    this.parent();
    this.makeActivator();
    return this;
  },

  detach : function() {
    this.parent();
    return this;
  },

  activate : function() {
      if( this.active ) return;
      this.parent();

      this.makeMenu();
      this.makeBody();

      // Generate form
      var fullUrl = this.options.requestOptions.url;

      var hasFlash = false;

      if(typeof sesAdvancedActivity == 'undefined'){

      this.elements.formFancyContainer = new Element('div', {
        'styles' : {
          'display' : 'none',
          'visibility' : 'hidden'
        }
      }).inject(this.elements.body);

      this.elements.scrollContainer = new Element('div', {
        'class': 'scrollbars',
        'styles' : {
          'width' : this.elements.body.getSize().x + 'px',
        }
      }).inject(this.elements.formFancyContainer);

      // This is the list
      this.elements.formFancyList = new Element('ul', {
        'class': 'compose-photos-fancy-list',
      }).inject(this.elements.scrollContainer);

      // This is the browse button
      this.elements.formFancyFile = new Element('div', {
        'id' : 'compose-photo-form-fancy-file',
        'class' : '',
      }).inject(this.elements.scrollContainer);

      this.elements.selectFileLink = new Element('a', {
        'class' : 'buttonlink',
        'html' : this._lang('Select File'),
        'styles': {
          'cursor' : 'pointer'
        }
      }).inject(this.elements.formFancyFile);

      this.elements.scrollContainer.scrollbars({
        scrollBarSize: 5,
        fade: true
      });

      // Ajax Upload Work
      this.elements.formInput = new Element('input', {
        'id' : 'compose-photo-form-input',
        'class' : 'compose-form-input',
        'type' : 'file',
        'multiple': this.options.fancyUploadOptions.limitFiles != 1,
        'value': '',
        'accepts': 'images/*',
        'events' : {
          'change' : this.onFileSelectAfter.bind(this)
        }
      }).inject(this.elements.scrollContainer);
      bindEvent = window.matchMedia("(min-width: 400px)").matches ? 'click' : 'touchend';
      this.elements.selectFileLink.addEvent(bindEvent, this.onSelectFileClick.bind(this));
      this.showForm();
      if (en4.isMobile) {
        this.elements.scrollContainer.getElement("ul.scrollbar.vertical").addClass('inactive');
        this.elements.scrollContainer.getElement("ul.scrollbar.horizontal").addClass('inactive');
      }
    }else{
      sesJqueryObject(this.elements.body).html('<input type="file" accept="image/x-png,image/jpeg" onchange="readImageUrlSesevent(this)" multiple="multiple" id="file_multi" name="file_multi" style="display:none"><div class="advact_compose_photo_container sesbasic_custom_horizontal_scroll sesbasic_clearfix"><div id="advact_compose_photo_container_inner" class="sesbasic_clearfix"><div id="show_photo"></div><div id="dragandrophandlerenvent" class="advact_compose_photo_uploader" title="Choose a file to upload"><i class="fa fa-plus"></i></div></div></div>');

				jqueryObjectOfSes(".sesbasic_custom_horizontal_scroll").mCustomScrollbar({
					axis:"x",
					theme:"light-3",
					advanced:{autoExpandHorizontalScroll:true}
				})
    }
    if(sesJqueryObject('#toValues-wrapper').length > 0 || sesJqueryObject('#submit-wrapper').length > 0){
      //sesJqueryObject('#file_multi').removeAttr('multiple');
    }
    if(sesJqueryObject('#toValues-wrapper').length > 0){
      sesJqueryObject('#toValues-wrapper').append('<div><input type="hidden" value="1" id="messageAttachment" name="attachment[messageAttachment]"><input type="hidden" value="" id="fancyalbumuploadfileidsevent" name="attachment[photo_id]"><input type="hidden" value="seseventphoto" id="photosesevent" name="attachment[type]"></div>');
    }else if(sesJqueryObject('#submit-wrapper').length > 0){
      sesJqueryObject('#body-wrapper').append('<div><input type="hidden" value="1" id="messageAttachment" name="attachment[messageAttachment]"><input type="hidden" value="seseventphoto" id="photosesevent" name="attachment[type]"><input type="hidden" value="" id="fancyalbumuploadfileidsevent" name="attachment[photo_id]"></div>');
    }
  },
  onSelectFileClick: function () {
    this.elements.formInput.click();
  },

  onFileSelectAfter: function() {
    this.elements.formFancyList.style.display = 'inline-block';
    this.getComposer().getMenu().setStyle('display', 'none');
    if (this.elements.formInput.files.length === 0) {
      return;
    }
    this.elements.fileElement = [];
    this.elements.filePreview = [];
    this.elements.fileRemoveLink = [];
    for (var i = 0; i < this.elements.formInput.files.length; i++) {
      if (!this.canUploadPhoto(this.elements.formInput.files[i])) {
        this.getComposer().getMenu().setStyle('display', '');
        continue;
      }
      this.elements.fileElement[i] = new Element('li', {
          'class' : 'file compose-photo-preview',
        }).inject(this.elements.formFancyList);

      this.elements.filePreview[i] = new Element('span', {
        'class' : 'compose-photo-preview-image compose-photo-preview-loading',
      }).inject(this.elements.fileElement[i]);

      var overlay = new Element('span', {
        'class' : 'compose-photo-preview-overlay',
      }).inject(this.elements.filePreview[i], 'after');

      this.elements.fileRemoveLink[i] = new Element('a', {
        'class': 'file-remove',
         html: 'X',
         title: 'Click to remove this entry.',
      }).inject(overlay);
      this.uploadFile(this.elements.formInput.files[i], i);

      if (this.canUploadPhoto(null) !== true) {
        this.elements.formFancyFile.setStyle('display', 'none');
        break;
      }
    }
    this.elements.formInput.value = '';
    this.updateScrollBar();
    var scrollbarContent = this.elements.formFancyList.getParent('.scrollbar-content');
    scrollbarContent.scrollTo(this.elements.formFancyFile.getPosition().x, scrollbarContent.getScroll().y);
  },

  uploadFile: function (file, iteration) {
    var xhr = new XMLHttpRequest();
    var fd = new FormData();
    xhr.open("POST", this.options.requestOptions.url, true);
    var composerInstance = this;
    xhr.onreadystatechange = function() {
      if (xhr.readyState === 4 && xhr.status === 200) {
        var res = JSON.parse(xhr.responseText);

        if (res['error'] !== undefined) {
          return false;
        }
        res['iteration'] = iteration;
        composerInstance.uploadedPhotos[res.photo_id] = res.fileName;
        composerInstance.doProcessResponse(res);
      }
    };
    fd.append('ajax-upload', 'true');
    fd.append('Filedata', file);
    xhr.send(fd);
  },

  removeFile: function(photo_id) {
    var composerInstance = this;
    $('file_remove-' + photo_id).getParent('li.compose-photo-preview').destroy();
    composerInstance.removePhoto(photo_id);
    delete composerInstance.uploadedPhotos[photo_id];
    if (this.canUploadPhoto(null)) {
      this.elements.formFancyFile.setStyle('display', '');
    }
    (function() {composerInstance.updateScrollBar();}).delay(1000);
  },

  showForm: function () {
    this.elements.formFancyContainer.setStyle('display', '');
    this.elements.formInput.setStyle('display', 'none');
    this.elements.formFancyContainer.setStyle('visibility', 'visible');
  },

  canUploadPhoto: function (photo) {
    if (photo === null) {
      return this.options.fancyUploadOptions.limitFiles === 0 ||
        $$('ul.compose-photos-fancy-list li').length < this.options.fancyUploadOptions.limitFiles;
    }
    if (this.uploadedPhotos.length === 0) {
      return true;
    }
    return this.uploadedPhotos.every(function (uploadedPhoto) {
        return uploadedPhoto !== photo.name;
    });
  },

  updateScrollBar: function () {
    var height = this.elements.formFancyFile.offsetHeight;
    if( height == 0 ) {
      height = 106;
    }
    var scrollbarContent = this.elements.formFancyList.getParent();
    scrollbarContent.setStyle('height', height + 20);
    var li = this.elements.formFancyList.getElements('li');
    scrollbarContent.setStyle('width', ((li[0].getSize().x + 11) * li.length) + this.elements.formFancyFile.getSize().x + 10);
    this.elements.scrollContainer.retrieve('scrollbars').updateScrollBars();
    scrollbarContent.getParent().setStyle('overflow', 'hidden');
  },


  deactivate : function() {
    if( !this.active ) return;
    this.parent();
    $('compose-photo-activator').style.display = 'none';
    sesJqueryObject('fancyalbumuploadfileidsevent').remove();
    sesJqueryObject('#photosesevent').remove();
    sesJqueryObject('#messageAttachment').remove();
  },

  doRequest : function() {
    this.elements.iframe = new IFrame({
      'name' : 'composePhotoFrame',
      'src' : 'javascript:false;',
      'styles' : {
        'display' : 'none'
      },
      'events' : {
        'load' : function() {
          this.doProcessResponse(window._composePhotoResponse);
          window._composePhotoResponse = false;
        }.bind(this)
      }
    }).inject(this.elements.body);
    window._composePhotoResponse = false;
    this.elements.form.set('target', 'composePhotoFrame');

    // Submit and then destroy form
    this.elements.form.submit();
    this.elements.form.destroy();

    // Start loading screen
    this.makeLoading();
  },

  doProcessResponse : function(responseJSON) {
    // An error occurred
    if( ($type(responseJSON) != 'hash' && $type(responseJSON) != 'object') || $type(responseJSON.src) != 'string' || $type(parseInt(responseJSON.photo_id)) != 'number' ) {
      //this.elements.body.empty();
      this.makeError(this._lang('Unable to upload photo. Please click cancel and try again'), '');
      return;
      //throw "unable to upload image";
    }

    // Success
    this.params.set('rawParams', responseJSON);
    this.params.set('photo_id', responseJSON.photo_id);
    this.elements.preview = Asset.image(responseJSON.src, {
      'id' : 'compose-photo-preview-image',
      'class' : 'compose-preview-image',
      'onload' : this.doImageLoaded.bind(this)
    });
  },

  doImageLoaded : function() {
    //compose-photo-error
    if($('compose-photo-error')){
      $('compose-photo-error').destroy();
    }

    if( this.elements.loading ) this.elements.loading.destroy();
    if( this.elements.formFancyContainer ) this.elements.formFancyContainer.destroy();
    this.elements.preview.erase('width');
    this.elements.preview.erase('height');
    this.elements.preview.inject(this.elements.body);
    this.makeFormInputs();
  },

  makeFormInputs : function() {
    this.ready();
    this.parent({
      'photo_id' : this.params.photo_id
    });
  }

});



})(); // END NAMESPACE
