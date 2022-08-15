jqueryObjectOfSes(document).ready(function() {
	var shareTitle = jqueryObjectOfSes('meta[property="og:title"]').attr('content');
	if(typeof shareTitle == 'undefined')
		shareTitle = jqueryObjectOfSes('meta[name="title"]').attr('content');
	var shareDescription = jqueryObjectOfSes('meta[property="og:description"]').attr('content');
	if(typeof  shareDescription == 'undefined')
		shareDescription = jqueryObjectOfSes('meta[name="description"]').attr('content');
	var shareImage = jqueryObjectOfSes('meta[property="og:image"]').attr('content');
	if(typeof shareImage == 'undefined')
		shareImage = '';
	var shareVideo = jqueryObjectOfSes('meta[property="og:video"]').attr('content');
	if(typeof shareVideo == 'undefined')
		shareVideo = '';
	var pageUrl = window.location.href;
	var social = [
	 ["Facebook", 	"http://www.facebook.com/sharer/sharer.php?u="+encodeURI(pageUrl),"#3B5998", en4.core.baseUrl+"application/modules/Sesbasic/externals/images/facebook.png"],
	 ["Google+", 	" https://plus.google.com/share?url="+encodeURI(pageUrl),"#dd4b39", en4.core.baseUrl+"application/modules/Sesbasic/externals/images/google_plus.png"],
	 ["Linkedin", 	"http://www.linkedin.com/shareArticle?mini=true&summary="+encodeURI(shareDescription)+"&title="+encodeURI(shareTitle)+"&url="+encodeURI(pageUrl),"#0e76a8", en4.core.baseUrl+"application/modules/Sesbasic/externals/images/linkedin.png"],
	 ["Twitter", 	"http://twitter.com/share?text="+encodeURI(shareDescription)+"&url="+encodeURI(pageUrl),"#55acee", en4.core.baseUrl+"application/modules/Sesbasic/externals/images/twitter.png"],
	 ];
	////////////////////////////////////////////////
	//// DO NOT EDIT ANYTHING BELOW THIS LINE! /////
	////////////////////////////////////////////////
	jqueryObjectOfSes("#sesbasic_socialside").append('<ul class="sesbasic_share_content_ul"></ul>');
	/// generating bars
	for(var i=0;i<social.length;i++){
	jqueryObjectOfSes(".sesbasic_share_content_ul").append("<li>" + '<ul class="sesbasic_share_content" style="background-color:' + social[i][2] + '">' +
						'<li>' + social[i][0] + '<img src="' + social[i][3] + '"/></li></ul></li>');
	}
	 var leftPosition, topPosition;
	 var width = 700;
	 var height = 500;
   //Allow for borders.
   leftPosition = (window.screen.width / 2) - ((width / 2) + 10);
   //Allow for title and status bars.
   var topPosition = (window.screen.height / 2) - ((height / 2) + 50);
		var windowFeatures = "status=no,height=" + height + ",width=" + width + ",resizable=yes,left=" + leftPosition + ",top=" + topPosition + ",screenX=" + leftPosition + ",screenY=" + topPosition + ",toolbar=no,menubar=no,scrollbars=no,location=no,directories=no";
	/// bar click event
	jqueryObjectOfSes(".sesbasic_share_content").click(function(){
		var link = jqueryObjectOfSes(this).text();
		for(var i=0;i<social.length;i++){
			if(social[i][0] == link){
				window.open(social[i][1],"Share on "+social[i][0], windowFeatures);
			}
		}
	});
	/// mouse hover event
	jqueryObjectOfSes(".sesbasic_share_content").mouseenter(function(){
		jqueryObjectOfSes(this).stop(true);
		jqueryObjectOfSes(this).clearQueue();
			jqueryObjectOfSes(this).animate({
				left : "100px"
			}, 300);
	});
	/// mouse out event
	jqueryObjectOfSes(".sesbasic_share_content").mouseleave(function(){
		jqueryObjectOfSes(this).animate({
			left:"0px"
		},700,'easeOutBounce');
	});
});