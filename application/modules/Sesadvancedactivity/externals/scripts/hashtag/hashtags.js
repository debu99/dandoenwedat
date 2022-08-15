/* $Id:hashtags.js  2017-01-12 00:00:00 SocialEngineSolutions $*/
(function($) { 	
    $.fn.hashtags = function() {     
        if(sesJqueryObject('#sessmoothbox_main').length){       
          var className = 'highlighter_edit';       
          var classMain = 'jqueryHashtags_edit';    
        }else{       
          var classMain = '';       
          var className = '';     
       } 		
       $(this).wrap('<div class="jqueryHashtags '+classMain+'"><div class="highlighter '+className+'"></div></div>').unwrap().before('<div class="highlighter '+className+'"></div>').wrap('<div class="typehead"></div></div>'); 		
       $(this).addClass("theSelector"); 		
       autosize($(this)); 		
       $(this).parent().prev().on('click', function() { 			
        $(this).parent().find(".theSelector").focus(); 		
      });   
    }; 
})(sesJqueryObject);