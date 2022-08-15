/* $Id:composer_linkedin.js  2017-01-12 00:00:00 SocialEngineSolutions $*/

(function() { // START NAMESPACE
var $ = 'id' in document ? document.id : window.$;
Composer.Plugin.SesadvancedactivityLikedin = new Class({
  Extends : Composer.Plugin.Interface,
  name : 'facebook',
  options : {
    title : 'Publish this on Linkedin',
    lang : {
        'Publish this on Linkedin': 'Publish this on Linkedin'
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
       openWindow = ' openWindowLinkedin';;
     this.elements.spanToggle = new Element('span', {
      'class' : 'composer_linkedin_toggle sesadv_tooltip'+openWindow,
      'href'  : 'javascript:void(0);',
      'title' : this.options.lang['Publish this on Linkedin'],
      'events' : {
        'click' : this.toggle.bind(this)
      }
    });
    this.elements.formCheckbox = new Element('input', {
      'id'    : 'compose-linkedin-form-input',
      'class' : 'compose-form-input',
      'type'  : 'checkbox',
      'name'  : 'post_to_linkedin',
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
    if(sesJqueryObject('.openWindowLinkedin').length)
      return;
    $('compose-linkedin-form-input').set('checked', !$('compose-linkedin-form-input').get('checked'));
    if(sesJqueryObject('.composer_linkedin_toggle').hasClass('composer_linkedin_toggle_active')){
      sesJqueryObject('.composer_linkedin_toggle').removeClass('composer_linkedin_toggle_active');  
    }else{
      sesJqueryObject('.composer_linkedin_toggle').addClass('composer_linkedin_toggle_active');  
    }
  }
});
})(); // END NAMESPACE