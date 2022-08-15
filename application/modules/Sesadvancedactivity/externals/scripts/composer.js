/* $Id:composer.js  2017-01-12 00:00:00 SocialEngineSolutions $*/

var sessctWidth = 0;

(function() { // START NAMESPACE
var $ = 'id' in document ? document.id : window.$;



Composer = new Class({

  Implements : [Events, Options],

  elements : {},

  plugins : {},

  options : {
    lang : {},
    overText : true,
    allowEmptyWithoutAttachment : false,
    allowEmptyWithAttachment : true,
    hideSubmitOnBlur : false,
    submitElement : false,
    useContentEditable : true
  },

  initialize : function(element, options) {
    this.setOptions(options);
    this.elements = new Hash(this.elements);
    this.plugins = new Hash(this.plugins);

    this.elements.textarea = $(element);
    this.elements.textarea.store('Composer');

    this.attach();
    this.getTray();
    this.getMenu();

    this.pluginReady = false;

    this.getForm().addEvent('submit', function(e) {
      var activatedPlugin = this.getActivePlugin();
      if(activatedPlugin)
        var pluginName = activatedPlugin.getName();
      else
        var pluginName = '';

     //feeling work
      if(pluginName != 'buysell' && pluginName != 'quote' && pluginName != 'prayer' && pluginName != 'wishe' && pluginName != 'thought' && pluginName != 'text' && !sesJqueryObject('#image_id').val() && !sesJqueryObject('#reaction_id').val() && !sesJqueryObject('#tag_location').val() && !sesJqueryObject('#feeling_activity').val() && !sesJqueryObject('#feedbgid').val()){
        if( this.pluginReady ) {
          if( !this.options.allowEmptyWithAttachment && this.getContent() == '' ) {
            e.stop();
             sesJqueryObject('.sesact_post_box').addClass('_blank');

             //sesJqueryObject('#activity-form').removeClass('feed_background_image');
             sesJqueryObject('.sesact_post_box').css('background-image', 'none');
            return;
          }
        } else {
          if( !this.options.allowEmptyWithoutAttachment && this.getContent() == '' ) {
            e.stop();
             sesJqueryObject('.sesact_post_box').addClass('_blank');

             //sesJqueryObject('#activity-form').removeClass('feed_background_image');
             sesJqueryObject('.sesact_post_box').css('background-image', 'none');
            return;
          }
        }

         sesJqueryObject('.sesact_post_box').removeClass('_blank');
      }
      //Feed Background Image Work
//       if(sesJqueryObject('#feedbgid').val()) {
//         sesJqueryObject('.sesact_post_box').css('background-image', 'none');
//         sesJqueryObject('#activity-form').removeClass('feed_background_image');
//         sesJqueryObject('#feedbg_main_continer').css('style', 'none');
//       }
      this.saveContent();
    }.bind(this));
  },

  getMenu : function() {
    if( !$type(this.elements.menu) ) {
      this.elements.menu = $try(function(){
        return $(this.options.menuElement);
      }.bind(this));

      if( !$type(this.elements.menu) ) {
        this.elements.menu = new Element('div',{
          'id' : 'compose-menu',
          'class' : 'compose-menu'
        }).inject(this.getForm(), 'after');
      }
    }
    return this.elements.menu;
  },

  getTray : function() {
    if( !$type(this.elements.tray) ) {
      this.elements.tray = $try(function(){
        return $(this.options.trayElement);
      }.bind(this));

      if( !$type(this.elements.tray) ) {
        this.elements.tray =  new Element('div',{
          'id' : 'compose-tray',
          'class' : 'compose-tray',
          'styles' : {
            'display' : 'none'
          }
        }).inject('sescomposer-tray-container');
      }
    }
    return this.elements.tray;
  },

  getInputArea : function() {
    sesJqueryObject('.fileupload-cnt').remove();
    //if(!sesJqueryObject('.fileupload-cnt').length) {
      var form = this.elements.textarea.getParent('form');
      this.elements.inputarea = new Element('div', {
        'class':'fileupload-cnt',
        'styles' : {
          'display' : 'none'
        }
      }).inject(form);
   // }
    return this.elements.inputarea;
  },

  getForm : function() {
    return this.elements.textarea.getParent('form');
  },



  // Editor

  attach : function() {
    var size = this.elements.textarea.getSize();

    // Modify textarea
    this.elements.textarea.addClass('compose-textarea').setStyle('display', 'none');

    // Create container
    this.elements.container = new Element('div', {
      'id' : 'compose-container',
      'class' : 'compose-container',
      'styles' : {

      }
    });
    this.elements.container.wraps(this.elements.textarea);

    // Create body
    var supportsContentEditable = this._supportsContentEditable();

    if( supportsContentEditable ) {
      this.elements.body = new Element('div', {
        'class' : 'compose-content',
        'styles' : {
          'display' : 'block'
        },
        'events' : {
          'keypress' : function(event) {
            if( event.key == 'a' && event.control ) {
              // FF only
              if( Browser.Engine.gecko ) {
                fix_gecko_select_all_contenteditable_bug(this, event);
              }
            }
          }
        }
      }).inject(this.elements.textarea, 'before');
    } else {
      this.elements.body = this.elements.textarea;
    }

    // Attach blur event
    var self = this;
    this.elements.body.addEvent('blur', function(e) {
      var curVal;
      if( supportsContentEditable ) {
        curVal = this.get('html').replace(/\s/, '').replace(/<[^<>]+?>/ig, '');
      } else {
        curVal = this.get('value').replace(/\s/, '').replace(/<[^<>]+?>/ig, '')
      }
      if( '' == curVal ) {
        if( !Browser.Engine.trident ) {
          if( supportsContentEditable ) {
            this.set('html', '<br />');
          } else {
            this.set('value', '');
          }
        }
        if( self.options.hideSubmitOnBlur ) {
          (function() {
            if( !self.hasActivePlugin() ) {
              self.getMenu().setStyle('display', 'none');
            }
          }).delay(250);
        }
      }
    });

    if( self.options.hideSubmitOnBlur ) {
      this.getMenu().setStyle('display', 'none');
      this.elements.body.addEvent('focus', function(e) {
        self.getMenu().setStyle('display', '');
      });
    }

    if( supportsContentEditable ) {
      $(this.elements.body);
      this.elements.body.contentEditable = true;
      this.elements.body.designMode = 'On';

      ['MouseUp', 'MouseDown', 'ContextMenu', 'Click', 'Dblclick', 'KeyPress', 'KeyUp', 'KeyDown','Paste'].each(function(eventName) {
        var method = (this['editor' + eventName] || function(){}).bind(this);
        this.elements.body.addEvent(eventName.toLowerCase(), method);
      }.bind(this));

      this.setContent(this.elements.textarea.value);

      this.selection = new Composer.Selection(this.elements.body);
    } else {
      this.elements.textarea.setStyle('display', '');
    }

    if( this.options.overText && supportsContentEditable ) {
      new Composer.OverText(this.elements.body, $merge({
        textOverride : this._lang('Post Something...'),
        poll : true,
        isPlainText : !supportsContentEditable,
        positionOptions: {
          position: ( en4.orientation == 'rtl' ? 'upperRight' : 'upperLeft' ),
          edge: ( en4.orientation == 'rtl' ? 'upperRight' : 'upperLeft' ),
          offset: {
            x: ( en4.orientation == 'rtl' ? -4 : 4 ),
            y: 2
          }
        }
      }, this.options.overTextOptions));
    }

    this.fireEvent('attach', this);


       isonCommentBox = false;
       if(!sesJqueryObject('#activity_body').attr('id'))
        sesJqueryObject('#activity_body').attr('id',new Date().getTime());

       var data = sesJqueryObject('#activity_body').val();
       //var data = composeInstance.getContent();

      if(!sesJqueryObject('#activity_body').val() || isOnEditField || sesJqueryObject('#hashtagtextsesadv').val()){
      //if(!composeInstance.getContent() || isOnEditField || sesJqueryObject('#hashtagtextsesadv').val()){
        if(!sesJqueryObject('#activity_body').val() )
        //if(!composeInstance.getContent() )
          EditFieldValue = '';
        sesJqueryObject('#activity_body').mentionsInput({
            onDataRequest:function (mode, query, callback) {
             sesJqueryObject.getJSON('sesadvancedactivity/ajax/friends/query/'+query, function(responseData) {
              responseData = _.filter(responseData, function(item) { return item.name.toLowerCase().indexOf(query.toLowerCase()) > -1 });
              callback.call('#activity_body', responseData);
            });
          },
          //defaultValue: EditFieldValue,
          onCaret: true
        });
      }

      if(data){
         getDataMentionEdit('#activity_body',data);
      }

      if(!sesJqueryObject('#activity_body').parent().hasClass('typehead')){
        sesJqueryObject('#activity_body').hashtags();
      }
      setTimeout(function(){ sesJqueryObject('#activity_body').mentionsInput("update"); }, 1000);

  },

  detach : function() {
    this.saveContent();
    this.textarea.setStyle('display', '').removeClass('compose-textarea').inject(this.container, 'before');
    this.container.dispose();
    this.fireEvent('detach', this);
    return this;
  },

  focus: function(){
    // needs the delay to get focus working
    (function(){
      this.elements.body.focus();
      this.fireEvent('focus', this);
    }).bind(this).delay(10);
    return this;
  },



  // Content

  getContent: function(){
    return this.elements.textarea.get('value');
  },

  setContent: function(newContent) {
    //sesJqueryObject('#activity_body_emojis').val(newContent);
    this.elements.textarea.set('value',newContent);
    return this;
  },


  saveContent: function(){
    if( this._supportsContentEditable() ) {
      this.elements.textarea.set('value', this.getContent());
    }
    return this;
  },

  cleanup : function(html) {
    // @todo
    return html
      .replace(/<(br|p|div)[^<>]*?>/ig, "\r\n")
      .replace(/<[^<>]+?>/ig, ' ')
      .replace(/(\r\n?|\n){3,}/ig, "\n\n")
      .trim();
  },



  // Plugins

  addPlugin : function(plugin) {
    var key = plugin.getName();
    this.plugins.set(key, plugin);
    plugin.setComposer(this);
    return this;
  },

  addPlugins : function(plugins) {
    plugins.each(function(plugin) {
      this.addPlugin(plugin);
    }.bind(this));
  },

  getPlugin : function(name) {
    return this.plugins.get(name);
  },

  activate : function(name) {
    this.deactivate();
    this.getMenu().setStyle();
    this.plugins.get(name).activate();
  },

  deactivate : function() {
    this.plugins.each(function(plugin) {
      plugin.deactivate();
      sesJqueryObject('#compose-'+plugin.getName()+'-activator').parent().removeClass('active');
    });
    sesJqueryObject('#fancyalbumuploadfileids').val('');
    sesJqueryObject('#reaction_id').val('');
    sesJqueryObject('.fileupload-cnt').html('');
    this.getTray().empty();
    var textAreal ='activity_body';
    var className = 'highlighter';
    if(sesadvancedactivitybigtext) {
      var textlength = sesJqueryObject('#'+textAreal).val().length;
      if(textlength <= sesAdvancedactivitytextlimit) {
        sesJqueryObject('.'+className).css("fontSize", sesAdvancedactivityfonttextsize);
        sesJqueryObject('#'+textAreal).css("fontSize", sesAdvancedactivityfonttextsize);
      } else {
        sesJqueryObject('.'+className).css("fontSize", '');
        sesJqueryObject('#'+textAreal).css("fontSize", '');
      }
    }
    if(sesadvancedactivityDesign == 4) {
      sesJqueryObject('#sesact_post_box_status').show();
    }
  },

  signalPluginReady : function(state) {
    this.pluginReady = state;
  },
  getActivePlugin : function() {

    var activeplugin = false;
    this.plugins.each(function(plugin) {
      if(plugin.active)
        activeplugin = plugin;
    });
    return activeplugin;
  },
  hasActivePlugin : function() {
    var active = false;
    this.plugins.each(function(plugin) {
      active = active || plugin.active;
    });
    return active;
  },



  // Key events

  editorMouseUp: function(e){
    this.fireEvent('editorMouseUp', e);
  },

  editorMouseDown: function(e){
    this.fireEvent('editorMouseDown', e);
  },

  editorContextMenu: function(e){
    this.fireEvent('editorContextMenu', e);
  },

  editorClick: function(e){
    // make images selectable and draggable in Safari
    if (Browser.Engine.webkit){
      var el = e.target;
      if (el.get('tag') == 'img'){
        this.selection.selectNode(el);
      }
    }

    this.fireEvent('editorClick', e);
  },

  editorDoubleClick: function(e){
    this.fireEvent('editorDoubleClick', e);
  },

  editorKeyPress: function(e){
    this.keyListener(e);
    this.fireEvent('editorKeyPress', e);
  },

  editorKeyUp: function(e){
    this.fireEvent('editorKeyUp', e);
      setTimeout(function () {
        linkDetection();
      }, 0);
			var str = this.getContent();
			//sesJqueryObject(this).parent().parent().find(".highlighter").css("width",$(this).css("width"));
			str = str.replace(/\n/g, '<br>');
			if(!str.match(/(http|ftp|https):\/\/[\w-]+(\.[\w-]+)+([\w.,@?^=%&amp;:\/~+#-]*[\w@?^=%&amp;\/~+#-])?#([a-zA-Z0-9]+)/g) && !str.match(/(http|ftp|https):\/\/[\w-]+(\.[\w-]+)+([\w.,@?^=%&amp;:\/~+#-]*[\w@?^=%&amp;\/~+#-])?@([a-zA-Z0-9]+)/g) && !str.match(/(http|ftp|https):\/\/[\w-]+(\.[\w-]+)+([\w.,@?^=%&amp;:\/~+#-]*[\w@?^=%&amp;\/~+#-])?#([\u0600-\u06FF]+)/g) && !str.match(/(http|ftp|https):\/\/[\w-]+(\.[\w-]+)+([\w.,@?^=%&amp;:\/~+#-]*[\w@?^=%&amp;\/~+#-])?@([\u0600-\u06FF]+)/g)) {
        if(!str.match(/#(([a-zA-Z0-9]+)|([\u0600-\u06FF]+))#/g)) { //arabic support
					str = str.replace(/#(([a-zA-Z0-9]+)|([\u0600-\u06FF]+))/g,'<span class="hashtag">#$1</span>');
				}else{
					str = str.replace(/#(([a-zA-Z0-9]+)|([\u0600-\u06FF]+))#(([a-zA-Z0-9]+)|([\u0600-\u06FF]+))/g,'<span class="hashtag">#$1</span>');
				}
				if(!str.match(/@(([a-zA-Z0-9]+)|([\u0600-\u06FF]+))@/g)) {
					//str = str.replace(/@(([a-zA-Z0-9]+)|([\u0600-\u06FF]+))/g,'<span class="hashtag">@$1</span>');
				}else{
					//str = str.replace(/@(([a-zA-Z0-9]+)|([\u0600-\u06FF]+))@(([a-zA-Z0-9]+)|([\u0600-\u06FF]+))/g,'<span class="hashtag">@$1</span>');
				}
			}
			this.setContent(str);

  },
  editorPaste: function(e) {
    this.fireEvent('editorPaste', e);
    setTimeout(function () {
      linkDetection();
    }, 0);
  },
  editorKeyDown: function(e){
    this.fireEvent('editorKeyDown', e);
  },

  keyListener: function(e){

  },
  _lang : function() {
    try {
      if( arguments.length < 1 ) {
        return '';
      }

      var string = arguments[0];
      if( $type(this.options.lang) && $type(this.options.lang[string]) ) {
        string = this.options.lang[string];
      }

      if( arguments.length <= 1 ) {
        return string;
      }

      var args = new Array();
      for( var i = 1, l = arguments.length; i < l; i++ ) {
        args.push(arguments[i]);
      }

      return string.vsprintf(args);
    } catch( e ) {
      alert(e);
    }
  },

  _supportsContentEditable : function() {
    return false;
  }
});



Composer.Selection = new Class({

  initialize: function(win){
    this.win = win;
  },

  getSelection: function(){
    //this.win.focus();
    return window.getSelection();
  },

  getRange: function(){
    var s = this.getSelection();

    if (!s) return null;

    try {
      return s.rangeCount > 0 ? s.getRangeAt(0) : (s.createRange ? s.createRange() : null);
    } catch(e) {
      // IE bug when used in frameset
      return document.body.createTextRange();
    }
  },

  setRange: function(range){
    if (range.select){
      $try(function(){
        range.select();
      });
    } else {
      var s = this.getSelection();
      if (s.addRange){
        s.removeAllRanges();
        s.addRange(range);
      }
    }
  },

  selectNode: function(node, collapse){
    var r = this.getRange();
    var s = this.getSelection();

    if (r.moveToElementText){
      $try(function(){
        r.moveToElementText(node);
        r.select();
      });
    } else if (s.addRange){
      collapse ? r.selectNodeContents(node) : r.selectNode(node);
      s.removeAllRanges();
      s.addRange(r);
    } else {
      s.setBaseAndExtent(node, 0, node, 1);
    }

    return node;
  },

  isCollapsed: function(){
    var r = this.getRange();
    if (r.item) return false;
    return r.boundingWidth == 0 || this.getSelection().isCollapsed;
  },

  collapse: function(toStart){
    var r = this.getRange();
    var s = this.getSelection();

    if (r.select){
      r.collapse(toStart);
      r.select();
    } else {
      toStart ? s.collapseToStart() : s.collapseToEnd();
    }
  },

  getContent: function(){
    var r = this.getRange();
    var body = new Element('body');

    if (this.isCollapsed()) return '';

    if (r.cloneContents){
      body.appendChild(r.cloneContents());
    } else if ($defined(r.item) || $defined(r.htmlText)){
      body.set('html', r.item ? r.item(0).outerHTML : r.htmlText);
    } else {
      body.set('html', r.toString());
    }

    var content = body.get('html');
    return content;
  },

  getText : function(){
    var r = this.getRange();
    var s = this.getSelection();

    return this.isCollapsed() ? '' : r.text || s.toString();
  },

  getNode: function(){
    var r = this.getRange();

    if (!Browser.Engine.trident){
      var el = null;

      if (r){
        el = r.commonAncestorContainer;

        // Handle selection a image or other control like element such as anchors
        if (!r.collapsed)
          if (r.startContainer == r.endContainer)
            if (r.startOffset - r.endOffset < 2)
              if (r.startContainer.hasChildNodes())
                el = r.startContainer.childNodes[r.startOffset];

        while ($type(el) != 'element') el = el.parentNode;
      }

      return $(el);
    }

    return $(r.item ? r.item(0) : r.parentElement());
  },

  insertContent: function(content){
    var r = this.getRange();

    if (r.insertNode){
      r.deleteContents();
      r.insertNode(r.createContextualFragment(content));
    } else {
      // Handle text and control range
      (r.pasteHTML) ? r.pasteHTML(content) : r.item(0).outerHTML = content;
    }
  }

});


Composer.OverText = new Class({

  Extends : OverText,

  test : function() {
    if( !$type(this.options.isPlainText) || !this.options.isPlainText ) {
      return !this.element.get('html').replace(/\s+/, '').replace(/<br.*?>/, '');
    } else {
      return this.parent();
    }
  },

  hide: function(suppressFocus, force){
    if (this.text && (this.text.isDisplayed() && (!this.element.get('disabled') || force))){
      this.text.hide();
      this.fireEvent('textHide', [this.text, this.element]);
      this.pollingPaused = true;
      try {
        this.element.fireEvent('focus');
        this.element.focus();
      } catch(e){} //IE barfs if you call focus on hidden elements
    }
    return this;
  }

})


Composer.Plugin = {};

Composer.Plugin.Interface = new Class({

  Implements : [Options, Events],

  name : 'interface',

  active : false,

  composer : false,

  options : {
    loadingImage : en4.core.staticBaseUrl + 'application/modules/Core/externals/images/loading.gif'
  },

  elements : {},

  persistentElements : ['activator', 'loadingImage','aActivator','sactivator'],

  params : {},

  initialize : function(options) {
    this.params = new Hash();
    this.elements = new Hash();
    this.reset();
    this.setOptions(options);
  },

  getName : function() {
    return this.name;
  },

  setComposer : function(composer) {
    this.composer = composer;
    this.attach();
    return this;
  },

  getComposer : function() {
    if( !this.composer ) throw "No composer defined";
    return this.composer;
  },

  attach : function() {
    this.reset();
  },

  detach : function() {
    this.reset();
    if( this.elements.activator ) {
      this.elements.activator.destroy();
      this.elements.erase('menu');
    }
  },

  reset : function() {
    this.elements.each(function(element, key) {
      if( $type(element) == 'element' && !this.persistentElements.contains(key) ) {
        element.destroy();
        this.elements.erase(key);
      }
    }.bind(this));
    this.params = new Hash();
    this.elements = new Hash();
  },

  activate : function() {

    var textAreal ='activity_body';
    var className = 'highlighter';
    if(sesadvancedactivitybigtext) {
      sesJqueryObject('.'+className).css("fontSize", '');
      sesJqueryObject('#'+textAreal).css("fontSize", '');
    }
    if( this.active ) return;

    //Feed Background image work
    if($('feedbgid')) {
      sesJqueryObject('#feedbgid_isphoto').val(0);
      sesJqueryObject('#feedbgid').val(0);
      sesJqueryObject('.sesact_post_box').css('background-image', 'none');
      sesJqueryObject('#activity-form').removeClass('feed_background_image');
      sesJqueryObject('#feedbg_main_continer').css('display','none');
      $('hideshowfeedbgcont').style.display = 'none';
      sesJqueryObject('#feedbg_content').css('display','none');
    }
    //Feed Background image work

    this.getComposer().getTray().empty();
    sesJqueryObject('#fancyalbumuploadfileids').val('');
    sesJqueryObject('#reaction_id').val('');
    sesJqueryObject('.fileupload-cnt').html('');
    this.getComposer().plugins.each(function(plugin) {
      plugin.active = false;
      sesJqueryObject('#compose-'+plugin.getName()+'-activator').parent().removeClass('active');
    });
    sesJqueryObject('#compose-'+this.getName()+'-activator').parent().addClass('active');
    this.active = true;
    this.reset();
    this.getComposer().getTray().setStyle('display', '');
    if(this.getName() == 'sesevent')
      this.getComposer().getTray().setStyle('display', 'none');
      //this.getComposer().getMenu().setStyle('display', 'none');
     //var submitButtonEl = $(this.getComposer().options.submitElement);
    //    if( submitButtonEl ) {
   //      submitButtonEl.setStyle('display', 'none');
  //    }

    this.getComposer().getMenu().setStyle('border', 'none');

    this.getComposer().getMenu().getElements('.compose-activator').each(function(element) {
      element.setStyle('display', 'none');
    });

    switch( $type(this.options.loadingImage) ) {
      case 'element':
        break;
      case 'string':
        this.elements.loadingImage = new Asset.image(this.options.loadingImage, {
          'id' : 'compose-' + this.getName() + '-loading-image',
          'class' : 'compose-loading-image'
        });
        break;
      default:
        this.elements.loadingImage = new Asset.image('loading.gif', {
          'id' : 'compose-' + this.getName() + '-loading-image',
          'class' : 'compose-loading-image'
        });
        break;
    }


  },

  deactivate : function() {
    if( !this.active ) return;
    this.active = false;

    this.reset();
    this.getComposer().getTray().setStyle('display', 'none');
    this.getComposer().getMenu().setStyle('display', '');
    var submitButtonEl = $(this.getComposer().options.submitElement);
    if( submitButtonEl ) {
      submitButtonEl.setStyle('display', '');
    }
    this.getComposer().getMenu().getElements('.compose-activator').each(function(element) {
      element.setStyle('display', '');
    });

    this.getComposer().getMenu().set('style', '');

    this.getComposer().signalPluginReady(false);
    sesJqueryObject('#fancyalbumuploadfileids').val('');
    sesJqueryObject('#reaction_id').val('');
    sesJqueryObject('.fileupload-cnt').html('');

    //Feed Background Image Work
    if($('feedbgid')) {
      $('hideshowfeedbgcont').style.display = 'block';
      sesJqueryObject('#feedbg_content').css('display','block');
      sesJqueryObject('#feedbg_main_continer').css('display','block');
    }
  },

  ready : function() {
    this.getComposer().signalPluginReady(true);
    this.getComposer().getMenu().setStyle('display', '');

    var submitEl = $(this.getComposer().options.submitElement);
    if( submitEl ) {
      submitEl.setStyle('display', '');
    }
  },


  // Utility

  makeActivator : function() {
    if( !this.elements.activator ) {
      var moreTab = false;
      var spanInsertBefore = 'sesact_post_media_options_before';
      if(sesadvancedactivityDesign == 1){
        this.elements.activator = new Element('span', {
          'html' :  '',
          'class': 'sesact_post_tool_i tool_i_'+this.getName(),
        });

        //Advanced Activity album work
        if(this.getName() == 'album') {
          this.elements.aActivator = new Element('a', {
            'id' : 'compose-' + this.getName() + '-activator',
            'class' : 'sesadv_tooltip sesalbum_popup_sesadv ',
            'href' : 'javascript:;',
            'data-url' : 'sesalbum/index/create/params/anfwallalbum/ispopup/'+isOpenPopup,
            'title' : this._lang(this.options.title),
          }).inject(this.elements.activator);
        } else {
          this.elements.aActivator = new Element('a', {
            'id' : 'compose-' + this.getName() + '-activator',
            'class' : 'sesadv_tooltip',
            'href' : 'javascript:;',
            'title' : this._lang(this.options.title),
            'events' : {
              'click' : this.activate.bind(this)
            }
          }).inject(this.elements.activator);
        }
        this.elements.activator.inject($('sesadvancedactivity-menu-selector'),'before');
      }else if(sesadvancedactivityDesign == 2 || sesadvancedactivityDesign == 4){

        var displayCI  = 'block';
        if(counterLoopComposerItem == 3) {
          var html = sesJqueryObject('<span class="sesact_post_media_options_icon tool_i_more"><a href="javascript:void(0);" title="More" class="sesadv_tooltip"><i></i></a></span>').insertBefore(sesJqueryObject('#sesact_post_media_options_before'));
        }
        if(counterLoopComposerItem > 2)
           displayCI = 'none';
        counterLoopComposerItem++;
        this.elements.activator = new Element('span', {
          'html' :  '',
          'style': 'display:'+displayCI,
          'class': 'sesact_post_media_options_icon tool_i_'+this.getName(),
        });

        //Album Work
        if(this.getName() == 'album') {
          this.elements.aActivator = new Element('a', {
            'id' : 'compose-' + this.getName() + '-activator',
            'class' : 'sesadv_tooltip sesalbum_popup_sesadv',
            'href' : 'javascript:;',
            'data-url' : 'sesalbum/index/create/params/anfwallalbum/ispopup/'+isOpenPopup,
            'title' : this._lang(this.options.title),
          }).inject(this.elements.activator);
        } else if(this.getName() == 'sesblog') {
          this.elements.aActivator = new Element('a', {
            'id' : 'compose-' + this.getName() + '-activator',
             'class' : 'sesadv_tooltip sessmoothbox',
             'href' : 'javascript:;',
             'data-url' : 'sesblog/index/create/',
             'title' : this._lang(this.options.title),
          }).inject(this.elements.activator);
        } else if(this.getName() == 'sescustomlistingreview') {
          this.elements.aActivator = new Element('a', {
            'id' : 'compose-' + this.getName() + '-activator',
            'class' : 'sesadv_tooltip sessmoothbox',
            'href' : 'javascript:;',
            'data-url' : 'sescustomlisting/review/post',
            'title' : this._lang(this.options.title),
          }).inject(this.elements.activator);
        } else {
          this.elements.aActivator = new Element('a', {
              'id' : 'compose-' + this.getName() + '-activator',
              'class' : 'sesadv_tooltip',
              'href' : 'javascript:;',
              'title' : this._lang(this.options.title),
              'events' : {
                'click' : this.activate.bind(this)
              }
          }).inject(this.elements.activator);
        }
        this.elements.sactivator = new Element('span', {
          'html' :  this._lang(this.options.title),
        }).inject(this.elements.aActivator);

        this.elements.activator.inject($('sesact_post_media_options_before'),'before');

        if(sesadvancedactivityDesign == 4) {

          var displayDesign4  = '';
          var sesacWrapperwidth = sesJqueryObject('.sesact_post_container_wrapper').width() - 20;
          sessctWidth = Math.round(sesacWrapperwidth / 100);

          if(counterLoopComposerItemDe4 == sessctWidth) {
            var html = sesJqueryObject('<span id="sesact_postde4_more" class="sesact_post_option option_more"><a href="javascript:void(0);" title="More" class="sesadv_tooltip"><span>'+en4.core.language.translate("More")+'</span></a></span>');
            sesJqueryObject('#sesact_post_options_design4').append(html);
          }

          if(counterLoopComposerItemDe4 == 1) {
            var htmltext = sesJqueryObject('<span id="sesact_postde4_addtext" class="sesact_post_option option_post"><a href="javascript:void(0);" title="Add Post" class="sesadv_tooltip"><span>'+en4.core.language.translate("Add Post")+'</span></a></span>');
            sesJqueryObject('#sesact_post_options_design4').append(htmltext);
          }

          if(counterLoopComposerItemDe4 >= sessctWidth)
            displayDesign4 = 'none';
          counterLoopComposerItemDe4++;

          var isShowClass = "";
          if(displayDesign4 == "block"){
            isShowClass = "design4show ";
          }

          this.elements.activator = new Element('span', {
            'html' :  '',
            'style': 'display:'+displayDesign4,
            'class': isShowClass+'sesact_post_option option_'+this.getName(),
          });

          //Album Work
          if(this.getName() == 'album') {
            this.elements.aActivator = new Element('a', {
              'id' : 'compose-' + this.getName() + '-activator',
              'class' : 'sesadv_tooltip sesalbum_popup_sesadv',
              'href' : 'javascript:;',
              'data-url' : 'sesalbum/index/create/params/anfwallalbum/ispopup/'+isOpenPopup,
              'title' : this._lang(this.options.title),
            }).inject(this.elements.activator);
          } else {
            this.elements.aActivator = new Element('a', {
            'id' : 'compose-' + this.getName() + '-activator',
            'class' : 'sesadv_tooltip',
            'href' : 'javascript:;',
            'title' : this._lang(this.options.title),
            'events' : {
              'click' : this.activate.bind(this)
            }
            }).inject(this.elements.activator);
          }

          this.elements.sactivator = new Element('span', {
            'html' :  this._lang(this.options.title),
          }).inject(this.elements.aActivator);

          this.elements.activator.inject($('sesact_post_options_design4'));

          sesJqueryObject('#activity_body').hide();
          sesJqueryObject('#sesact_post_box_img').hide();
          sesJqueryObject('#feedbg_main_continer').hide();

        }

      } else if(sesadvancedactivityDesign == 3) {

       if(counterLoopComposerItem == 4){
          moreTab = true;
          spanInsertBefore = 'sesact_post_media_options_before_more';
          var html = sesJqueryObject('<span class="sesact_post_head_option tool_i_more"><a href="javascript:void(0);" class="sesadv_tooltip"><i class="fa fa-ellipsis-h"></i><span>'+en4.core.language.translate("More")+'</span></a><div class="sesact_post_head_option_more_dropbox_wrapper"><div class="sesact_post_head_option_more_dropbox"><div id="sesact_post_media_options_before_more"></div></div></div></span>').insertBefore(sesJqueryObject('#sesact_post_media_options_before'));
        }
        if(counterLoopComposerItem > 3)
          spanInsertBefore = 'sesact_post_media_options_before_more';
        counterLoopComposerItem++;
        this.elements.activator = new Element('span', {
          'html' :  '',
          'class': 'sesact_post_head_option tool_i_'+this.getName(),
        });

        //Album Work
        if(this.getName() == 'album') {
          this.elements.aActivator = new Element('a', {
            'id' : 'compose-' + this.getName() + '-activator',
            'class' : 'sesadv_tooltip sesalbum_popup_sesadv',
            'href' : 'javascript:;',
            'data-url' : 'sesalbum/index/create/params/anfwallalbum/ispopup/'+isOpenPopup,
            'title' : this._lang(this.options.title),
          }).inject(this.elements.activator);
        } else {
          this.elements.aActivator = new Element('a', {
              'id' : 'compose-' + this.getName() + '-activator',
              'class' : 'sesadv_tooltip',
              'href' : 'javascript:;',
              'title' : this._lang(this.options.title),
              'events' : {
                'click' : this.activate.bind(this)
              }
          }).inject(this.elements.activator);
        }
        this.elements.sactivator = new Element('span', {
          'html' :  this._lang(this.options.title),
        }).inject(this.elements.aActivator);
        this.elements.activator.inject($(spanInsertBefore),'before');
      }
    }
     sesJqueryObjectTooltip('.sesadv_tooltip').powerTip({
      smartPlacement: true
     });
  },

  makeMenu : function() {
    if( !this.elements.menu ) {
      var tray = this.getComposer().getTray();

      this.elements.menu = new Element('div', {
        'id' : 'compose-' + this.getName() + '-menu',
        'class' : 'compose-menu'
      }).inject(tray);

      this.elements.menuTitle = new Element('span', {
				'class' : 'compose-menu-head',
        'html' : this._lang(this.options.title) + ''
      }).inject(this.elements.menu);

      this.elements.menuClose = new Element('a', {
				'class' : 'compose-menu-close fas fa-times',
        'href' : 'javascript:void(0);',
        'title' : this._lang('cancel'),
        'events' : {
          'click' : function(e) {
            e.stop();
            this.getComposer().deactivate();
          }.bind(this)
        }
      }).inject(this.elements.menuTitle);

      this.elements.menuTitle.appendText('');

    }
  },

  makeBody : function() {
    if( !this.elements.body ) {
      var tray = this.getComposer().getTray();
      this.elements.body = new Element('div', {
        'id' : 'compose-' + this.getName() + '-body',
        'class' : 'compose-body'
      }).inject(tray);
    }
  },

  makeLoading : function(action) {
    if( !this.elements.loading ) {
      if( action == 'empty' ) {
        this.elements.body.empty();
      } else if( action == 'hide' ) {
        this.elements.body.getChildren().each(function(element){ element.setStyle('display', 'none')});
      } else if( action == 'invisible' ) {
        this.elements.body.getChildren().each(function(element){ element.setStyle('height', '0px').setStyle('visibility', 'hidden')});
      }

      this.elements.loading = new Element('div', {
        'id' : 'compose-' + this.getName() + '-loading',
        'class' : 'compose-loading'
      }).inject(this.elements.body);
      var image = this.elements.loadingImage || (new Element('img', {
        'id' : 'compose-' + this.getName() + '-loading-image',
        'class' : 'compose-loading-image'
      }));
      image.inject(this.elements.loading);
      new Element('span', {
        'html' : this._lang('Loading...')
      }).inject(this.elements.loading);
    }
  },

  makeError : function(message, action) {
    if( !$type(action) ) action = 'empty';
    message = message || 'An error has occurred';
    message = this._lang(message);
    this.elements.error = new Element('div', {
      'id' : 'compose-' + this.getName() + '-error',
      'class' : 'compose-error',
      'html' : message
    }).inject(this.elements.body);
  },
  makeFormInputs : function(data) {
    this.ready();
    this.getComposer().getInputArea().empty();
    var name = this.getName();
    if(name == 'link')
      name  = 'sesadvancedactivitylink';
    data.type = name;
    $H(data).each(function(value, key) {
      this.setFormInputValue(key, value);
    }.bind(this));
  },
  setFormInputValue : function(key, value) {
    var elName = 'attachmentForm' + key.capitalize();
    if( !this.elements.has(elName) ) {
      this.elements.set(elName, new Element('input', {
        'type' : 'hidden',
        'name' : 'attachment[' + key + ']',
        'value' : value || ''
      }).inject(sesJqueryObject('.fileupload-cnt').get(0)));
    }
    this.elements.get(elName).value = value;
  },
  _lang : function() {
    try {
      if( arguments.length < 1 ) {
        return '';
      }
      var string = arguments[0];
      if( $type(this.options.lang) && $type(this.options.lang[string]) ) {
        string = this.options.lang[string];
      }
      if( arguments.length <= 1 ) {
        return string;
      }
      var args = new Array();
      for( var i = 1, l = arguments.length; i < l; i++ ) {
        args.push(arguments[i]);
      }
      return string.vsprintf(args);
    } catch( e ) {
      alert(e);
    }
  }
});
})(); // END NAMESPACE
sesJqueryObject(document).on('click',function(e){

//   if(enableStatusBoxHighlight == 0){
//     //return;
//   }
  var container = sesJqueryObject('.sesact_post_container');
  var smoothbox = sesJqueryObject('.sessmoothbox_main');
  var smoothboxIcon = sesJqueryObject('.sessmoothbox_overlay');
  var smoothboxSE = sesJqueryObject('#TB_window');
  var smoothboxSEOverlay = sesJqueryObject('#TB_overlay');
   var notclose = sesJqueryObject('.notclose');
  if(sesJqueryObject(e.target).hasClass('notclose') || sesJqueryObject(e.target).parent().hasClass('tag') || smoothbox.has(e.target).length || smoothbox.is(e.target) || smoothboxIcon.has(e.target).length || smoothboxIcon.is(e.target) || notclose.has(e.target).length || notclose.is(e.target) || sesJqueryObject(e.target).hasClass('sessmoothbox_close_btn') || sesJqueryObject(e.target).hasClass('sessmoothbox_main') || smoothboxSE.has(e.target).length || smoothboxSE.is(e.target) || smoothboxSEOverlay.has(e.target).length || smoothboxSEOverlay.is(e.target) || sesJqueryObject(e.target).attr('id') == 'TB_overlay' ||  sesJqueryObject(e.target).hasClass('close') || sesJqueryObject('.pac-container').has(e.target).length || sesJqueryObject('.pac-container').is(e.target) || sesJqueryObject(e.target).attr('id') == 'TB_window' || sesJqueryObject(e.target).prop("tagName") == 'BODY'){
    return;
  }

  if(sesJqueryObject(e.target).attr('id') == 'discard_post' || sesJqueryObject(e.target).attr('id') == 'goto_post'){
    return;
  }

  //Feed Background Image Work
  if(sesJqueryObject(e.target).hasClass('fa fa-angle-right') || sesJqueryObject(e.target).hasClass('fa fa-angle-left')){
    return;
  }

  if ((!container.is(e.target)
      && container.has(e.target).length === 0) || sesJqueryObject(e.target).hasClass('sesact_post_box_close_a'))
  {
    if(sesJqueryObject('._sesadv_composer_active').length){
      checkComposerAdv();
    }
  } else {
    sesJqueryObject('.sesact_post_container_wrapper').addClass('_sesadv_composer_active');
    sesJqueryObject(".sesact_post_box_close").show();
    sesJqueryObject('.sesact_post_media_options').addClass('sesact_post_media_options_active');
    sesJqueryObject(".sesact_post_media_options span:gt(3)").show();
    sesJqueryObject(".sesact_post_media_options").children().eq(2).hide();

    // Feed bg work
    if($('feedbg_main_continer'))
      sesJqueryObject('#feedbg_main_continer').css('display','block');

    if($('sesadvancedactivity_feeling_emojis'))
      sesJqueryObject('#sesadvancedactivity_feeling_emojis').css('display','block');

    //if(sesJqueryObject('#sesact_post_options_design4').length > 0) {
      sesJqueryObject('#sesact_post_options_design4').hide();
      sesJqueryObject('#sesact_postde4_more').hide();
      sesJqueryObject('.sesact_post_media_options').show();
      sesJqueryObject('#compose-menu').show();
      if(sesadvancedactivityDesign == 4) {


        sesJqueryObject('#activity_body').show();
        sesJqueryObject('#sesact_post_box_img').show();
        sesJqueryObject('#feedbg_main_continer').show();

        sesJqueryObject('.sesact_post_media_options').addClass('_isiconviewenable');

        if(e.target.id == 'compose-quote-activator' || e.target.id == 'quote-description' || e.target.id == 'quote-source' || e.target.id == 'compose-wishe-activator' || e.target.id == 'wishe-description' || e.target.id == 'wishe-source' || e.target.id == 'compose-prayer-activator' || e.target.id == 'prayer-description' || e.target.id == 'prayer-source' || e.target.id == 'compose-thought-activator' || e.target.id == 'thought-description' || e.target.id == 'thought-source' || e.target.id == 'tags') {
          sesJqueryObject('#sesact_post_box_status').hide();
        } else if(e.target.id == 'compose-text-activator') {
          sesJqueryObject('#sesact_post_box_status').hide();
        } else {
          sesJqueryObject('#sesact_post_box_status').show();
        }
      }
    //}
  }
});

function hideStatusBoxSecond() {

  if(sesadvancedactivityDesign == 4) {
    getConfirmation();
  }

  //return;
  sesJqueryObject('.sesact_post_container_wrapper').removeClass('_sesadv_composer_active');
  sesJqueryObject('.sesact_post_media_options').removeClass('sesact_post_media_options_active');
  sesJqueryObject(".sesact_post_media_options span:gt(3)").hide();
  sesJqueryObject(".sesact_post_media_options").children().eq(2).show();
  sesJqueryObject(".sesact_post_box_close").hide();
  sesJqueryObject('.sesadvancedactivity_shedulepost_overlay').hide();
  //resetComposerBoxStatus();

  //Feed Background Image Work
  if(document.getElementById('feedbgid')) {
    if(document.getElementById('feedbg_main_continer'))
    document.getElementById('feedbg_main_continer').style.display = 'none';
    sesJqueryObject('.sesact_post_box').css('background-image', 'none');
    sesJqueryObject('#activity-form').removeClass('feed_background_image');
  }
  if($('sesadvancedactivity_feeling_emojis'))
    sesJqueryObject('#sesadvancedactivity_feeling_emojis').css('display','none');

  // resize the status box
  sesJqueryObject('#activity_body').css('height','auto');

  sesJqueryObject('.sesact_post_box').removeClass('_blank');

  //Design 4
  if(sesadvancedactivityDesign == 4) {

    sesJqueryObject('#sesact_post_options_design4').show();
    sesJqueryObject('#sesact_postde4_more').show();
    sesJqueryObject('.sesact_post_media_options').hide();
    sesJqueryObject('#compose-menu').hide();
    sesJqueryObject('#sesact_post_box_status').show();

    sesJqueryObject('#activity_body').hide();
    sesJqueryObject('#sesact_post_box_img').hide();
    sesJqueryObject('#feedbg_main_continer').hide();
    document.getElementById('feedbg_main_continer').style.display = 'none';

    //Design 4 work for remove activated plugin
    var activatedPlugin = composeInstance.getActivePlugin();
    if(activatedPlugin)
      var pluginName = activatedPlugin.getName();
    else
      var pluginName = '';
    if((pluginName &&  pluginName != 'sesevent') || composeInstance.getContent() || sesJqueryObject('#toValues').val() || sesJqueryObject('#tag_location').val()) {
      composeInstance.plugins.each(function(plugin) {
        plugin.deactivate();

        sesJqueryObject('#compose-menu').hide();
        sesJqueryObject('#compose-'+plugin.getName()+'-activator').parent().removeClass('active');
      });
      return false;
    } else {
      return undefined;
    }
  }
}


function getConfirmation() {
  if(!sesJqueryObject('#activity_body').val())
    return;
//   if(!sesJqueryObject('#activity_body').length)
//     return undefined;
  var retVal = confirm("Are you sure to discard this post ?");
  if( retVal == true ) {
    resetComposerBoxStatus();
    composeInstance.plugins.each(function(plugin) {
      plugin.deactivate();
      sesJqueryObject('#compose-menu').hide();
      sesJqueryObject('#compose-'+plugin.getName()+'-activator').parent().removeClass('active');
    });
  }
}



sesJqueryObject(document).on('paste','#activity_body',function(){
   setTimeout(function () {
      linkDetection();
    }, 20);
});

sesJqueryObject(document).on('keyup','#activity_body',function(e) {
    if(e.keyCode != '32')
      return;
    setTimeout(function () {
      linkDetection();
    }, 20);
});
function updateEditVal(that,data){
    EditFieldValue = data;
    sesJqueryObject(that).mentionsInput("update");
}
var mentiondataarray = [];
sesJqueryObject(document).on('keyup','#activity_body',function(){
    var data = sesJqueryObject(this).val();
     EditFieldValue = data;
     //sesJqueryObject(this).mentionsInput("update");
});
function getDataMentionEdit (that,data){
  if (sesJqueryObject(that).attr('data-mentions-input') === 'true') {
       updateEditVal(that, data);
  }
}
var isOnEditField = isonCommentBox = false;
sesJqueryObject(document).on('focus','#activity_body',function(){
   isonCommentBox = false;
   if(!sesJqueryObject(this).attr('id'))
    sesJqueryObject(this).attr('id',new Date().getTime());
   var data = sesJqueryObject(this).val();
  if(!sesJqueryObject(this).val() || isOnEditField){
    if(!sesJqueryObject(this).val() )
      EditFieldValue = '';
    sesJqueryObject(this).mentionsInput({
        onDataRequest:function (mode, query, callback) {
         sesJqueryObject.getJSON('sesadvancedactivity/ajax/friends/query/'+query, function(responseData) {
          responseData = _.filter(responseData, function(item) { return item.name.toLowerCase().indexOf(query.toLowerCase()) > -1 });
          callback.call(this, responseData);
        });
      },
      //defaultValue: EditFieldValue,
      onCaret: true
    });
  }

  if(data){
     getDataMentionEdit(this,data);
  }

  if(!sesJqueryObject(this).parent().hasClass('typehead')){
    sesJqueryObject(this).hashtags();
    sesJqueryObject(this).focus();
  }
  autosize(sesJqueryObject(this));
});
function getUrlVars()
{
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++)
    {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}
sesJqueryObject(document).on('keydown','#activity_body',function(){
   if(sesJqueryObject(this).val() != '')
    sesJqueryObject('.sesact_post_box').removeClass('_blank');

});
sesJqueryObject(window).bind('beforeunload',function(){
 if(!sesJqueryObject('#activity_body').length)
    return undefined;
 var url      = window.location.href;
 if(url.indexOf('hashtag?hashtag=') >= 0){
  //if('#'+getUrlVars()["hashtag"] == sesJqueryObject('#activity_body').val()){
  if('#'+getUrlVars()["hashtag"] == composeInstance.getContent()){
    return undefined;
  }
 }
 var activatedPlugin = composeInstance.getActivePlugin();
 if(activatedPlugin)
  var pluginName = activatedPlugin.getName();
 else
  var pluginName = '';
  //if((pluginName &&  pluginName != 'sesevent') || sesJqueryObject('#activity_body').val() || sesJqueryObject('#toValues').val() || sesJqueryObject('#tag_location').val()){
  if((pluginName &&  pluginName != 'sesevent') || composeInstance.getContent() || sesJqueryObject('#toValues').val() || sesJqueryObject('#tag_location').val()){
    return false;
  }else{
    return undefined;
  }
});
function checkComposerAdv(){
  var activatedPlugin = composeInstance.getActivePlugin();
  if(activatedPlugin)
   var pluginName = activatedPlugin.getName();
  else
    var pluginName = '';

  hideStatusBoxSecond();
  return;
  if((pluginName &&  pluginName != 'sesevent') || sesJqueryObject('#activity_body').val() || sesJqueryObject('#toValues').val() || sesJqueryObject('#tag_location').val()){
  //if((pluginName &&  pluginName != 'sesevent') || composeInstance.getContent() || sesJqueryObject('#toValues').val() || sesJqueryObject('#tag_location').val()){
    sesJqueryObject('.sesact_confirmation_popup').show();
    sesJqueryObject('.sesact_confirmation_popup_overlay').show();
  }else{
      hideStatusBoxSecond();
  }
}
function linkDetection(){
  var html = sesJqueryObject('#activity_body').val();
  //var html = composeInstance.getContent();
    if(!html || !sesJqueryObject('#compose-link-activator').length || sesJqueryObject('#compose-tray').html())
      return false;
    var mystrings = [];
    var valid = false;
    var url = '';
    valid = this.checkUrl(html);
    if(!valid)
      return;
   var pluginlink = composeInstance.getPlugin('link');
   pluginlink.activate();
   //check for youtube video url
   var matches = valid.match(/watch\?v=([a-zA-Z0-9\-_]+)/);
   if (matches)
   {
     if(valid.indexOf('?') < 0)
      valid = valid+'?youtubevideo=1';
     else
      valid = valid+'&youtubevideo=1';
   }else if(parseVimeo(valid)){
     if(valid.indexOf('?') < 0)
      valid = valid+'?vimeovideo=1';
     else
      valid = valid+'&vimeovideo=1';
   }else if(valid.indexOf('https://soundcloud.com') >= 0){
      if(valid.indexOf('?') < 0)
        valid = valid+'?soundcloud=1';
      else
        valid = valid+'&soundcloud=1';
   }
   pluginlink.elements.formInput.value = valid;
   pluginlink.doAttach();
   pluginlink.active = true;
   sesJqueryObject('#compose-link-form-submit').trigger('click');
}
function parseVimeo(str) {
    // embed & link: http://vimeo.com/86164897
    var re = /\/\/(?:www\.)?vimeo.com\/([0-9a-z\-_]+)/i;
    var matches = re.exec(str);
    return matches && matches[1];
}
function checkUrl(str){
   var geturl = /(((https?:\/\/)|(www\.))[^\s]+)/g;
   if(str.match(geturl)){
    var length =   str.match(geturl).length
    var urls =   str.match(geturl)

    if(length)
      return urls[0];
   }
    return '';
  }
