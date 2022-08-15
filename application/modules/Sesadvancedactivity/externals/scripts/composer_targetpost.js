/* $Id:composer_targetpost.js  2017-01-12 00:00:00 SocialEngineSolutions $*/

(function() { // START NAMESPACE
var $ = 'id' in document ? document.id : window.$;
Composer.Plugin.Sesadvancedactivitytargetpost = new Class({
  Extends : Composer.Plugin.Interface,
  name : 'targetpost',
  options : {
    title : 'Choose Preferred Audience',
    lang : {
        'Choose Preferred Audience': 'Choose Preferred Audience'
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
     
     this.elements.spanToggle = new Element('span', {
      'class' : 'composer_targetpost_toggle sesadv_tooltip',
      'href'  : 'javascript:void(0);',
      'title' : this.options.lang['Choose Preferred Audience'],
      'events' : {
        'click' : this.toggle.bind(this)
      }
    });
    this.elements.formCheckbox = new Element('input', {
      'id'    : 'compose-targetpost-form-input',
      'class' : 'compose-form-input',
      'type'  : 'checkbox',
      'name'  : 'post_to_targetpost',
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
    //open target post popup
    openTargetPostPopup();
    composeInstance.plugins['targetpost'].active=true;
    setTimeout(function(){
      composeInstance.plugins['targetpost'].active=false;
    }, 300);
  }
});
})(); // END NAMESPACE