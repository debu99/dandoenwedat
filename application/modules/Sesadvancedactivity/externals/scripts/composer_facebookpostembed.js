/* $Id:composer_link.js  2017-01-12 00:00:00 SocialEngineSolutions $*/


(function() { // START NAMESPACE
var $ = 'id' in document ? document.id : window.$;
Composer.Plugin.Sesadvancedactivityfacebookpostembed = new Class({
  Extends : Composer.Plugin.Interface,
  name : 'sesadvancedactivityfacebookpostembed',
  options : {
    title : 'Add FB Embed Post',
    lang : {},
    // Options for the link preview request
    requestOptions : {},
    // Various image filtering options
    imageMaxAspect : ( 10 / 3 ),
    imageMinAspect : ( 3 / 10 ),
    imageMinSize : 48,
    imageMaxSize : 5000,
    imageMinPixels : 2304,
    imageMaxPixels : 1000000,
    imageTimeout : 5000,
    // Delay to detect links in input
    monitorDelay : 600,
    debug : false
  },

  initialize : function(options) {
    this.params = new Hash(this.params);
    this.parent(options);
  },

  attach : function() {
    this.parent();
    this.makeActivator();

    // Poll for links
    //this.interval = (function() {
    //  this.poll();
    //}).periodical(250, this);
    this.monitorLastContent = '';
    this.monitorLastMatch = '';
    this.monitorLastKeyPress = $time();
    this.getComposer().addEvent('editorKeyPress', function() {
      this.monitorLastKeyPress = $time();
    }.bind(this));
    return this;
  },

  detach : function() {
    this.parent();
    if( this.interval ) $clear(this.interval);
    return this;
  },

  activate : function() {
    if( this.active ) return;
    this.parent();

    this.makeMenu();
    this.makeBody();
    
    // Generate body contents
    // Generate form
    this.elements.formInput = new Element('input', {
      'id' : 'compose-link-form-input',
      'class' : 'compose-form-input',
      'type' : 'text'
    }).inject(this.elements.body);

    this.elements.formSubmit = new Element('button', {
      'id' : 'compose-link-form-submit',
      'class' : 'compose-form-submit',
      'html' : this._lang('Attach'),
      'events' : {
        'click' : function(e) {
          e.stop();
          this.doAttach();
        }.bind(this)
      }
    }).inject(this.elements.body);

    this.elements.formInput.focus();
  },

  deactivate : function() {
    if( !this.active ) return;
    this.parent();
    
    this.request = false;
  },

  poll : function() {
    // Active plugin, ignore
    if( this.getComposer().hasActivePlugin() ) return;
    // Recent key press, ignore
    if( $time() < this.monitorLastKeyPress + this.options.monitorDelay ) return;
    // Get content and look for links
    var content = this.getComposer().getContent();
    // Same as last body
    if( content == this.monitorLastContent ) return;
    this.monitorLastContent = content;
    // Check for match
    var m = content.match(/http:\/\/([-\w\.]+)+(:\d+)?(\/([-#:\w/_\.]*(\?\S+)?)?)?/);
    if( $type(m) && $type(m[0]) && this.monitorLastMatch != m[0] )
    {
      this.monitorLastMatch = m[0];
      this.activate();
      this.elements.formInput.value = this.monitorLastMatch;
      this.doAttach();
    }
  },


  // Getting into the core stuff now

  doAttach : function() {
    var val = this.elements.formInput.value;
    if( !val ) {
      return;
    }
//     if( !val.match(/^[a-zA-Z]{1,5}:\/\//) )
//     {
//       val = val;
//     }
    this.params.set('uri', val)
    // Input is empty, ignore attachment
    if( val == '' ) {
      e.stop();
      return;
    }

    // Send request to get attachment
    var options = $merge({
      'data' : {
        'format' : 'json',
        'uri' : val
      },
      'onComplete' : this.doProcessResponse.bind(this)
    }, this.options.requestOptions);

    // Inject loading
    this.makeLoading('empty');

    // Send request
    this.request = new Request.JSON(options);
    this.request.send();
  },

  doProcessResponse : function(responseJSON, responseText) {

    // Handle error
    if( $type(responseJSON) != 'object' ) {
      responseJSON = {
        'status' : false
      };
    }
    this.params.set('uri', responseJSON.url);

    // If google docs then just output Google Document for title and descripton
    var uristr = responseJSON.url;

    var title = responseJSON.url;
    var description = '';

    var images = responseJSON.images || [];
    if(responseJSON.gifUrl)
      title = responseJSON.gifImageUrl;
    this.params.set('title', title);
    this.params.set('description', description);
    this.params.set('images', images);
    this.params.set('loadedImages', []);
    this.params.set('thumb', '');
    this.params.set('isGif', responseJSON.isGif);
    this.params.set('gifUrl',responseJSON.gifUrl);
    this.params.set('isIframe',responseJSON.isIframe);
    this.params.set('gifImageUrl',responseJSON.gifImageUrl);
    
    if(responseJSON.isIframe) {
       this.params.set('thumb', responseJSON.thumb);
       this.elements.body.empty();
       this.makeFormInputs();
       sesJqueryObject('#compose-link-menu').hide();
      sesJqueryObject('#compose-link-body').html('<div class="composer_link_video_content_wrapper"><div class="composer_link_gif_content composer_link_iframe_content">'+responseJSON.thumb+'<a href="javascript:;" class="link_cancel_activity"><i class="fas fa-times notclose" title="'+en4.core.language.translate("CANCEL")+'"></i></a></div><div class="composer_link_iframe_content_body"><div class="compose-preview-title"><a target="_blank" href="'+responseJSON.url+'">'+title+'</a></div><div class="compose-preview-description">'+description+'</div></div></div>');
    }else if( images.length > 0 ) {
      this.doLoadImages();
    } else {
      this.doShowPreview();
    }
  },

  // Image loading
  
  doLoadImages : function() {
    // Start image load timeout
    var interval = (function() {
      // Debugging
      if( this.options.debug ) {
        console.log('Timeout reached');
      }
      this.doShowPreview();
    }).delay(this.options.imageTimeout, this);
      
    // Load them images
    this.params.loadedImages = [];

    this.params.set('assets', new Asset.images(this.params.get('images'), {
      'properties' : {
        'class' : 'compose-link-image'
      },
      'onProgress' : function(counter, index) {
        this.params.loadedImages[index] = this.params.images[index];
        // Debugging
        if( this.options.debug ) {
          console.log('Loaded - ', this.params.images[index]);
        }
      }.bind(this),
      'onError' : function(counter, index) {
        delete this.params.images[index];
      }.bind(this),
      'onComplete' : function() {
        $clear(interval);
        this.doShowPreview();
      }.bind(this)
    }));
  },


  // Preview generation
  
  doShowPreview : function() {
    var self = this;
    this.elements.body.empty();
    this.makeFormInputs();

    this.elements.previewInfo = new Element('div', {
      'id' : 'compose-link-preview-info',
      'class' : 'compose-preview-info'
    }).inject(this.elements.body);
    
    // Generate title and description
    this.elements.previewTitle = new Element('div', {
      'id' : 'compose-link-preview-title',
      'class' : 'compose-preview-title'
    }).inject(this.elements.previewInfo);

    this.elements.previewTitleLink = new Element('a', {
      'href' : this.params.uri,
      'html' : this.params.title,
      'events' : {
        'click' : function(e) {
          e.stop();
          self.handleEditTitle(this);
        }
      }
    }).inject(this.elements.previewTitle);

    this.elements.previewDescription = new Element('div', {
      'id' : 'compose-link-preview-description',
      'class' : 'compose-preview-description',
      'html' : this.params.description,
      'events' : {
        'click' : function(e) {
          e.stop();
          self.handleEditDescription(this);
        }
      }
    }).inject(this.elements.previewInfo);

  },

  makeFormInputs : function() {
    this.ready();
    this.parent({
      'uri' : this.params.uri,
      'title' : this.params.title,
      'description' : this.params.description,
      'thumb' : this.params.thumb,
      'isGif' : this.params.isGif,
      'isIframe':this.params.isIframe,
      'gifUrl' : this.params.gifUrl,
    });
  },

  handleEditTitle : function(element) {
    element.setStyle('display', 'none');
    var input = new Element('input', {
      'type' : 'text',
      'value' : element.get('text').trim(),
      'events' : {
        'blur' : function() {
          if( input.value.trim() != '' ) {
            this.params.title = input.value;
            element.set('text', this.params.title);
            this.setFormInputValue('title', this.params.title);
          }
          element.setStyle('display', '');
          input.destroy();
        }.bind(this)
      }
    }).inject(element, 'after');
    input.focus();
  },

  handleEditDescription : function(element) {
    element.setStyle('display', 'none');
    var input = new Element('textarea', {
      'html' : element.get('text').trim(),
      'events' : {
        'blur' : function() {
          if( input.value.trim() != '' ) {
            this.params.description = input.value;
            element.set('text', this.params.description);
            this.setFormInputValue('description', this.params.description);
          }
          element.setStyle('display', '');
          input.destroy();
        }.bind(this)
      }
    }).inject(element, 'after');
    input.focus();
  }
});
})(); // END NAMESPACE
sesJqueryObject(document).on('click','.link_play_activity',function(e){
  sesJqueryObject('.link_play_activity').show();
  //loop over all item and hide
  sesJqueryObject('.composer_link_gif_content').each(function(i, obj) {
    sesJqueryObject(obj).find('img').attr('src',sesJqueryObject(obj).find('img').attr('data-still'));
  });
  sesJqueryObject(this).closest('.composer_link_gif_content').find('img').attr('src',sesJqueryObject(this).closest('.composer_link_gif_content').find('img').attr('data-original'));
  sesJqueryObject(this).hide(); 
  if(!sesJqueryObject(this).closest('.feed_attachment_core_link').length)
  sesJqueryObject('.compose-link-menu').hide(); 
});
sesJqueryObject(document).on('click','.composer_link_gif_content > img',function(){
  sesJqueryObject(this).closest('.composer_link_gif_content').find('.link_play_activity').show();
  sesJqueryObject(this).closest('.composer_link_gif_content').find('img').attr('src',sesJqueryObject(this).closest('.composer_link_gif_content').find('img').attr('data-still'));
});
sesJqueryObject(document).on('click','.link_cancel_activity',function(){
   composeInstance.plugins.each(function(plugin) {
      plugin.deactivate();
      sesJqueryObject('#fancyalbumuploadfileids').val('');
   });
   composeInstance.getTray().empty(); 
})

