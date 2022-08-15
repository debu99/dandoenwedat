/* $Id:composer_photo.js  2017-01-12 00:00:00 SocialEngineSolutions $*/


(function() { // START NAMESPACE
var $ = 'id' in document ? document.id : window.$;
Composer.Plugin.AdvancedactivityPhoto = new Class({

  Extends : Composer.Plugin.Interface,

  name : 'photo',

  options : {
    title : 'Add Photo',
    lang : {},
    requestOptions : false,
    fancyUploadEnabled : true,
    fancyUploadOptions : {}
  },

  initialize : function(options) {
    this.elements = new Hash(this.elements);
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
    return this;
  },

  activate : function() {
    if( this.active ) return;
    this.parent();

    this.makeMenu();
    this.makeBody();
    
    // Generate form
    var fullUrl = this.options.requestOptions.url;    
      sesJqueryObject(this.elements.body).html('<input type="file" accept="image/x-png,image/jpeg" onchange="readImageUrl(this)" multiple="multiple" id="file_multi" name="file_multi" style="display:none"><div class="advact_compose_photo_container sesbasic_custom_horizontal_scroll sesbasic_clearfix"><div id="advact_compose_photo_container_inner" class="sesbasic_clearfix"><div id="show_photo"></div><div id="dragandrophandler" class="advact_compose_photo_uploader" title="'+en4.core.language.translate("Choose a file to upload")+'"><i class="fa fa-plus"></i></div></div></div>');
    jqueryObjectOfSes(".sesbasic_custom_horizontal_scroll").mCustomScrollbar({
      axis:"x",
      theme:"light-3",
      advanced:{autoExpandHorizontalScroll:true}
    });
  },

  deactivate : function() {
    if( !this.active ) return;
    this.parent();
  },

  doRequest : function() {},

  doProcessResponse : function(responseJSON) {},

  doImageLoaded : function() {},

  makeFormInputs : function() {}

});



})(); // END NAMESPACE
