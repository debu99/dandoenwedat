/* $Id:composer_twitter.js  2017-01-12 00:00:00 SocialEngineSolutions $*/


(function() { // START NAMESPACE
var $ = 'id' in document ? document.id : window.$;



Composer.Plugin.Twitter = new Class({

  Extends : Composer.Plugin.Interface,

  name : 'twitter',

  options : {
    title : 'Publish this on Twitter',
    lang : {
        'Publish this on Twitter': 'Publish this on Twitter'
    },
    requestOptions : false,
  },

  initialize : function(options) {
    this.elements = new Hash(this.elements);
    this.params = new Hash(this.params);
    this.parent(options);
  },

  attach : function() {
    var openWindow = '';
     if(!this.options.status)
       openWindow = ' openWindowTwitter';
    this.elements.spanToggle = new Element('span', {
      'class' : 'composer_twitter_toggle sesadv_tooltip'+openWindow,
      'href'  : 'javascript:void(0);',
      'title' : this.options.lang['Publish this on Twitter'],
      'events' : {
        'click' : this.toggle.bind(this)
      }
    });

    this.elements.formCheckbox = new Element('input', {
      'id'    : 'compose-twitter-form-input',
      'class' : 'compose-form-input',
      'type'  : 'checkbox',
      'name'  : 'post_to_twitter',
      'style' : 'display:none;'
    });
    
    /*this.elements.spanTooltip = new Element('span', {
      'for' : 'compose-twitter-form-input',
      'class' : 'composer_twitter_tooltip',
      'html' : this.options.lang['Publish this on Twitter']
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
    if(sesJqueryObject('.openWindowTwitter').length)
      return;
    $('compose-twitter-form-input').set('checked', !$('compose-twitter-form-input').get('checked'));
    if(sesJqueryObject('.composer_twitter_toggle').hasClass('composer_twitter_toggle_active')){
      sesJqueryObject('.composer_twitter_toggle').removeClass('composer_twitter_toggle_active');  
    }else{
      sesJqueryObject('.composer_twitter_toggle').addClass('composer_twitter_toggle_active');  
    }
    composeInstance.plugins['twitter'].active = true;
    setTimeout(function(){
      composeInstance.plugins['twitter'].active = false;
    }, 300);
  }
});



})(); // END NAMESPACE
