/* $Id:composer_facebook.js  2017-01-12 00:00:00 SocialEngineSolutions $*/

(function() { // START NAMESPACE
var $ = 'id' in document ? document.id : window.$;
Composer.Plugin.SesadvancedactivityEvacebook = new Class({
  Extends : Composer.Plugin.Interface,
  name : 'facebook',
  options : {
    title : 'Publish this on Facebook',
    lang : {
        'Publish this on Facebook': 'Publish this on Facebook'
    },
    requestOptions : false
  },
  initialize : function(options) {
    this.elements = new Hash(this.elements);
    this.params = new Hash(this.params);
    this.parent(options);
  },
  attach : function() {
     var openWindow = '';
     if(!this.options.status)
       openWindow = ' openWindowFacebook';;
     this.elements.spanToggle = new Element('span', {
      'class' : 'composer_facebook_toggle sesadv_tooltip'+openWindow,
      'href'  : 'javascript:void(0);',
      'title' : this.options.lang['Publish this on Facebook'],
      'events' : {
        'click' : this.toggle.bind(this)
      }
    });
    this.elements.formCheckbox = new Element('input', {
      'id'    : 'compose-facebook-form-input',
      'class' : 'compose-form-input',
      'type'  : 'checkbox',
      'name'  : 'post_to_facebook',
      'style' : 'display:none;'
    });
    /*this.elements.spanTooltip = new Element('span', {
      'for' : 'compose-facebook-form-input',
      'class' : 'sesadv_tooltip',
      'title' : this.options.lang['Publish this on Facebook']
    });*/
    this.elements.formCheckbox.inject(this.elements.spanToggle);
    //this.elements.spanTooltip.inject(this.elements.spanToggle);
    this.elements.spanToggle.inject($('compose-menu'));
    //this.parent();
    //this.makeActivator();
    return this;
  },
  detach : function() {
    this.parent();
    return this;
  },
  toggle : function(event) {
    if(sesJqueryObject('.openWindowFacebook').length)
      return;
    $('compose-facebook-form-input').set('checked', !$('compose-facebook-form-input').get('checked'));
    if(sesJqueryObject('.composer_facebook_toggle').hasClass('composer_facebook_toggle_active')){
      sesJqueryObject('.composer_facebook_toggle').removeClass('composer_facebook_toggle_active');  
    }else{
      sesJqueryObject('.composer_facebook_toggle').addClass('composer_facebook_toggle_active');  
    }
    composeInstance.plugins['facebook'].active=true;
    setTimeout(function(){
      composeInstance.plugins['facebook'].active=false;
    }, 300);
  }
});
})(); // END NAMESPACE