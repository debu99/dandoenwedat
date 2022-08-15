sesJqueryObject(document).on('click','.sesconsent_opt',function(e){
  sesJqueryObject.post('sesgdpr/index/inset-audit',{type:'opt',name:sesJqueryObject(this).closest('tr').children().eq(0).html()},function(response){
  })  
})
sesJqueryObject(document).on('submit','.sesgdpr_contactdpo',function(e){
  e.preventDefault();
  var type = sesJqueryObject('._active').find('a').attr('data-rel');
  sesJqueryObject(this).find('#type').val(type);
  var firstName = sesJqueryObject(this).find('#first_name').val();
  var lastName = sesJqueryObject(this).find('#last_name').val();
  var emailName = sesJqueryObject(this).find('#email').val();
  var message = sesJqueryObject(this).find('#message').val();
  var error = false;
  if(!firstName){
    sesJqueryObject(this).find('#first_name').css('border','1px solid red');
    error = 1;
  }else{
    sesJqueryObject(this).find('#first_name').css('border','');  
  }
  if(type == "dpo"){
    if(!message){
      sesJqueryObject(this).find('#message').css('border','1px solid red');
      error = 1;
    }else{
      sesJqueryObject(this).find('#message').css('border','');  
    }
  }
  if(!lastName){
    sesJqueryObject(this).find('#last_name').css('border','1px solid red');
    error = 1;
  }else{
    sesJqueryObject(this).find('#last_name').css('border','');  
  }
  if(!emailName){
    sesJqueryObject(this).find('#email').css('border','1px solid red');
    error = 1;
  }else if(!validEmailSesgdpr(emailName)){
    sesJqueryObject(this).find('#email').css('border','1px solid red');
    error = 1;
  }else{
    sesJqueryObject(this).find('#email').css('border','');  
  }
  
  if(error == true)
    return;
   var obj = sesJqueryObject(this);
   sesJqueryObject.post('sesgdpr/index/inset',{type:type,firstName:firstName,lastName:lastName,emailName:emailName,message:message},function(response){
    // var valid = sesJqueryObject.parseJSON(response);
      if(response.status == 1){
          sesJqueryObject(obj).parent().html(htmlSuccessMessage);
      }else{
        alert(en4.core.language.translate('Something went wrong, please try again later'));  
      }
   });  
});
function validEmailSesgdpr(email){
  var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
  return regex.test(email);
}
function callBackSesconsent(type){
  sesJqueryObject('#sesgdpa_consents').find('li').find('a[data-rel='+type+']').trigger('click'); 
}
sesJqueryObject(document).on('click','#sesgdpa_consents > li > a',function(){
  if(sesJqueryObject(this).parent().hasClass('_active'))
    return;
  var index = sesJqueryObject(this).parent().index();  
  sesJqueryObject('._active').removeClass('_active');
  sesJqueryObject(this).parent().addClass('_active');
  sesJqueryObject('#sesgdpa_consent_div').find('._form').hide();
  sesJqueryObject('#sesgdpa_consent_div').find('._form').eq(index).show();
});