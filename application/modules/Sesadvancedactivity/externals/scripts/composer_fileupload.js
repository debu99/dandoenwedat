/* $Id:composer_fileupload.js  2017-01-12 00:00:00 SocialEngineSolutions $*/

(function() { // START NAMESPACE
var $ = 'id' in document ? document.id : window.$;



Composer.Plugin.Fileupload = new Class({

  Extends : Composer.Plugin.Interface,

  name : 'fileupload',

  options : {
    title : 'Add File',
    serverLimit : 0,
    lang : {},
    // Options for the link preview request
    requestOptions : {},
    debug : false
  },

  initialize : function(options) {
    this.params = new Hash(this.params);
    this.parent(options);
  },

  attach : function() {
    this.parent();
    this.makeActivator();
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
    sesJqueryObject(this.elements.body).html('<input id="fileupload-input-type" type="file" name="fileupload" value="" onchange="checkuploadfiletype(this,'+this.options.sesrverLimitDigits+')"><span class="sesbasic_text_light">(Max size '+this.options.serverLimit+')</span>');    
  },

  deactivate : function() {
    if( !this.active ) return;
    this.parent();
    
    this.request = false;
  },
});
})(); // END NAMESPACE
function checkuploadfiletype(input,value){
  var url = input.value;
  var ext = url.substring(url.lastIndexOf('.') + 1).toLowerCase();
  if (input.files && input.files[0] && (ext == "exe" || ext == '.mp3')) {
    sesJqueryObject('#fileupload-input-type').val('');
    return false;
  }
  if(input.files[0].size > value){
     en4.core.showError("<p>" + en4.core.language.translate("Upload smaller file.") + '</p><button onclick="Smoothbox.close()">'+en4.core.language.translate("Close")+'</button>');
     sesJqueryObject('#fileupload-input-type').val('');
    return false;
  }
  var field = '<input type="hidden" name="attachment[type]" value="fileupload">';
  if(!sesJqueryObject('.fileupload-cnt').length)
    sesJqueryObject('#activity-form').append('<div style="display:none" class="fileupload-cnt">'+field+'</div>');
  else
    sesJqueryObject('.fileupload-cnt').html(field);
  var plugin = composeInstance.getPlugin('fileupload');
  plugin.ready();
}