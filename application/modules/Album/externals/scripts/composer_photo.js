/* $Id: composer_photo.js 10262 2014-06-05 19:47:29Z lucas $ */

(function() { // START NAMESPACE
var $ = 'id' in document ? document.id : window.$;

Composer.Plugin.Photo = new Class({

  Extends : Composer.Plugin.Interface,

  name : 'photo',

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
    if (this.active) {
      return;
    }

    this.parent();
    this.makeMenu();
    this.makeBody();

    var fullUrl = this.options.requestOptions.url;
    var hasFlash = false;

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

    var FileSize;
    var valid = true;
    this.elements.fileElement = [];
    this.elements.filePreview = [];
    this.elements.fileRemoveLink = [];
    for (var i = 0; i < this.elements.formInput.files.length; i++) {
      FileSize = this.elements.formInput.files[i].size / 1024 / 1024; // in MB
      if(FileSize > post_max_size) {
        valid = false;
        continue;
      }

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

    if(!valid) alert("The size of the file exceeds the limits set on the server.(12MB)");

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
          //return false;
        }else{
            res['iteration'] = iteration;
            composerInstance.uploadedPhotos[res.photo_id] = res.fileName;
        }

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
    this.uploadedPhotos = [];
    this.parent();
  },

  doProcessResponse : function(responseJSON, file) {
	  if( typeof responseJSON == 'object' && typeof responseJSON.error != 'undefined' ) {
		  if( this.elements.loading ) {
			  this.elements.loading.destroy();
		  }
		  this.elements.body.empty();
		  return this.makeError(responseJSON.error, 'empty');
	  }

    // An error occurred
    if( ($type(responseJSON) != 'hash' && $type(responseJSON) != 'object') || $type(responseJSON.src) != 'string' || $type(parseInt(responseJSON.photo_id)) != 'number' ) {
      this.elements.loading ? this.elements.loading.destroy() : '';
      this.elements.body.empty();
      if( responseJSON.error == 'Invalid data' ) {
        this.makeError(this._lang('The image you tried to upload exceeds the maximum file size.'), 'empty');
      } else {
        this.makeError(this._lang(responseJSON.error), 'empty');
      }
      return;
      //throw "unable to upload image";
    }

    // Success
    if (file) {
      file = file || {};
      file.rawParams = responseJSON;
      this.setPhotoId(responseJSON.photo_id);
      this.elements.preview = Asset.image(responseJSON.src, {
        'id' : 'compose-photo-preview-image',
        'class' : 'compose-preview-image',
        'onload' : (function() {
          this.doImageLoaded(file);
        }.bind(this))
      });
      return;
    }
    // In case of ajax upload
    this.elements.filePreview[responseJSON['iteration']].removeClass('compose-photo-preview-loading');
    this.elements.filePreview[responseJSON['iteration']].setStyle(
      'backgroundImage',
      'url(' + responseJSON.src + ')'
    );
    this.elements.fileRemoveLink[responseJSON['iteration']].set('id', 'file_remove-' + responseJSON.photo_id);
    this.elements.fileRemoveLink[responseJSON['iteration']].addEvent(
      'click',
      this.removeFile.bind(this, responseJSON.photo_id)
    );
    this.setPhotoId(responseJSON.photo_id);
    this.makeFormInputs();
  },

  doImageLoaded : function(file) {
    //compose-photo-error
    if($('compose-photo-error')){
      $('compose-photo-error').destroy();
    }

    if( this.elements.loading ) this.elements.loading.destroy();
    if( this.elements.formFancyContainer ) {
      file.preview.removeClass('compose-photo-preview-loading');
      file.preview.setStyle('backgroundImage', 'url(' + this.elements.preview.src + ')' );
    } else {
      this.elements.preview.erase('width');
      this.elements.preview.erase('height');
      this.elements.preview.inject(this.elements.body);
    }
    if(this.allowToSetInInput) {
      this.makeFormInputs();
    }
  },

  removePhoto: function(removePhotoId) {
    this.setPhotoId(removePhotoId, 'remove');
    var photo_id = this.setPhotoId(removePhotoId, 'remove');
    photo_id.erase(removePhotoId);
    if (photo_id.length === 0) {
      this.getComposer().deactivate();
      this.activate();
      return;
    }
    this.makeFormInputs();
  },

  setPhotoId: function (photoId, action) {
    var photo_id =  this.params.get('photo_id') || [];
    if (action === 'remove') {
      photo_id.erase(photoId);
    } else {
      photo_id.push(photoId);
    }
    this.params.set('photo_id', photo_id);
    return photo_id;
  },

  makeFormInputs : function() {
    this.ready();
    if( this.elements.has('attachmentFormPhoto_id') ) {
      return this.setFormInputValue('photo_id', this.getPhotoIdsString());
    }

    this.parent({
      'photo_id': this.getPhotoIdsString()
    });
  },

  getPhotoIdsString : function() {
    var photo_id_str = '';
    this.params.photo_id.each(function(value) {
      photo_id_str += value + ',';
    });
    return photo_id_str.substr(0, photo_id_str.length-1);
  }
});



})(); // END NAMESPACE
