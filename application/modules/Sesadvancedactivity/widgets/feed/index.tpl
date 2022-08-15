<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
$staticBaseUrl = $this->layout()->staticBaseUrl;

if($this->feeddesign == 2) {
  $this->headScript()->appendFile($staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/imagesloaded.pkgd.js');
  $this->headLink()->appendStylesheet($staticBaseUrl . 'application/modules/Sesadvancedactivity/externals/styles/style_pinboard.css'); 
  $this->headScript()->appendFile($staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/wookmark.min.js');
  $this->headScript()->appendFile($staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/pinboardcomment.js');
  $randonNumber = 'pinFeed'; 
}
?>
<script type="application/javascript">
    function setFocus(){

      document.getElementById("activity_body").focus();
      
    }
    var sesAdvancedActivityGetFeeds = <?php echo $this->getUpdates ?>;
  var sesAdvancedActivityGetAction_id = <?php echo $this->action_id; ?>;
    if(!sesAdvancedActivityGetFeeds){
      en4.core.runonce.add(function() {
        var subject_guid = '<?php echo $this->subjectGuid ?>';
        sesJqueryObject('ul.sesadvancedactivity_filter_tabs li a:first').trigger("click");
      });
    }
    function activateFunctionalityOnFirstLoad() {
      var action_id = <?php echo $this->action_id; ?>;
      sesAdvancedActivityGetFeeds = true;

      if(!action_id) {
        sesJqueryObject(".sesact_feed_filters").show();
        if (sesJqueryObject('#activity-feed').children().length)
          sesJqueryObject('.sesadv_noresult_tip').hide();
        else
          sesJqueryObject('.sesadv_noresult_tip').show();
      }else{
        if (!sesJqueryObject('#activity-feed').children().length)
          sesJqueryObject(".no_content_activity_id").show();
      }
      sesJqueryObject(".sesadv_content_load_img").hide();
    }
</script>
<?php if($this->feeddesign != 2){ ?>
<script type="application/javascript">
  function feedUpdateFunction(){}
</script>
<?php } ?>
<?php if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesfeelingactivity')) { ?>
  <?php $getFeelings = Engine_Api::_()->getDbTable('feelings', 'sesfeelingactivity')->getFeelings(array('fetchAll' => 1, 'admin' => 0)); ?>
<?php } ?>
<?php if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesemoji')) { ?>
  <?php $getEmojis = Engine_Api::_()->getDbTable('emojis', 'sesemoji')->getEmojis(array('fetchAll' => 1)); ?>
<?php } ?>
<?php $this->headTranslate(array('More','Close','Permalink of this Post','Copy link of this feed:','Go to this feed','You won\'t see this post in Feed.',"Undo","Hide all from",'You won\'t see',"post in Feed.","Select","It is a long established fact that a reader will be distracted","If you find it offensive, please","file a report.", "Choose Feeling or activity...", "How are you feeling?", "ADD POST")); ?>

<?php
  //Web cam upload for profile photo
  if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.profilephotoupload', 1) && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesalbum')):
    $this->headScript()->appendFile($staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/webcam.js'); 
  endif; 
  if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesemoji')) {
    $this->headScript()->appendFile($staticBaseUrl . 'application/modules/Sesemoji/externals/scripts/emojiscontent.js'); 
  }
  $this->headScript()->appendFile($staticBaseUrl . 'application/modules/Sesadvancedactivity/externals/scripts/mo.min.js');
  $this->headScript()->appendFile($staticBaseUrl . 'application/modules/Sesadvancedactivity/externals/scripts/animation.js');
  
  if(defined('SESFEEDGIFENABLED')) {
    $this->headScript()->appendFile($staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/flexcroll.js');
  }
  if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesdiscussion')) {
    $this->headLink()->appendStylesheet($staticBaseUrl . 'application/modules/Sesdiscussion/externals/styles/styles.css');
  }
  if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesquote')) {
    $this->headLink()->appendStylesheet($staticBaseUrl . 'application/modules/Sesquote/externals/styles/styles.css');
  }
  
  if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('seswishe')) {
    $this->headLink()->appendStylesheet($staticBaseUrl . 'application/modules/Seswishe/externals/styles/styles.css');
  }
  
  if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesprayer')) {
    $this->headLink()->appendStylesheet($staticBaseUrl . 'application/modules/Sesprayer/externals/styles/styles.css');
  }
  
  if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesthought')) {
    $this->headLink()->appendStylesheet($staticBaseUrl . 'application/modules/Sesthought/externals/styles/styles.css');
  }

if(defined('SESTEXTENABLED')) {
  $this->headLink()->appendStylesheet($staticBaseUrl . 'application/modules/Sestext/externals/styles/summernote.css'); ?>
  <?php $this->headScript()->appendFile($staticBaseUrl . 'application/modules/Sestext/externals/scripts/jquery.js'); ?>
  <?php $this->headScript()->appendFile($staticBaseUrl . 'application/modules/Sestext/externals/scripts/summernote.js'); ?>
  <?php $this->headScript()->appendFile($staticBaseUrl . 'application/modules/Sestext/externals/scripts/bootstrap.js'); ?>

  <style>
    .note-btn-group button{display: block !important;}
  </style>
<?php } ?>

<?php 
$viewer = $this->viewer();

$settings = Engine_Api::_()->getApi('settings', 'core');
$showwelcometab = $settings->getSetting('sesadvancedactivity.showwelcometab', 1);
$makelandingtab = $settings->getSetting('sesadvancedactivity.makelandingtab', 2);
$tabvisibility = $settings->getSetting('sesadvancedactivity.tabvisibility', 0);
$diff_days = $friendsCount = 0;
$numberofdays = $settings->getSetting('sesadvancedactivity.numberofdays', 3);
$numberoffriends = $settings->getSetting('sesadvancedactivity.numberoffriends', 3); 
if($viewer->getIdentity()) {
  if($tabvisibility == 2) {
    $signup_date = explode(' ', $viewer->creation_date);
    $finalSignupDate = date_create($signup_date[0]);
    $todayDate = date_create(date('Y-m-d'));
    $diff = date_diff($finalSignupDate,$todayDate); 
    $diff_days = $diff->d;
  } elseif($tabvisibility == 1) {
    $friendsCount = $this->viewer()->membership()->getMemberCount($this->viewer());
  }
}
$welcomeflag = 'false';
if($showwelcometab) {
  if($tabvisibility == 2 && $numberofdays > $diff_days) {
    $welcomeflag = 'true';
  } elseif($tabvisibility == 1 && $numberoffriends > $friendsCount) {
    $welcomeflag = 'true';
  } elseif($tabvisibility == 0) {
    $welcomeflag = 'true';
  }
}
?>


<script type="application/javascript">
var privacySetAct = false;
var sespageContentSelected = "";
 <?php if( !$this->feedOnly && $this->action_id){ ?>
 sesJqueryObject(document).ready(function(e){
   sesJqueryObject('.tab_<?php echo $this->identity; ?>.tab_layout_sesadvancedactivity_feed').find('a').click();
 });
 <?php } ?>
 </script>
<?php if( !$this->feedOnly && $this->isMemberHomePage): ?>
<div class="sesact_tabs_wrapper sesbasic_clearfix sesbasic_bxs">
  <ul id="sesadv_tabs_cnt" class="sesact_tabs sesbasic_clearfix">
    <?php if($showwelcometab): ?>
      <?php if($welcomeflag == 'true'): ?>
        <li data-url="1" class="sesadv_welcome_tab <?php if($makelandingtab == 2): ?> active <?php endif; ?>">
          <a href="javascript:;">
          <?php if($this->welcomeicon == 'icon'){ ?>
            <i class="far fa-smile" aria-hidden="true"></i>
          <?php }else if($this->welcomeicon){ ?>
            <i class="_icon"><img src="<?php echo Engine_Api::_()->sesadvancedactivity()->getFileUrl($this->welcomeicon); ?>" ></i>
         <?php } ?>
            <span><?php echo $this->translate($this->welcometabtext); ?></span>
          </a>
        </li>
      <?php endif; ?>
    <?php endif; ?>
    <li data-url="2" class="sesadv_update_tab <?php if(empty($showwelcometab) || $makelandingtab == 0): ?> active <?php endif; ?>">
      <a href="javascript:;">
        <?php if($this->welcomeicon == 'icon'){ ?>
            <i class="fa fa-globe" aria-hidden="true"></i>
          <?php }else if($this->whatsnewicon){ ?>
            <i class="_icon"><img src="<?php echo Engine_Api::_()->sesadvancedactivity()->getFileUrl($this->whatsnewicon); ?>" ></i>
         <?php } ?>
      	<span><?php echo $this->translate($this->whatsnewtext); ?></span>
        <span id="count_new_feed"></span>
      </a>
    </li>
  </ul>
</div>

<div id="sesadv_tab_1" class="sesadv_tabs_content" style="display:none;">
  <div class="sesbasic_loading_container sesadv_loading_img" style="height:100px;"  data-href="sesadvancedactivity/ajax/welcome/"></div>
</div>
<script type="application/javascript">
sesJqueryObject(document).ready(function(){
      if(sesJqueryObject('#sesadv_tabs_cnt').children().length == 1){
        sesJqueryObject('#sesadv_tabs_cnt').parent().remove(); 
      }
    });
</script>

<div id="sesadv_tab_2" class="sesadv_tabs_content" <?php if(!empty($showwelcometab) && $makelandingtab != 0): ?> style="display:none;"<?php endif; ?>>
<?php endif; ?>
  <?php $this->headLink()->appendStylesheet($staticBaseUrl . 'application/modules/Sesadvancedactivity/externals/styles/styles.css'); ?>
	<?php $this->headLink()->appendStylesheet($staticBaseUrl . 'application/modules/Sesbasic/externals/styles/emoji.css'); ?>    
<?php $this->headLink()->appendStylesheet($staticBaseUrl . 'application/modules/Sesbasic/externals/styles/customscrollbar.css'); ?>

<?php $this->headScript()->appendFile($staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/tooltip.js'); ?>

<?php $this->headScript()->appendFile($staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/jquery.min.js'); ?>
<?php $this->headScript()->appendFile($staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/customscrollbar.concat.min.js'); ?>
<?php $this->headLink()->appendStylesheet($staticBaseUrl . 'application/modules/Sesbasic/externals/styles/mention/jquery.mentionsInput.css'); ?>    

 <?php $this->headScript()->appendFile($staticBaseUrl .'application/modules/Sesbasic/externals/scripts/mention/underscore-min.js'); ?>
  <?php $this->headScript()->appendFile($staticBaseUrl .'application/modules/Sesbasic/externals/scripts/mention/jquery.mentionsInput.js'); ?>
 
<?php if( (!empty($this->feedOnly) || !$this->endOfFeed ) &&
    (empty($this->getUpdate) && empty($this->checkUpdate)) ): 
    
    $adsEnable = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.adsenable', 0);
    ?>
  <script type="text/javascript">
  
  function defaultSettingsSesadv(){
      var activity_count = <?php echo sprintf('%d', $this->activityCount) ?>;
      var next_id = <?php echo sprintf('%d', $this->nextid) ?>;
      var subject_guid = '<?php echo $this->subjectGuid ?>';
      var endOfFeed = <?php echo ( $this->endOfFeed ? 'true' : 'false' ) ?>;
      var activityViewMore = window.activityViewMore = function(next_id, subject_guid) {
        //if( en4.core.request.isRequestActive() ) return;
        var hashTag = sesJqueryObject('#hashtagtextsesadv').val();
        var url = '<?php echo $this->url(array('module' => 'core', 'controller' => 'widget', 'action' => 'index', 'content_id' => $this->identity), 'default', true) ?>';         
         if(typeof sesItemSubjectGuid != "undefined")
            var itemSubject = sesItemSubjectGuid;
          else
            var itemSubject = "";
        $('feed_viewmore_activityact').style.display = 'none';
        $('feed_loading').style.display = '';
        
        var adsIds = sesJqueryObject('.sescmads_ads_listing_item');
        var adsIdString = "";
        if(adsIds.length > 0){
           sesJqueryObject('.sescmads_ads_listing_item').each(function(index){
             adsIdString = sesJqueryObject(this).attr('rel')+ "," + adsIdString ;
           });
        }
        
          var request = new Request.HTML({
          url : url+"?hashtag="+hashTag+'&isOnThisDayPage='+isOnThisDayPage+'&isMemberHomePage='+isMemberHomePage+'&subjectPage='+itemSubject,
          data : {
            format : 'html',
            'maxid' : next_id,
            'feedOnly' : true,
            'nolayout' : true,
            'getUpdates' : true,
            'subject' : subject_guid,
            'ads_ids': adsIdString,
            'contentCount':sesJqueryObject('#activity-feed').find("[id^='activity-item-']").length,
            'filterFeed':sesJqueryObject('.sesadvancedactivity_filter_tabs .active > a').attr('data-src'),
          },
          evalScripts : true,
          onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript) {
            Elements.from(responseHTML).inject($('activity-feed'));
            en4.core.runonce.trigger();
            Smoothbox.bind($('activity-feed'));
            feedUpdateFunction();
            <?php if($adsEnable){ ?>
            displayGoogleAds();
            <?php  } ?>
            sesadvtooltip();
          }
        });
       request.send();
      }
      
      if( next_id > 0 && !endOfFeed ) {
        sesJqueryObject('#feed_viewmore_activityact').show();
        sesJqueryObject('#feed_loading').hide();
        if(sesJqueryObject('#feed_viewmore_activityact_link').length){
          $('feed_viewmore_activityact_link').removeEvents('click').addEvent('click', function(event){
            event.stop();
            activityViewMore(next_id, subject_guid);
          });
        }
      } else {
        
        sesJqueryObject('#feed_viewmore_activityact').hide();
        sesJqueryObject('#feed_loading').hide();
      }
      
   //   
  }
  <?php if($adsEnable){ ?>
  function displayGoogleAds(){
    try{
      sesJqueryObject('ins').each(function(){
          (adsbygoogle = window.adsbygoogle || []).push({});
      });
      if(sesJqueryObject('script[src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"]').length == 0){        
        var script = document.createElement('script');
        script.src = '//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js';
        document.head.appendChild(script);  
      }
    }catch(e){
      //silence  
    }
  }
  <?php } ?>
    en4.core.runonce.add(function() {defaultSettingsSesadv();<?php if($adsEnable){ ?>displayGoogleAds();<?php } ?>});
    defaultSettingsSesadv();
 <?php if(!$this->feedOnly && $this->autoloadTimes > 0 && $this->scrollfeed){ ?>
    var autoloadTimes = '<?php echo $this->autoloadTimes; ?>';
    var counterLoadTime = 0;
    window.addEvent('load', function() {
      sesJqueryObject(window).scroll( function() {
        var containerId = '#activity-feed';
         if(typeof sesJqueryObject(containerId).offset() != 'undefined') {
          var heightOfContentDiv = sesJqueryObject(containerId).height();
          var fromtop = sesJqueryObject(this).scrollTop() + 300;
          if(fromtop > heightOfContentDiv - 100 && sesJqueryObject('#feed_viewmore_activityact').css('display') == 'block' && autoloadTimes > counterLoadTime){
            document.getElementById('feed_viewmore_activityact_link').click();
            counterLoadTime++;
          }
        }
      });
    });
  <?php } ?>
  </script>
<?php endif; ?>

<?php if( !empty($this->feedOnly) && empty($this->checkUpdate)): // Simple feed only for AJAX
  echo $this->activityLoop($this->activity, array(
    'action_id' => $this->action_id,
    'communityadsIds' => $this->communityadsIds,
    'viewAllComments' => $this->viewAllComments,
    'viewAllLikes' => $this->viewAllLikes,
    'getUpdate' => $this->getUpdate,
    'ulInclude'=>!$this->getUpdates ? 0 : $this->feedOnly,
    'contentCount'=>$this->contentCount,
    'userphotoalign' => $this->userphotoalign,
    'filterFeed'=>$this->filterFeed,
    'isMemberHomePage' => $this->isMemberHomePage,
    'isOnThisDayPage' => $this->isOnThisDayPage
  ));
  return; // Do no render the rest of the script in this mode
endif; ?>

<?php if( !empty($this->checkUpdate) ): // if this is for the live update
  if ($this->activityCount){ ?>
   <script type='text/javascript'>
          document.title = '(<?php echo $this->activityCount; ?>) ' + SesadvancedactivityUpdateHandler.title;
          SesadvancedactivityUpdateHandler.options.next_id = "<?php echo $this->firstid; ?>";
          <?php if($this->autoloadfeed){ ?>
            SesadvancedactivityUpdateHandler.getFeedUpdate("<?php echo $this->firstid; ?>");
            $("feed-update").empty();
          <?php } ?>
          sesJqueryObject('#count_new_feed').html("<span><?php echo $this->activityCount; ?></span>");
        </script>
   <div class='tip' style="display:<?php echo ($this->autoloadfeed) ? 'none' : '' ?>">
          <span>
            <a href='javascript:void(0);' onclick='javascript:SesadvancedactivityUpdateHandler.getFeedUpdate("<?php echo $this->firstid ?>");$("feed-update").empty();sesJqueryObject("#count_new_feed").html("");sesJqueryObject("#count_new_feed").hide();'>
              <?php echo $this->translate(array(
                  '%d new update is available - click this to show it.',
                  '%d new updates are available - click this to show them.',
                  $this->activityCount),
                $this->activityCount); ?>
            </a>
          </span>
        </div>
 <?php } 
  return; // Do no render the rest of the script in this mode
endif; ?>

<?php if( !empty($this->getUpdate) ): // if this is for the get live update ?>
<script type="text/javascript">
     SesadvancedactivityUpdateHandler.options.last_id = <?php echo sprintf('%d', $this->firstid) ?>;
   </script>
<?php endif; ?>
<style>
 #scheduled_post, #datetimepicker_edit{display:block !important;}
 </style>

<?php if( $this->enableComposer && !$this->isOnThisDayPage): ?>
<script type="application/javascript">
var sesadvancedactivityDesign = '<?php echo $this->design; ?>';
var userphotoalign = '<?php echo $this->userphotoalign; ?>';
var enableStatusBoxHighlight = '<?php echo $this->enableStatusBoxHighlight; ?>';
var counterLoopComposerItem = counterLoopComposerItemDe4 = 1;
var composeInstance;
 en4.core.runonce.add(function () {
     composeInstance = new Composer('activity_body',{
        overText : true,
        allowEmptyWithoutAttachment : false,
        allowEmptyWithAttachment : true,
        hideSubmitOnBlur : false,
        submitElement : false,
        useContentEditable : true  ,
        menuElement : 'compose-menu',
        baseHref : '<?php echo $this->baseUrl() ?>',
        lang : {
          'Post Something...' : '<?php echo $this->string()->escapeJavascript($this->translate('Post Something...')) ?>'
        }
    });
      
      sesJqueryObject(document).on('submit','#activity-form',function(e) {
        var activatedPlugin = composeInstance.getActivePlugin();
        if(activatedPlugin)
         var pluginName = activatedPlugin.getName();
        else 
          var pluginName = '';

        if(sesJqueryObject('#image_id').length > 0 && sesJqueryObject('#image_id').val() != '' || sesJqueryObject('#reaction_id').val() != '' || sesJqueryObject('#tag_location').val() != '' || ( sesJqueryObject('#feeling_activity').length > 0 && sesJqueryObject('#feeling_activity').val() != '' && sesJqueryObject('#feelingactivityid').val() != '')) {
          //silence  
        }else if(pluginName != 'buysell' && pluginName != 'quote' && pluginName != 'wishe' && pluginName != 'prayer' && pluginName != 'thought' && pluginName != 'text' && pluginName != 'sespagepoll' && pluginName != 'sesbusinesspoll' && pluginName != 'sesgrouppoll'){
          if( composeInstance.pluginReady ) {
            if( !composeInstance.options.allowEmptyWithAttachment && composeInstance.getContent() == '' ) {
              sesJqueryObject('.sesact_post_box').addClass('_blank');
              e.preventDefault();
              return;
            }
          } else {
            if( !composeInstance.options.allowEmptyWithoutAttachment && composeInstance.getContent() == '' ) {
              e.preventDefault();
              sesJqueryObject('.sesact_post_box').addClass('_blank');
              return;
            }
          }
        }else if (pluginName == "sespagepoll"){
			var isValidPoll = checkValidationPagePoll();
			if(isValidPoll == false){
				e.preventDefault();
				return;
			}
		}else if(pluginName == "sesbusinesspoll"){
				var isValidPoll = checkValidationBusinessPoll();
			if(isValidPoll == false){
				e.preventDefault();
				return;
			}
		}
		else if(pluginName == "sesgrouppoll"){
				var isValidPoll = checkValidationGroupPoll();
			if(isValidPoll == false){
				e.preventDefault();
				return;
			}
		}
		else if(pluginName == 'buysell'){
          if(!sesJqueryObject('#buysell-title').val()){
              if(!sesJqueryObject('.buyselltitle').length) {
                var errorHTMlbuysell = '<div class="sesact_post_error buyselltitle"><?php echo $this->translate("Please enter the title of your product.");?></div>';
                sesJqueryObject('.sesact_sell_composer_title').append(errorHTMlbuysell);
                sesJqueryObject('#buysell-title').parent().addClass('_blank');
                sesJqueryObject('#buysell-title').css('border','1px solid red');
              }
              e.preventDefault();
              return;
          }
          if(sesJqueryObject('#buy-url').val() && !isUrl(sesJqueryObject('#buy-url').val())){
              if(!sesJqueryObject('.buyurl').length) {
                var errorHTMlbuyurl = '<div class="sesact_post_error buyselltitle"><?php echo $this->translate("Please enter valid url.");?></div>';
                sesJqueryObject('.sesact_sell_composer_title').append(errorHTMlbuyurl);
                sesJqueryObject('#buy-url').parent().addClass('_blank');
                sesJqueryObject('#buy-url').css('border','1px solid red');
              }
              e.preventDefault();
              return;
          }else if(!sesJqueryObject('#buysell-price').val()){
              if(!sesJqueryObject('.buysellprice').length) {
                var errorHTMlbuysell = '<div class="sesact_post_error buysellprice"><?php echo $this->translate("Please enter the price of your product.");?></div>';
                sesJqueryObject('.sesact_sell_composer_price').append(errorHTMlbuysell);
                sesJqueryObject('#buysell-price').parent().parent().addClass('_blank');
                sesJqueryObject('#buysell-price').css('border','1px solid red');
              }
              e.preventDefault();
              return;
          }
          
            var field = '<input type="hidden" name="attachment[type]" value="buysell">';
            if(!sesJqueryObject('.fileupload-cnt').length)
              sesJqueryObject('#activity-form').append('<div style="display:none" class="fileupload-cnt">'+field+'</div>');
            else
              sesJqueryObject('.fileupload-cnt').html(field);
              
        } else if(pluginName == 'quote') {
          if(!sesJqueryObject('#quote-description').val()){
            if(!sesJqueryObject('.quotedescription').length) {
              var errorHTMlquote = '<div class="sesact_post_error quotedescription"><?php echo $this->translate("Please enter the quote.");?></div>';
              sesJqueryObject('.sesact_quote_composer_title').append(errorHTMlquote);
              sesJqueryObject('#quote-description').parent().addClass('_blank');
              sesJqueryObject('#quote-description').css('border','1px solid red');
            }
            e.preventDefault();
            return;
          }
          //Video check if choose from media type
          if(sesJqueryObject("input[name='mediatype']:checked").val() == 2 && sesJqueryObject('#video').val() == '') {
            if(!sesJqueryObject('#video').val()) {
              var errorHTMlquote = '<div class="sesact_post_error quotedescription"><?php echo $this->translate("Please enter the video url.");?></div>';
              sesJqueryObject('.sesact_quote_composer_title').append(errorHTMlquote);
              sesJqueryObject('#video').parent().addClass('_blank');
              sesJqueryObject('#video').css('border','1px solid red');
            }
            e.preventDefault();
            return;
          }
        } else if(pluginName == 'wishe') {
          if(!sesJqueryObject('#wishe-description').val()){
            if(!sesJqueryObject('.wishedescription').length) {
              var errorHTMlwishe = '<div class="sesact_post_error wishedescription"><?php echo $this->translate("Please enter the wishe.");?></div>';
              sesJqueryObject('.sesact_wishe_composer_title').append(errorHTMlwishe);
              sesJqueryObject('#wishe-description').parent().addClass('_blank');
              sesJqueryObject('#wishe-description').css('border','1px solid red');
            }
            e.preventDefault();
            return;
          }
        } else if(pluginName == 'prayer') {
          if(!sesJqueryObject('#prayer-description').val()){
            if(!sesJqueryObject('.prayerdescription').length) {
              var errorHTMlprayer = '<div class="sesact_post_error prayerdescription"><?php echo $this->translate("Please enter the prayer.");?></div>';
              sesJqueryObject('.sesact_prayer_composer_title').append(errorHTMlprayer);
              sesJqueryObject('#prayer-description').parent().addClass('_blank');
              sesJqueryObject('#prayer-description').css('border','1px solid red');
            }
            e.preventDefault();
            return;
          }
        } else if(pluginName == 'thought') {
          if(!sesJqueryObject('#thought-description').val()){
            if(!sesJqueryObject('.thoughtdescription').length) {
              var errorHTMlthought = '<div class="sesact_post_error thoughtdescription"><?php echo $this->translate("Please enter the thought.");?></div>';
              sesJqueryObject('.sesact_thought_composer_title').append(errorHTMlthought);
              sesJqueryObject('#thought-description').parent().addClass('_blank');
              sesJqueryObject('#thought-description').css('border','1px solid red');
            }
            e.preventDefault();
            return;
          }
        }
        else if(pluginName == 'text') {
          if(!sesJqueryObject('#summernote').val()) {
            if(!sesJqueryObject('.quotedescription').length) {
              var errorHTMlquote = '<div class="sesact_post_error quotedescription"><?php echo $this->translate("Please enter the description.");?></div>';
              sesJqueryObject('.sesact_quote_composer_title').append(errorHTMlquote);
              sesJqueryObject('#summernote').parent().addClass('_blank');
              sesJqueryObject('#summernote').css('border','1px solid red');
            }
            e.preventDefault();
            return;
          }
        }
        sesJqueryObject('.sesact_post_box').removeClass('_blank');
      <?php if($this->submitWithAjax){ ?>
        e.preventDefault();
        var url = "<?php echo $this->url(array('module' => 'sesadvancedactivity', 'controller' => 'index', 'action' => 'post'), 'default', true) ?>";
        submitActivityFeedWithAjax(url,'<i class="fas fa-circle-notch fa-spin"></i>','<?php echo $this->translate("Share") ?>',this);
        return;
     <?php } ?>
      });
      
      if(sesJqueryObject('#hashtagtextsesadv').val() && typeof composeInstance != "undefined") {
        composeInstance.setContent('#'+sesJqueryObject('#hashtagtextsesadv').val()).trigger('keyup');
      }

      sesJqueryObject("#activity_body").css("height", "auto");
      
 });
 sesJqueryObject(document).on('keyup', '#buysell-title, #buysell-price, #buy-url', function() {
  if(!sesJqueryObject(this).val())
    return;
  sesJqueryObject(this).parent().removeClass('_blank');
  sesJqueryObject(this).parent().parent().removeClass('_blank');
  sesJqueryObject(this).css('border', '');
  sesJqueryObject(this).parent().find('.sesact_post_error').remove();

 });
</script>

  <?php if($this->enablestatusbox == 0) { ?>
    <?php $display = 'none'; ?>
  <?php } else if($this->enablestatusbox == 1 && $viewer && $this->subject()) { ?>
    <?php if($viewer->getIdentity() && ($viewer->getIdentity() == $this->subject()->getIdentity())) { ?>
      <?php $display = 'block'; ?>
    <?php } else { ?>
      <?php $display = 'none'; ?>
    <?php } ?>
  <?php } else if($this->enablestatusbox == 2) { ?>
    <?php $display = 'block'; ?>
  <?php } ?>
  <div class="sesact_post_container_wrapper sesbasic_clearfix sesbasic_bxs <?php if($this->design == 2){ ?>sesact_cd_p<?php } ?> <?php  if($this->design == 4){ ?>isbigicons<?php } ?>">
	<div class="sesact_post_container_overlay"></div>
	<div class="sesact_post_container sesbasic_clearfix" style="display:<?php echo $display ?>;">
    <form enctype="multipart/form-data" method="post" action="<?php echo $this->url(array('module' => 'sesadvancedactivity', 'controller' => 'index', 'action' => 'post'), 'default', true) ?>" class="" id="activity-form">
      <?php  if($this->design == 3){ ?>
       <div class="sesact_post_head sesbasic_clearfix">
        <div id="sesact_post_media_options_before"></div>
       </div>
      <?php  } ?>
      <?php if($this->showUpperMenuDesigns && ($this->design == 1 || $this->design == 2) && ($this->photoActivator || $this->albumActivator || $this->videoActivator )){ ?>
      <div class="sesact_post_head sesact_post_head_fixed sesbasic_clearfix">
      	<span class="sesact_post_head_option tool_i_make_post option_selected"><a class="selectedTabClick" href="javascript:;" data-rel="all"><span><?php echo $this->translate("Make Post");?></span></a></span>
        <?php if($this->photoActivator){ ?>
          <span class="sesact_post_head_option tool_i_photo"><a href="javascript:;" class="selectedTabClick"  data-rel="photo"><span><?php echo $this->translate("Add Photo");?></span></a></span>
        <?php } ?>
        <?php if($this->albumActivator){ ?>
        <span class="sesact_post_head_option tool_i_album"><a href="javascript:;" class="selectedTabClick"  data-rel="album"><span><?php echo $this->translate("Add Photo Album");?></span></a></span>
        <?php } ?>
        <?php if($this->videoActivator){ ?>
          <span class="sesact_post_head_option tool_i_video"><a href="javascript:;" class="selectedTabClick"  data-rel="video"><span><?php echo $this->translate("Add Video");?></span></a></span>
        <?php } ?>
      </div>
      <?php } ?>
    	<div class="sesact_post_box sesbasic_clearfix" id="sesact_post_box_status">
      	<div class="sesact_post_box_img" id="sesact_post_box_img">
        <?php 
        echo $this->htmlLink('javascript:;', $this->itemPhoto($this->viewer(), 'thumb.icon', $this->viewer()->getTitle()), array()) ?>
        </div>
       <?php if($this->design == 2 || $this->design == 4){ ?>
        <div class="sesact_post_box_close" style="display:none;"><a class="fas fa-times sesact_post_box_close_a sesadv_tooltip" title="<?php echo $this->escape($this->translate('Close')) ?>" href="javascript:;"></a></div>
       <?php } ?>
        <textarea style="display:none;" id="activity_body" class="resetaftersubmit" cols="1" rows="1" name="body" placeholder="<?php echo $this->escape($this->translate($this->statusplacehoder)) ?>"></textarea>
        <input type="hidden" name="return_url" value="<?php echo $this->url() ?>" />
        <?php if( $this->viewer() && $this->subject() && !$this->viewer()->isSelf($this->subject())): ?>
          <input type="hidden" name="subject" value="<?php echo $this->subject()->getGuid() ?>" />
        <?php endif; ?>
        <input type="hidden" name="crosspostVal" id="crosspostVal"  class="resetaftersubmit" value="">
        <input type="hidden" name="reaction_id" class="resetaftersubmit" id="reaction_id" value="" />
        <?php if( $this->formToken ): ?>
          <input type="hidden" name="token" value="<?php echo $this->formToken ?>" />
        <?php endif ?>
         <input type="hidden" id="hashtagtextsesadv" name="hashtagtextsesadv" value="<?php echo isset($_GET['hashtag']) ? $_GET['hashtag'] : ''; ?>" />
        <input type="hidden" name="fancyalbumuploadfileids" class="resetaftersubmit" id="fancyalbumuploadfileids">
        <div class="sesact_post_error"><?php echo $this->translate("It seems, that the post is blank. Please write or attach something to share your post.");?></div>
        <div id="sesadvancedactivity-menu" class="sesadvancedactivity-menu sesact_post_tools">
          <span class="sesadvancedactivity-menu-selector" id="sesadvancedactivity-menu-selector"></span>
          
        <?php if($this->design == 1 || $this->design == 3) { ?>
          <?php if(in_array('shedulepost',$this->composerOptions)){ ?>
            <?php $enableShedulepost = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sesadvactivity', $viewer, 'composeroptions'); ?>
            <?php if(in_array('shedulepost', $enableShedulepost)) { ?>
              <?php $this->headScript()->appendFile($staticBaseUrl . 'application/modules/Sesadvancedactivity/externals/scripts/schedule/bootstrap.min.js'); ?>
              <?php $this->headScript()->appendFile($staticBaseUrl . 'application/modules/Sesadvancedactivity/externals/scripts/schedule/bootstrap-datetimepicker.min.js'); ?>
                <span class="sesact_post_tool_i tool_i_sheduled_post">
                  <a href="javascript:;" id="sesadvancedactivity_shedulepost" class="sesadv_tooltip" title="<?php echo $this->translate("Schedule Post"); ?>"></a>
                </span>
              <div class="sesact_popup_overlay sesadvancedactivity_shedulepost_overlay" style="display:none;"></div>
              <div class="sesact_popup sesadvancedactivity_shedulepost_select sesbasic_bxs" style="display:none;">
                <div class="sesact_popup_header"><?php echo $this->translate("Schedule Post"); ?></div>
                <div class="sesact_popup_cont">
                  <b><?php echo $this->translate("Schedule Your Post"); ?></b>
                  <p><?php echo $this->translate("Select date and time on which you want to publish your post."); ?></p>
                  <div class="sesact_time_input_wrapper">
                    <div id="datetimepicker" class="input-append date sesact_time_input">
                      <input type="text" name="scheduled_post" id="scheduled_post" class="resetaftersubmit"></input>
                      <span class="add-on" title="Select Time"><i data-time-icon="icon-time" data-date-icon="icon-calendar"></i></span>
                    </div>
                    <div class="sesact_error sesadvancedactivity_shedulepost_error"></div>
                  </div>
                </div>
                <div class="sesact_popup_btns">
                 <button type="submit" class="schedule_post_schedue"><?php echo $this->translate("Schedule"); ?></button>
                 <button class="close schedule_post_close"><?php echo $this->translate("Cancel"); ?></button>
                </div>
              </div>
            <?php } ?>
          <?php } ?>
          <?php if(in_array('tagUseses',$this->composerOptions)){ ?>
            <span class="sesact_post_tool_i tool_i_tag">
              <a href="javascript:;" id="sesadvancedactivity_tag" class="sesadv_tooltip" title="<?php echo $this->translate('Tag People'); ?>">&nbsp;</a>
            </span>
          <?php } ?>
          <?php if(in_array('locationses',$this->composerOptions) && Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 1)){ ?>
            <span class="sesact_post_tool_i tool_i_location">
              <a href="javascript:;" id="sesadvancedactivity_location" title="<?php echo $this->translate('Check In'); ?>" class="sesadv_tooltip">&nbsp;</a>
            </span>
          <?php } ?>
          <?php if(in_array('smilesses',$this->composerOptions)){ ?>
          	<?php if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesadvancedcomment') && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedcomment.pluginactivated')) { ?>
            	<span class="sesact_post_tool_i tool_i_sticker">
                <a href="javascript:;" class="sesadv_tooltip emoji_comment_select activity_emoji_content_a" title="<?php echo $this->translate('Stickers'); ?>">&nbsp;</a>
              </span>  
            <?php } else { ?>
            	<span class="sesact_post_tool_i tool_i_emoji">
                <a href="javascript:;" class="sesadv_tooltip emoji_comment_select activity_emoji_content_a" title="<?php echo $this->translate('Emoticons'); ?>">&nbsp;</a>
          		</span>
          	<?php } ?>
          <?php } ?>
          <?php if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesfeelingactivity') && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesfeelingactivity.enablefeeling', 1)) { ?>
            <?php $enablefeeling = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sesadvactivity', $viewer, 'composeroptions'); ?>
            <?php if(count($getFeelings) > 0 && in_array('feelingssctivity',$this->composerOptions) && in_array('feelingssctivity', $enablefeeling)): ?>
              <span class="sesact_post_tool_i tool_i_feelings" id="sesadvancedactivity_feelings">
                <a href="javascript:;" id="sesadvancedactivity_feelingsa" class="sesadv_tooltip" title="<?php echo $this->translate('Feeling/Activity'); ?>">&nbsp;</a>
              </span>
            <?php endif; ?>
          <?php } ?>
          
          <?php if(defined('SESFEEDGIFENABLED') && in_array('sesfeedgif',$this->composerOptions)) { ?>
            <?php $enable = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sesadvactivity', $viewer, 'composeroptions'); ?>
            <?php if(in_array('sesfeedgif', $enable)) { ?>
              <span class="sesact_post_tool_i tool_i_gif">
                <a href="javascript:;" class="sesadv_tooltip gif_comment_select activity_gif_content_a" title="<?php echo $this->translate('GIF'); ?>">&nbsp;</a>
              </span>
              <input type="hidden" name="image_id" class="resetaftersubmit" id="image_id" value="" />
            <?php } ?>
          <?php } ?>
        <?php } ?>
        
        <?php if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesemoji')) { ?>
          <?php $enableattachement = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sesadvactivity', $viewer, 'cmtattachement'); ?>
          <?php if(count($getEmojis) > 0 && in_array('emojisses',$this->composerOptions) && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesemoji.enableemoji', 1) && in_array('emojis', $enableattachement)): ?>
            <span class="sesact_post_tool_i tool_i_feelings feeling_emoji_comment_select" id="sesadvancedactivity_feeling_emojis" style="display:none;">
              <a href="javascript:;" id="sesadvancedactivity_feeling_emojisa" class="sesadv_tooltip" title="<?php echo $this->translate('Emojis'); ?>">&nbsp;</a>
            </span>
          <?php endif; ?>
        <?php } else { ?>
          <span class="sesact_post_tool_i tool_i_emoji">
            <a href="javascript:;" id="sesadvancedactivityemoji-statusbox"  class="sesadv_tooltip" title="<?php echo $this->translate('Emoticons'); ?>">&nbsp;</a>
            <div id="sesadvancedactivityemoji_statusbox" class="ses_emoji_container sesbasic_bxs">
              <div class="ses_emoji_container_arrow"></div>
              <div class="ses_emoji_container_inner sesbasic_clearfix">
                <div class="ses_emoji_holder">
                  <div class="sesbasic_loading_container" style="height:100%;"></div>
                </div>
              </div>
            </div>
          </span>
        <?php } ?> 
        
        </div>
        
        <?php  if($this->design == 4) { ?>
        	<div id="sesact_post_options_design4" class="sesact_post_options">
          </div>
        <?php } ?>
        
          <div id="sesact_post_tags_sesadv" class="sesact_post_tags sesbasic_text_light" <?php if(defined('SESFEEDBGENABLED')) { ?> style="display:none;" <?php } ?>>
            <?php if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesfeelingactivity') && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesfeelingactivity.enablefeeling', 1)): ?><span style="display:none;" id="feeling_elem_act">- </span><?php endif; ?> <span style="display:none;" id="dash_elem_act">-</span>	<?php if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sespage')): ?><span style="display:none;" id="sespage_elem_act"></span><?php endif; ?><span id="tag_friend_cnt" style="display:none;"> with </span> <span id="location_elem_act" style="display:none;"></span>
          </div>
          <?php if($this->enablefeedbgwidget && defined('SESFEEDBGENABLED') && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesfeedbg.enablefeedbg', 1)) {  ?>
            <?php
            $sesfeedbg_enablefeedbg = false;
            $enablefeedbg = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sesadvactivity', $viewer, 'composeroptions');
            if(in_array('enablefeedbg', $enablefeedbg)) {
              $sesfeedbg_enablefeedbg = true;
            }
            ?>
            <?php $sesfeedbg_limit_show = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'sesadvactivity', 'sesfeedbg_max'); ?>
            <?php if($sesfeedbg_enablefeedbg) { ?>
              <?php 
              $getFeaturedBackgrounds = Engine_Api::_()->getDbTable('backgrounds', 'sesfeedbg')->getBackgrounds( array('admin' => 1, 'fetchAll' => 1, 'sesfeedbg_limit_show' => 5, 'featured' => 1) );
              $featured = $backgrounds = array();
              foreach($getFeaturedBackgrounds as $getFeaturedBackground) {
                $featured[] = $getFeaturedBackground->background_id;
              }
              //https://github.com/Vaibhav-Agarwal06/sedev/issues/378
              // if featured images are available show in first then rest of images are come according to member level.
              // featured + member_level
              if(count($featured) > 5) {
                $sesfeedbg_limit_show = 5;
              }
              $getBackgrounds = Engine_Api::_()->getDbTable('backgrounds', 'sesfeedbg')->getBackgrounds( array('admin' => 1, 'fetchAll' => 1, 'sesfeedbg_limit_show' => $sesfeedbg_limit_show, 'feedbgorder' => $this->feedbgorder, 'featuredbgIds' => $featured)); 
              foreach($getBackgrounds as $getBackground) {
                $backgrounds[] = $getBackground->background_id;
              }
              if(count($featured) > 0) {
                $backgrounds = array_merge($featured, $backgrounds);
              }
              ?>
              <?php if( count( $backgrounds ) > 0 ) { ?>
                <div id="feedbg_main_continer" style="display:none;">
                  <a href="javascript:void(0);" id="hideshowfeedbgcont"><i onclick="hideshowfeedbgcont();" class="fa fa-angle-left"></i></a>
                  <ul id="feedbg_content">
                    <li>
                      <a class="feedbg_active" id="feedbg_image_defaultimage" href="javascript:void(0);" onclick="feedbgimage('defaultimage')"><img height="30px;" width="30px;" id="feed_bg_image_defaultimage" alt="" src="<?php echo 'application/modules/Sesfeedbg/externals/images/white.png'; ?>" /></a>
                    </li>
                    <?php foreach($backgrounds as $getBackground) {
                      $getBackground = Engine_Api::_()->getItem('sesfeedbg_background', $getBackground);
                    ?>
                      <?php if($getBackground->file_id) {
                        $photo = Engine_Api::_()->storage()->get($getBackground->file_id, '');
                        if($photo) {
                          $photo = $photo->getPhotoUrl(); ?>
                       <li>
                         <a id="feedbg_image_<?php echo $getBackground->background_id; ?>" href="javascript:void(0);" onclick="feedbgimage('<?php echo $getBackground->background_id; ?>', 'photo');setFocus();"><img height="30px;" width="30px;" id="feed_bg_image_<?php echo $getBackground->background_id; ?>" data-id="<?php echo $getBackground->background_id; ?>" alt="" src="<?php echo $photo; ?>" /></a>
                       </li>
                      <?php  }
                      }
                      ?>
                    <?php } ?>
  <!--                  <li class="_more">
                      <a href="#" class="sesadv_tooltip" title='<?php //echo $this->translate("More"); ?>'><i class="fa fa-th-large"></i></a>
                    </li>-->
                    <?php  ?>
                  </ul>
                  <input type="hidden" name="feedbgid" id="feedbgid" value="" class="resetaftersubmit">
                  <input type="hidden" name="feedbgid_isphoto" id="feedbgid_isphoto" value="1" class="resetaftersubmit">
                </div>
              <?php } ?>
            <?php } ?>
          <?php }  ?>
      </div>
      
      <div id="sescomposer-tray-container"></div>
      <div class="sesact_post_tag_container sesbasic_clearfix sesact_post_tag_cnt" style="display:none;">
        <span class="tag">With</span>
        <div class="sesact_post_tags_holder">
          <div id="toValues-element">
          </div>
        	<div class="sesact_post_tag_input">
          	<input type="text" class="resetaftersubmit" placeholder="<?php echo $this->translate('Who are you with?'); ?>" id="tag_friends_input" />
            <div id="toValues-wrapper" style="display:none">
            <input type="hidden" id="toValues" name="tag_friends" class="resetaftersubmit">
            </div>
          </div>
        </div>	
      </div>
      <div class="sesact_post_tag_container sesbasic_clearfix sesact_post_location_container" style="display:none;">
        <span class="tag">At</span>
        <div class="sesact_post_tags_holder">
          <div id="locValues-element"></div>
        	<div class="sesact_post_tag_input">
          	<input type="text" placeholder="<?php echo $this->translate('Where are you?'); ?>" name="tag_location" id="tag_location" class="resetaftersubmit"/>
            <input type="hidden" name="activitylng" id="activitylng" value="" class="resetaftersubmit">
            <input type="hidden" name="activitylat" id="activitylat" value="" class="resetaftersubmit">
          </div>
        </div>	
      </div>
      <div id="sesact_page_tags"></div>
       <div id="sesact_business_tags"></div>
        <div id="sesact_group_tags"></div>
      <?php // Feeling work ?>
      <?php if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesfeelingactivity')) { ?>
        <div id="sesact_post_feeling_container" class="sesact_post_tag_container sesbasic_clearfix sesact_post_feeling_container" style="display:none;">
          <span id="feelingActType" class="tag" style="display:none;"></span>
          <div class="sesact_post_tags_holder">
            <div id="feelingValues-element"></div>
            <div class="sesact_post_tag_input">
              <input autofocus autocomplete="off" type="text" placeholder="<?php echo $this->translate('Choose Feeling or activity...'); ?>" name="feeling_activity" id="feeling_activity" class="resetaftersubmit"/>
              
              <a onclick="feelingactivityremoveact();" style="display:none;" href="javascript:void(0);" class="feeling_activity_remove_act notclose" id="feeling_activity_remove_act" title="<?php echo $this->translate('Remove'); ?>">x</a>
              
              <input type="hidden" name="feelingactivityid" id="feelingactivityid" value="" class="resetaftersubmit">
              <input type="hidden" name="feelingactivityiconid" id="feelingactivityiconid" value="" class="resetaftersubmit">
              <input type="hidden" name="feelingactivity_resource_type" id="feelingactivity_resource_type" value="" class="resetaftersubmit">
              <input type="hidden" name="feelingactivity_custom" id="feelingactivity_custom" value="" class="resetaftersubmit">
              <input type="hidden" name="feelingactivity_customtext" id="feelingactivity_customtext" value="" class="resetaftersubmit">
              <input type="hidden" name="feelingactivity_type" id="feelingactivity_type" value="" class="resetaftersubmit">
            </div>
          </div>
          
          <div class="sesact_post_feelingautocompleter_container sesact_post_feelings_autosuggest" style="display:none;">
          	<div class="sesbasic_clearfix sesbasic_custom_scroll">
              <ul class="sesfeelingactivity-ul" id="showSearchResults"></ul>
            </div>	
          </div>
          
          <div class="sesact_post_feelingcontent_container sesact_post_feelings_autosuggest" style="display:none;">
          	<div class="sesbasic_clearfix sesbasic_custom_scroll">
              <ul id="all_feelings">
                <?php $feelings = Engine_Api::_()->getDbTable('feelings', 'sesfeelingactivity')->getFeelings(array('fetchAll' => 1, 'admin' => 0));  ?>
                <?php foreach($feelings as $feeling): ?>
                  <?php $photo = Engine_Api::_()->storage()->get($feeling->file_id, '');
                      if($photo) {
                      $photo = $photo->getPhotoUrl(); ?>
                  <li data-title="<?php echo $feeling->title; ?>" class="sesact_feelingactivitytype sesbasic_clearfix" data-rel="<?php echo $feeling->feeling_id; ?>" data-type="<?php echo $feeling->type; ?>">
                    <a href="javascript:void(0);" class="sesact_feelingactivitytypea">
                      <img id="sesactfeelingactivitytypeimg_<?php echo $feeling->feeling_id; ?>" title="<?php echo $feeling->title ?>" src="<?php echo $photo; ?>">
                      <span><?php echo $this->translate($feeling->title); ?></span>
                    </a>
                  </li>
                  <?php } ?>
                <?php endforeach; ?>
              </ul>
            </div>  
          </div>	
        </div>
        
      <?php } ?>
      <?php // Feeling work ?>
      
      <?php if($this->design == 2 || $this->design == 4) { ?>
        <div class="sesact_post_media_options sesbasic_clearfix">
          <div id="sesact_post_media_options_before"></div>
          <?php if(in_array('shedulepost',$this->composerOptions)){ ?>
            <?php $enableShedulepost = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sesadvactivity', $viewer, 'composeroptions'); ?>
            <?php if(in_array('shedulepost', $enableShedulepost)) { ?>
              <?php $this->headScript()->appendFile($staticBaseUrl . 'application/modules/Sesadvancedactivity/externals/scripts/schedule/bootstrap.min.js'); ?>
              <?php $this->headScript()->appendFile($staticBaseUrl . 'application/modules/Sesadvancedactivity/externals/scripts/schedule/bootstrap-datetimepicker.min.js'); ?>
                <span class="sesact_post_media_options_icon tool_i_sheduled_post" style="display:none;">
                  <a href="javascript:;" id="sesadvancedactivity_shedulepost" class="sesadv_tooltip" title="<?php echo $this->translate('Schedule Post'); ?>"><span><?php echo $this->translate('Schedule Post'); ?></span></a>
                </span>
              <div class="sesact_popup_overlay sesadvancedactivity_shedulepost_overlay" style="display:none;"></div>
              <div class="sesact_popup sesadvancedactivity_shedulepost_select sesbasic_bxs" style="display:none;">
                <div class="sesact_popup_header"><?php echo $this->translate('Schedule Post'); ?></div>
                <div class="sesact_popup_cont">
                  <b><?php echo $this->translate("Schedule Your Post"); ?></b>
                  <p><?php echo $this->translate("Select date and time on which you want to publish your post."); ?></p>
                  <div class="sesact_time_input_wrapper">
                    <div id="datetimepicker" class="input-append date sesact_time_input">
                      <input type="text" name="scheduled_post" id="scheduled_post" class="resetaftersubmit"></input>
                      <span class="add-on sesadv_tooltip" title="View Calendar"><i data-time-icon="icon-time" data-date-icon="icon-calendar"></i></span>
                    </div>
                    <div class="sesact_error sesadvancedactivity_shedulepost_error"></div>
                  </div>
                </div>
                <div class="sesact_popup_btns">
                 <button type="submit" class="schedule_post_schedue"><?php echo $this->translate('Schedule'); ?></button>
                 <button class="close schedule_post_close"><?php echo $this->translate('Cancel'); ?></button>
                </div>
              </div>
            <?php } ?>
          <?php } ?>
          <?php if(in_array('tagUseses',$this->composerOptions)){ ?>
            <span class="sesact_post_media_options_icon tool_i_tag" style="display:none;">
              <a href="javascript:;" id="sesadvancedactivity_tag" class="sesadv_tooltip" title="<?php echo $this->translate('Tag People'); ?>"><span><?php echo $this->translate('Tag People'); ?></span></a>
            </span>
          <?php } ?>
          <?php if($this->isGoogleApiKeySaved){ ?>
            <?php if(in_array('locationses',$this->composerOptions)){ ?>
                <?php $enable = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sesadvactivity', $viewer, 'composeroptions'); ?>
                <?php if(in_array('locationses', $enable)) { ?>
                  <span class="sesact_post_media_options_icon tool_i_location" style="display:none;">
                    <a href="javascript:;" id="sesadvancedactivity_location" title="Check In" class="sesadv_tooltip"><span><?php echo $this->translate('Check In'); ?></span></a>
                  </span>
              <?php } ?>
            <?php } ?>
          <?php } ?>
          <?php if(in_array('smilesses',$this->composerOptions)){ ?>
          	<?php if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesadvancedcomment') && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedcomment.pluginactivated')) { ?>
              <?php if(in_array('stickers',$this->composerOptions)){ ?>
              	<span class="sesact_post_media_options_icon tool_i_sticker" style="display:none;">
                	<a href="javascript:;" class="sesadv_tooltip emoji_comment_select activity_emoji_content_a" title="<?php echo $this->translate('Stickers'); ?>"><span class="emoji_comment_select"><?php echo $this->translate('Stickers'); ?></span></a>
                </span>
              <?php } ?>
            <?php } else { ?>
              <span class="sesact_post_media_options_icon tool_i_emoji" style="display:none;">	
              	<a href="javascript:;" class="sesadv_tooltip emoji_comment_select activity_emoji_content_a" title="<?php echo $this->translate('Emoticons'); ?>"><span class="emoji_comment_select"><?php echo $this->translate('Emoticons'); ?></span></a>
            	</span>
            <?php } ?>            
          <?php } ?>
          
          <?php //Feeling Work ?>
          <?php if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesfeelingactivity') && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesfeelingactivity.enablefeeling', 1)) { ?>
            <?php $enablefeeling = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sesadvactivity', $viewer, 'composeroptions'); ?>
            <?php if(count($getFeelings) > 0 && in_array('feelingssctivity',$this->composerOptions) && in_array('feelingssctivity', $enablefeeling)): ?>
              <span class="sesact_post_media_options_icon tool_i_feelings" style="display:none;" id="sesadvancedactivity_feelings">
                <a id="sesadvancedactivity_feelingsa" href="javascript:;"  class="sesadv_tooltip" title="<?php echo $this->translate('Feeling/Activity'); ?>"><span class="sesadvancedactivity_feelingsspan"><?php echo $this->translate('Feeling/Activity'); ?></span></a>
              </span>
            <?php endif; ?>
          <?php } ?>
          
          <?php if(defined('SESFEEDGIFENABLED') && in_array('sesfeedgif',$this->composerOptions)){ ?>
            <?php $enable = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sesadvactivity', $viewer, 'composeroptions'); ?>
            <?php if(in_array('sesfeedgif', $enable)) { ?>
              <span class="sesact_post_media_options_icon tool_i_gif" style="display:none;">
                <a href="javascript:;" class="sesadv_tooltip gif_comment_select activity_gif_content_a" title="<?php echo $this->translate('GIF'); ?>"><span class="gif_comment_select"><?php echo $this->translate('GIF'); ?></span></a>
                <input type="hidden" name="image_id" class="resetaftersubmit" id="image_id" value="" />
              </span>
            <?php } ?>
          <?php } ?>
        </div>
      <?php } ?>
       <?php $privacyFeed = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.view.privacy'); ?>
       <?php
        $privacyFeedHold = Engine_Api::_()->getApi('settings', 'core')->getSetting($this->viewer()->getIdentity().".activity.user.setting");
        ?>

      <div id="compose-menu" class="sesact_compose_menu" <?php if($this->design == 4) { ?> style="display:none;" <?php } ?>>
        <input type="hidden" name="privacy" id="privacy"  value="<?php echo $privacyFeedHold; ?>">
        <div class="sesact_compose_menu_btns notclose">
        
        	<div class="sesact_chooser sesact_content_pulldown_wrapper" style="display:none;">
          	<a href="javascript:void(0);" class="sesact_privacy_btn sesact_chooser_btn"><i class="_icon fa fa-users sesbasic_text_light"></i><span><?php echo $this->translate('Select Pages'); ?></span><i class="_arrow fa fa-caret-down"></i></a>
            <div class="sesact_content_pulldown" style="display:none;">
            	<ul class="sesact_content_pulldown_list">
              </ul>
            </div>
          </div>
          
          <?php if($this->allowprivacysetting){ ?>
            <div class="sesact_privacy_chooser sesact_chooser sesact_pulldown_wrapper">
              <a href="javascript:void(0);" class="sesact_privacy_btn sesact_chooser_btn"><i id="sesadv_privacy_icon"></i><span id="adv_pri_option"><?php if(in_array('everyone',$privacyFeed)){ echo $this->translate('Everyone'); } else{ echo $this->translate($privacyFeed[0]); }  ?></span><i class="_arrow fa fa-caret-down"></i></a>
              <div class="sesact_pulldown">
                <div class="sesact_pulldown_cont isicon">
                  <ul class="adv_privacy_optn">
                   
                    <?php if(in_array('everyone',$privacyFeed)){ ?>
                    <li data-src="everyone" class=""><a href="javascript:;"><i class="sesact_public"></i><span><?php echo $this->translate('Everyone'); ?></span></a></li>
                    <?php } ?>
                    <?php if(in_array('networks',$privacyFeed)){ ?>
                    <li data-src="networks"><a href="javascript:;"><i class="sesact_network"></i><span><?php echo $this->translate('Friends & Networks'); ?></span></a></li>
                    <?php } ?>
                    <?php if(in_array('friends',$privacyFeed)){ ?>
                    <li data-src="friends"><a href="javascript:;"><i class="sesact_friends"></i><span><?php echo $this->translate('Friends Only'); ?></span></a></li>
                    <?php } ?>
                    <?php if(in_array('onlyme',$privacyFeed)){ ?>
                    <li data-src="onlyme"><a href="javascript:;"><i class="sesact_me"></i><span><?php echo $this->translate('Only Me'); ?></span></a></li>
                    <?php } ?>
                    <?php if($this->allownetworkprivacy){ ?>
                    <?php if(count($this->usernetworks)){ ?>
                    <li class="_sep"></li>
                    <?php foreach($this->usernetworks as $usernetworks){ ?>
                      <li data-src="network_list" class="network sesadv_network" data-rel="<?php echo $usernetworks->getIdentity(); ?>"><a href="javascript:;"><i class="sesact_network"></i><span><?php echo $this->translate($usernetworks->getTitle()); ?></span></a></li>
                    <?php }
                    if(count($this->usernetworks) > 1){
                     ?>
                    <li class="multiple mutiselect" data-rel="network-multi"><a href="javascript:;"><i class="sesact_network"></i><span><?php echo $this->translate('Multiple Networks'); ?></span></a></li>
                    <?php 
                      }
                    } ?>
                    <?php } ?>
                    <?php if($this->allowlistprivacy){ ?>
                    <?php if(count($this->userlists)){ ?>
                    <li class="_sep"></li>
                    <?php foreach($this->userlists as $userlists){ ?>
                      <li data-src="members_list" class="lists sesadv_list" data-rel="<?php echo $userlists->getIdentity(); ?>"><a href="javascript:;"><i class="sesact_list"></i><span><?php echo $this->translate($userlists->getTitle()); ?></span></a></li>
                    <?php } 
                     if(count($this->userlists) > 1){
                    ?>
                    <li class="multiple mutiselect" data-rel="lists-multi"><a href="javascript:;"><i class="sesact_list"></i><span><?php echo $this->translate('Multiptle Lists'); ?></span></a></li>
                    <?php 
                      }
                    } ?>
                    <?php } ?>
                  </ul>
                </div>													
              </div>
            </div>
          <?php } ?>
        	<button id="compose-submit" type="submit"><?php echo $this->translate("Share") ?></button>
        </div>
        <span class="composer_crosspost_toggle sesadv_tooltip" href="javascript:void(0);" title="<?php echo $this->translate('Crosspost');?>" style="display:none;"></span>
      </div>
  	</form>
  <?php //if($this->design == 2){ ?>
    <div class="sesact_popup_overlay sesact_confirmation_popup_overlay" style="display:none;"></div>
    <div class="sesact_popup sesact_confirmation_popup sesbasic_bxs" style="display:none;">
      <div class="sesact_popup_header"><?php echo $this->translate("Finish Your Post?"); ?></div>
      <div class="sesact_popup_cont"><?php echo $this->translate("If you leave now, your post won't be saved."); ?></div>
      <div class="sesact_popup_btns">
        <button id="discard_post"><?php echo $this->translate("Discard Post"); ?></button>
        <button id="goto_post"><?php echo $this->translate("Go to Post"); ?></button>
      </div>
    </div>
  <?php //} ?>
    <?php
  if (APPLICATION_ENV == 'production')
    $this->headScript()
      ->appendFile($staticBaseUrl . 'externals/autocompleter/Autocompleter.min.js');
  else
    $this->headScript()
      ->appendFile($staticBaseUrl . 'externals/autocompleter/Observer.js')
      ->appendFile($staticBaseUrl . 'externals/autocompleter/Autocompleter.js')
      ->appendFile($staticBaseUrl . 'externals/autocompleter/Autocompleter.Local.js')
      ->appendFile($staticBaseUrl . 'externals/autocompleter/Autocompleter.Request.js')
      ;
?>
    
    <?php
      $this->headScript()
        ->appendFile($staticBaseUrl . 'externals/mdetect/mdetect' . ( APPLICATION_ENV != 'development' ? '.min' : '' ) . '.js')
        ->appendFile($staticBaseUrl . 'application/modules/Sesadvancedactivity/externals/scripts/composer.js');
    ?>

     
    <?php foreach( $this->composePartials as $partial ): ?>
      <?php echo $this->partial($partial[0], $partial[1], array('isMemberHomePage' => $this->isMemberHomePage)) ?>
    <?php endforeach; ?>
    
  </div>
  </div>
<?php endif; ?>
<script type="text/javascript">
    sesJqueryObject(document).on('click',':not(#sesadvancedactivityemoji-statusbox)',function(){
        if(sesJqueryObject("#sesadvancedactivityemoji-statusbox")){
          if(sesJqueryObject("#sesadvancedactivityemoji-statusbox").hasClass('active')){
            sesJqueryObject("#sesadvancedactivityemoji-statusbox").removeClass('active');
            sesJqueryObject("#sesadvancedactivityemoji_statusbox").hide();
          }
        }
      });
      sesJqueryObject(document).on('click','#sesadvancedactivity_tag, .sestag_clk',function(e){
        that = sesJqueryObject(this);
        if(sesJqueryObject(this).hasClass('.sestag_clk'))
           that = sesJqueryObject('#sesadvancedactivity_tag');
         if(sesJqueryObject(that).hasClass('active')){
           sesJqueryObject(that).removeClass('active');
           sesJqueryObject('.sesact_post_tag_cnt').hide();
           return;
         }
         sesJqueryObject('.sesact_post_tag_cnt').show();
         sesJqueryObject(that).addClass('active');
      });
      sesJqueryObject(document).on('click','#sesadvancedactivity_location, .seloc_clk',function(e){
        that = sesJqueryObject(this);
        if(sesJqueryObject(this).hasClass('.seloc_clk'))
           that = sesJqueryObject('#sesadvancedactivity_location');
         if(sesJqueryObject(this).hasClass('active')){
           sesJqueryObject(this).removeClass('active');
           sesJqueryObject('.sesact_post_location_container').hide();
           return;
         }

         sesJqueryObject('.sesact_post_location_container').show();
         sesJqueryObject(this).addClass('active');
      });
      
      <?php if(defined('SESFEEDBGENABLED')) { ?>
        
        function hideshowfeedbgcont() {

          if(!$('feedbg_content').hasClass('sesfeedbg_feedbg_small_content')) {
            //$('feedbg_content').style.display = 'none';
            sesJqueryObject('#feedbg_content').addClass('sesfeedbg_feedbg_small_content');
            sesJqueryObject('#hideshowfeedbgcont').html('<i onclick="hideshowfeedbgcont();" class="fa fa-angle-right right_img"></i>');
          } else {
            //$('feedbg_content').style.display = 'block';
            sesJqueryObject('#feedbg_content').removeClass('sesfeedbg_feedbg_small_content');
            sesJqueryObject('#hideshowfeedbgcont').html('<i onclick="hideshowfeedbgcont();" class="fa fa-angle-left"></i>');
          }
        }
        
        function feedbgimage(feedbgid, type) {
          var feedbgidval = sesJqueryObject('#feedbgid').val();
          if(feedbgid == 'defaultimage') {
            sesJqueryObject('#activity-form').removeClass('feed_background_image');
            sesJqueryObject('.sesact_post_box').css("background-image","none");
            sesJqueryObject('#feedbgid').val(0);
            sesJqueryObject('#feedbgid_isphoto').val(0);
            sesJqueryObject('#feedbg_image_'+feedbgid).addClass('feedbg_active');
            sesJqueryObject('#activity_body').css('height','auto');
          } else {
            if(feedbgidval)
              sesJqueryObject('#feedbg_image_'+feedbgidval).removeClass('feedbg_active');
            else
              sesJqueryObject('#feedbg_image_defaultimage').removeClass('feedbg_active');
              
            if(type == 'photo') {
              var imgSource = sesJqueryObject('#feed_bg_image_'+feedbgid).attr('src');
            } else if(type == 'video') {
              var imgSource = sesJqueryObject('#feed_bg_image_'+feedbgid).attr('data-src');
              
            }
            sesJqueryObject('#activity-form').addClass('feed_background_image');
            if(type == 'photo') {
              sesJqueryObject('#sesfeedbg_videoid').remove();
              sesJqueryObject('.sesact_post_box').css("background-image","url("+ imgSource +")");
            }
            sesJqueryObject('#feedbgid').val(feedbgid);
            sesJqueryObject('#feedbg_image_'+feedbgid).addClass('feedbg_active');
            sesJqueryObject('#feedbgid_isphoto').val(1);
          }
        }
      <?php } ?>
      
      //Feeling Work
      <?php if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesfeelingactivity')) { ?>
      
          //Click on Feeling/Activity text in status box
          sesJqueryObject(document).on('click','.sesadvancedactivity_feelingsspan',function(e) {
            sesJqueryObject(this).parent().parent().trigger('click');
            return;
          });
      
          sesJqueryObject(document).on('click','#sesadvancedactivity_feelings',function(e) {

            that = sesJqueryObject(this);
            if(sesJqueryObject(this).hasClass('.seloc_clk'))
              that = sesJqueryObject('#sesadvancedactivity_feelings');
            if(sesJqueryObject(this).hasClass('active')) {
              sesJqueryObject(this).removeClass('active');
              sesJqueryObject('.sesact_post_feeling_container').hide();
              sesJqueryObject('.sesact_post_feelingcontent_container').hide();
                return;
            }
            sesJqueryObject(this).addClass('active');
            sesJqueryObject('.sesact_post_feeling_container').show();
            if(sesJqueryObject('#feelingactivityid').val() == '') {
              sesJqueryObject('.sesact_post_feelingcontent_container').show();
            }
          });

          sesJqueryObject(document).click(function(e) {
            if(sesJqueryObject(e.target).attr('id') != 'sesadvancedactivity_feelingsa' && sesJqueryObject(e.target).attr('id') != 'feeling_activity' && sesJqueryObject(e.target).attr('class') != 'sesact_feelingactivitytype'  && sesJqueryObject(e.target).attr('class') != 'sesact_feelingactivitytypea' && sesJqueryObject(e.target).attr('id') != 'showFeelingContanier' && sesJqueryObject(e.target).attr('id') != 'feelingActType' && sesJqueryObject(e.target).parent().attr('class') != 'sesact_feelingactivitytypea' && sesJqueryObject(e.target).attr('class') != 'sesadvancedactivity_feelingsspan' && sesJqueryObject(e.target).attr('class') != 'mCSB_dragger_bar' && sesJqueryObject(e.target).attr('class') != 'mCSB_dragger' && sesJqueryObject(e.target).attr('class') != 'mCSB_1_dragger_vertical') {            
              if(sesJqueryObject('#sesact_post_feeling_container').css('display') == 'table') {
                sesJqueryObject('.sesact_post_feeling_container').hide();
                sesJqueryObject('.sesact_post_feelingcontent_container').hide();
                sesJqueryObject('#feelingActType').html('');
                sesJqueryObject('#feelingActType').hide();
                sesJqueryObject('#feeling_activity').attr("placeholder", en4.core.language.translate("Choose Feeling or activity..."));
                sesJqueryObject('.sesfeelingactivity-ul').html('');
                if(sesJqueryObject('#sesadvancedactivity_feelings').hasClass('active'))
                  sesJqueryObject('#sesadvancedactivity_feelings').removeClass('active');
                if(sesJqueryObject('#feelingactivityid').val())
                  $('feelingactivityid').value = '';
                
              } 
            } else if(sesJqueryObject(e.target).attr('id') == 'feelingActType') {
              sesJqueryObject('#feelingActType').html('');
              sesJqueryObject('#feelingActType').hide();
              sesJqueryObject('#feeling_activity').attr("placeholder", en4.core.language.translate("Choose Feeling or activity..."));
              sesJqueryObject('.sesfeelingactivity-ul').html('');
              if(sesJqueryObject('#feelingactivityid').val())
                $('feelingactivityid').value = '';
              if(sesJqueryObject('#feeling_activity').val())
                $('feeling_activity').value = '';
              if(sesJqueryObject('#feelingactivityiconid').val())
                $('feelingactivityiconid').value = '';
              sesJqueryObject('.sesact_post_feelingcontent_container').show();
              sesJqueryObject('#feeling_elem_act').html('');
            }
          });
          
          sesJqueryObject(document).on('click', '.sesact_feelingactivitytype', function(e){
      
            var feelingsactivity = sesJqueryObject(this);
            var feelingId = sesJqueryObject(this).attr('data-rel');
            var feelingType = sesJqueryObject(this).attr('data-type');
            var feelingTitle = sesJqueryObject(this).attr('data-title');
            sesJqueryObject('#feelingActType').show();
            sesJqueryObject('#feelingActType').html(feelingTitle);
            sesJqueryObject('#feeling_activity').attr("placeholder", en4.core.language.translate("How are you feeling?"));
            sesJqueryObject('#feeling_activity').trigger('focus');
            document.getElementById('feelingactivityid').value = feelingId;
            document.getElementById('feelingactivity_type').value = feelingType;
            sesJqueryObject('.sesact_post_feelingcontent_container').hide();
            
            //Autocomplete Feeling trigger
            sesJqueryObject('#feeling_activity').trigger('change').trigger('keyup').trigger('keydown');
            
            //Feed Background Image Work
            if($('feedbgid') && document.getElementById('feelingactivity_type').value == 2) {
              $('hideshowfeedbgcont').style.display = 'none';
              sesJqueryObject('#feedbgid_isphoto').val(0);
              sesJqueryObject('.sesact_post_box').css('background-image', 'none');
              sesJqueryObject('#activity-form').removeClass('feed_background_image');
              sesJqueryObject('#feedbg_content').css('display','none');
            }
          });
          
          
          //Autosuggest Feeling Work
          sesJqueryObject(document).ready(function() {
            sesJqueryObject("#feeling_activity").keyup(function() {
              var search_string = sesJqueryObject("#feeling_activity").val();
              if(search_string == '') {
                search_string = 'default';
              }

              var autocompleteFeeling;
              postdata = {
                'text' : search_string, 
                'feeling_id': document.getElementById('feelingactivityid').value,
                'feeling_type': document.getElementById('feelingactivity_type').value,
              }
              
              if (autocompleteFeeling) {
                autocompleteFeeling.abort();
              }
              
              autocompleteFeeling = sesJqueryObject.post("<?php echo $this->url(array('module' => 'sesfeelingactivity', 'controller' => 'index', 'action' => 'getfeelingicons'), 'default', true) ?>",postdata,function(data) {
                var parseJson = JSON.parse( data );
                if(parseJson.status == 1 && parseJson.html) {
                  sesJqueryObject('.sesact_post_feelingautocompleter_container').show();
                  sesJqueryObject("#showSearchResults").html(parseJson.html);
                } else {
                
                  if(sesJqueryObject('#feeling_activity').val()) {
                    sesJqueryObject('.sesact_post_feelingautocompleter_container').show();

                    var html = '<li data-title="'+sesJqueryObject('#feeling_activity').val()+'" class="sesact_feelingactivitytypeli sesbasic_clearfix" data-rel=""><a href="javascript:void(0);" class="sesact_feelingactivitytypea"><img class="sesfeeling_feeling_icon" title="'+sesJqueryObject('#feeling_activity').val()+'" src="'+sesJqueryObject('#sesactfeelingactivitytypeimg_'+sesJqueryObject('#feelingactivityid').val()).attr('src')+'"><span>'+sesJqueryObject('#feeling_activity').val()+'</span></a></li>';
                    sesJqueryObject("#showSearchResults").html(html);
                  } else {
                    sesJqueryObject('.sesact_post_feelingautocompleter_container').show();
                    sesJqueryObject("#showSearchResults").html(html);
                  }
                }
              });
            });
          });

          sesJqueryObject(document).on('click', '.sesact_feelingactivitytypeli', function(e) {

            $('feelingactivityiconid').value = sesJqueryObject(this).attr('data-rel');
            $('feelingactivity_resource_type').value = sesJqueryObject(this).attr('data-type');
            
            if(!sesJqueryObject(this).attr('data-rel')) {
              $('feelingactivity_custom').value = 1;
              $('feelingactivity_customtext').value = sesJqueryObject('#feeling_activity').val();
            }

            if(sesJqueryObject(this).attr('data-icon')) {
              var finalFeeling = '-- ' + '<img class="sesfeeling_feeling_icon" title="'+sesJqueryObject(this).attr('data-title').toLowerCase()+'" src="'+sesJqueryObject(this).attr('data-icon')+'"><span>' + ' ' +  sesJqueryObject('#feelingActType').html().toLowerCase() + ' ' + '<a href="javascript:;" id="showFeelingContanier" class="" onclick="showFeelingContanier()">'+sesJqueryObject(this).attr('data-title').toLowerCase()+'</a>';
            } else {
              var finalFeeling = '-- ' + '<img class="sesfeeling_feeling_icon" title="'+sesJqueryObject(this).attr('data-title').toLowerCase()+'" src="'+sesJqueryObject(this).find('a').find('img').attr('src')+'"><span>' + ' ' +  sesJqueryObject('#feelingActType').html().toLowerCase() + ' ' + '<a href="javascript:;" id="showFeelingContanier" class="" onclick="showFeelingContanier()">'+sesJqueryObject(this).attr('data-title').toLowerCase()+'</a>';
            }
            
            sesJqueryObject('#sesact_post_tags_sesadv').css('display', 'block');
            sesJqueryObject('#feeling_activity').val(sesJqueryObject(this).attr('data-title').toLowerCase());
            sesJqueryObject('#feeling_elem_act').show();
            sesJqueryObject('#feeling_elem_act').html(finalFeeling);
            if(!sespageContentSelected)
            sesJqueryObject('#dash_elem_act').hide();
            sesJqueryObject('#sesact_post_feeling_container').hide();
          });
          //Autosuggest Feeling Work

            sesJqueryObject(document).on('click', '#feeling_activity', function(e) {

              if(sesJqueryObject('#feelingactivityid').val() == '')
                sesJqueryObject('.sesact_post_feelingcontent_container').show();
            });
            
            sesJqueryObject(document).on('keyup', '#feeling_activity', function(e) {
            
              socialShareSearch();

              if(!sesJqueryObject('#feeling_activity').val()) {
                if (e.which == 8) {
                  sesJqueryObject('#feelingActType').html('');
                  sesJqueryObject('#feelingActType').hide();
                  sesJqueryObject('.sesfeelingactivity-ul').html('');
                  if(sesJqueryObject('#feelingactivityid').val())
                    $('feelingactivityid').value = '';
                  if(sesJqueryObject('#feelingactivityid').val() == '')
                    sesJqueryObject('.sesact_post_feelingcontent_container').show();
                  
                  var toValueSESFeedbg = sesJqueryObject('#toValues').val();
                  if(sesFeedBgEnabled && (toValueSESFeedbg.length == 0 && !sesJqueryObject('#feelingactivityid').val()) && !sespageContentSelected) {
                    sesJqueryObject('#sesact_post_tags_sesadv').css('display', 'none');
                  }
                  
                  //Feed Background Image Work
                  if($('feedbgid') && document.getElementById('feelingactivity_type').value == 2) {
                    var feedbgid = sesJqueryObject('#feedbgid').val();
                    $('hideshowfeedbgcont').style.display = 'block';
                    sesJqueryObject('#feedbg_content').css('display','block');
                    var feedagainsrcurl = sesJqueryObject('#feed_bg_image_'+feedbgid).attr('src');
                    sesJqueryObject('.sesact_post_box').css("background-image","url("+ feedagainsrcurl +")");
                    sesJqueryObject('#feedbgid_isphoto').val(1);
                    sesJqueryObject('#feedbg_main_continer').css('display','block');
                    if(feedbgid) {
                      sesJqueryObject('#activity-form').addClass('feed_background_image');
                    }
                  }
                }
              }
            });
            
            //static search function
            function socialShareSearch() {

              // Declare variables
              var socialtitlesearch, socialtitlesearchfilter, allsocialshare_lists, allsocialshare_lists_li, allsocialshare_lists_p, i;
              
              socialtitlesearch = document.getElementById('feeling_activity');
              socialtitlesearchfilter = socialtitlesearch.value.toUpperCase();
              allsocialshare_lists = document.getElementById("all_feelings");
              allsocialshare_lists_li = allsocialshare_lists.getElementsByTagName('li');

              // Loop through all list items, and hide those who don't match the search query
              for (i = 0; i < allsocialshare_lists_li.length; i++) {
              
                allsocialshare_lists_a = allsocialshare_lists_li[i].getElementsByTagName("a")[0];


                if (allsocialshare_lists_a.innerHTML.toUpperCase().indexOf(socialtitlesearchfilter) > -1) {
                    allsocialshare_lists_li[i].style.display = "";
                } else {
                  //  allsocialshare_lists_li[i].style.display = "none";
                }
              }
            }
            
            sesJqueryObject(document).ready(function() {
              sesJqueryObject('#feeling_activity').keyup(function(e) {
                if (e.which == 8) {
                  $('feelingactivityiconid').value = '';
                  $('feelingactivity_custom').value = '';
                  $('feelingactivity_customtext').value = '';
                  sesJqueryObject('#feeling_elem_act').html('');
                  //sesJqueryObject('#feeling_activity').attr("placeholder", "Choose Feeling or activity...");
                }
              });
            });

            function showFeelingContanier() {
            
              if($('sesact_post_feeling_container').style.display == 'table') {
                $('showFeelingContanier').removeClass('active');
                sesJqueryObject('#sesact_post_feeling_container').hide();
              } else {
                $('showFeelingContanier').addClass('active');
                sesJqueryObject('#feeling_activity_remove_act').show();
                sesJqueryObject('#sesact_post_feeling_container').show();
              }
            } 
            
            function feelingactivityremoveact() {
              sesJqueryObject('#feeling_activity_remove_act').hide();
              sesJqueryObject('#feelingActType').html('');
              sesJqueryObject('#feelingActType').hide();
              sesJqueryObject('.sesfeelingactivity-ul').html('');
              if(sesJqueryObject('#feelingactivityid').val())
                $('feelingactivityid').value = '';
              sesJqueryObject('#feeling_activity').val('');
              $('feelingactivityiconid').value = '';
              sesJqueryObject('#feeling_elem_act').html('');
              //Feed Background Image Work
              if($('feedbgid') && document.getElementById('feelingactivity_type').value == 2) {
                var feedbgid = sesJqueryObject('#feedbgid').val();
                $('hideshowfeedbgcont').style.display = 'block';
                sesJqueryObject('#feedbg_content').css('display','block');
                var feedagainsrcurl = sesJqueryObject('#feed_bg_image_'+feedbgid).attr('src');
                sesJqueryObject('.sesact_post_box').css("background-image","url("+ feedagainsrcurl +")");
                sesJqueryObject('#feedbgid_isphoto').val(1);
                sesJqueryObject('#feedbg_main_continer').css('display','block');
                if(feedbgid) {
                  sesJqueryObject('#activity-form').addClass('feed_background_image');
                }
              }
              var toValueSESFeedbg = sesJqueryObject('#toValues').val();
              if(sesFeedBgEnabled && (toValueSESFeedbg.length == 0 && !sesJqueryObject('#feelingactivityid').val()) && !sespageContentSelected && !sespageContentSelected) {
                sesJqueryObject('#sesact_post_tags_sesadv').css('display', 'none');
              }
            }
          //Feeling Work End
          <?php } ?>
          
          <?php if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesemoji')) { ?>
          //Feeling Emojis Work
          <?php if(!$this->advcomment) { ?>
            var feeling_requestEmoji;
            sesJqueryObject('.feeling_emoji_comment_select').click(function() {
            
              sesJqueryObject('.feeling_emoji_content').removeClass('from_bottom');
              
              var topPositionOfParentDiv =  sesJqueryObject(this).offset().top + 35;
              topPositionOfParentDiv = topPositionOfParentDiv+'px';
              if(sesadvancedactivityDesign == 2) {
                var leftSub = 55;  
              } else
                var leftSub = 264;
                
              var leftPositionOfParentDiv =  sesJqueryObject(this).offset().left - leftSub;
              leftPositionOfParentDiv = leftPositionOfParentDiv+'px';
              sesJqueryObject('._feeling_emoji_content').css('top',topPositionOfParentDiv);
              sesJqueryObject('._feeling_emoji_content').css('left',leftPositionOfParentDiv).css('z-index',99);
              sesJqueryObject('._feeling_emoji_content').show();
              var eTop = sesJqueryObject(this).offset().top; //get the offset top of the element
              var availableSpace = sesJqueryObject(document).height() - eTop;
              if(availableSpace < 400){
                  sesJqueryObject('.feeling_emoji_content').addClass('from_bottom');
              }
                if(sesJqueryObject(this).hasClass('active')){
                  sesJqueryObject(this).removeClass('active');
                  sesJqueryObject('.feeling_emoji_content').hide();
                  return false;
                }
                  sesJqueryObject(this).addClass('active');
                  sesJqueryObject('.feeling_emoji_content').show();
                  if(sesJqueryObject(this).hasClass('complete'))
                    return false;
                  if(typeof feeling_requestEmoji != 'undefined')
                    feeling_requestEmoji.cancel();
                  var that = this;
                  var url = '<?php echo $this->url(array('module' => 'sesemoji', 'controller' => 'index', 'action' => 'feelingemoji'), 'default', true) ?>';
                  feeling_requestEmoji = new Request.HTML({
                    url : url,
                    data : {
                      format : 'html',
                    },
                    evalScripts : true,
                    onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript) {
                      sesJqueryObject('.ses_feeling_emoji_holder').html(responseHTML);
                      sesJqueryObject(that).addClass('complete');
                      sesJqueryObject('.feeling_emoji_content').show();
                      jqueryObjectOfSes(".sesbasic_custom_scroll").mCustomScrollbar({
                        theme:"minimal-dark"
                      });
                    }
                  });
                feeling_requestEmoji.send();
            });
          <?php } ?>
        <?php } ?>
        
        <?php //GIF Work ?>
        <?php if(defined('SESFEEDGIFENABLED') && !$this->advcomment) { ?>
          var requestGif;
          sesJqueryObject('.gif_comment_select').click(function() {
            clickGifContentContainer = this;
            sesJqueryObject('.gif_content').removeClass('from_bottom');
            var topPositionOfParentDiv =  sesJqueryObject(this).offset().top + 35;
            topPositionOfParentDiv = topPositionOfParentDiv+'px';
            if(sesadvancedactivityDesign == 2){
              var leftSub = 55;  
            } else
              var leftSub = 264;
              
            var leftPositionOfParentDiv =  sesJqueryObject(this).offset().left - leftSub;
            leftPositionOfParentDiv = leftPositionOfParentDiv+'px';
            sesJqueryObject('._gif_content').css('top',topPositionOfParentDiv);
            sesJqueryObject('._gif_content').css('left',leftPositionOfParentDiv).css('z-index',99);
            sesJqueryObject('._gif_content').show();
            var eTop = sesJqueryObject(this).offset().top; //get the offset top of the element
            var availableSpace = sesJqueryObject(document).height() - eTop;
            if(availableSpace < 400){
              sesJqueryObject('.gif_content').addClass('from_bottom');
            }
            
            if(sesJqueryObject(this).hasClass('active')) {
              sesJqueryObject(this).removeClass('active');
              sesJqueryObject('.gif_content').hide();
              return false;
            }
            sesJqueryObject(this).addClass('active');
            sesJqueryObject('.gif_content').show();
            
            if(sesJqueryObject(this).hasClass('complete'))
              return false;
             if(typeof requestGif != 'undefined')
              requestGif.cancel();
             var that = this;
             var url = '<?php echo $this->url(array('module' => 'sesfeedgif', 'controller' => 'index', 'action' => 'gif'), 'default', true) ?>';
             requestGif = new Request.HTML({
              url : url,
              data : {
                format : 'html',
              },
              evalScripts : true,
              onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript) {
                sesJqueryObject('.gif_content').find('.ses_gif_container_inner').find('.ses_gif_holder').html(responseHTML);
                sesJqueryObject(that).addClass('complete');
                sesJqueryObject('._gif_content').show();
                jqueryObjectOfSes(".sesbasic_custom_scroll").mCustomScrollbar({
                  theme:"minimal-dark"
                });
                
              }
            });
            requestGif.send();
          });

          var clickGifContentContainer;
          function activityGifFeedAttachment(that){
            var code = sesJqueryObject(that).parent().parent().attr('rel');
            var image = sesJqueryObject(that).attr('src');
            composeInstance.plugins.each(function(plugin) {
              plugin.deactivate();
              sesJqueryObject('#compose-'+plugin.getName()+'-activator').parent().removeClass('active');
            });
            sesJqueryObject('#fancyalbumuploadfileids').val('');
            sesJqueryObject('.fileupload-cnt').html('');
            composeInstance.getTray().empty();
            sesJqueryObject('#compose-tray').show();
            sesJqueryObject('#compose-tray').html('<div class="sesact_composer_gif"><img src="'+image+'"><a class="remove_gif_image_feed notclose fas fa-times" href="javascript:;"></a></div>');
            sesJqueryObject('#image_id').val(code);
            sesJqueryObject('.gif_content').hide();  
            sesJqueryObject('.gif_comment_select').removeClass('active');
            
            //Feed Background Image Work
            if($('feedbgid') && sesJqueryObject('#image_id').val()) {
              $('hideshowfeedbgcont').style.display = 'none';
              sesJqueryObject('#feedbgid_isphoto').val(0);
              sesJqueryObject('.sesact_post_box').css('background-image', 'none');
              sesJqueryObject('#activity-form').removeClass('feed_background_image');
              sesJqueryObject('#feedbg_content').css('display','none');
            }
          }
          sesJqueryObject(document).on('click','._sesadvgif_gif > img',function(e) {
            if(sesJqueryObject(clickGifContentContainer).hasClass('activity_gif_content_a')){
              activityGifFeedAttachment(this);  
            }else
              commentGifContainerSelect(this);
            sesJqueryObject('.exit_gif_btn').trigger('click');
          });
          
          function commentGifContainerSelect(that){
            var code = sesJqueryObject(that).parent().parent().attr('rel');
            var elem = sesJqueryObject(clickGifContentContainer).parent();
            var elemInput = elem.parent().find('span').eq(0).find('.select_gif_id') .val(code);
            elem.closest('form').trigger('submit');  
          }
          /*ACTIVITY FEED*/
          sesJqueryObject(document).on('click','.remove_gif_image_feed',function(){
            composeInstance.getTray().empty();
            sesJqueryObject('#image_id').val('');
            sesJqueryObject('#compose-tray').hide();
            
            //Feed Background Image Work
            if($('feedbgid') && sesJqueryObject('#image_id').val() == '') {
              var feedbgid = sesJqueryObject('#feedbgid').val();
              $('hideshowfeedbgcont').style.display = 'block';
              sesJqueryObject('#feedbg_content').css('display','block');
              var feedagainsrcurl = sesJqueryObject('#feed_bg_image_'+feedbgid).attr('src');
              sesJqueryObject('.sesact_post_box').css("background-image","url("+ feedagainsrcurl +")");
              sesJqueryObject('#feedbgid_isphoto').val(1);
              sesJqueryObject('#feedbg_main_continer').css('display','block');
              if(feedbgid) {
                sesJqueryObject('#activity-form').addClass('feed_background_image');
              }
            }
          });
          var gifsearchAdvReq;

          var canPaginatePageNumber = 1;
          sesJqueryObject(document).on('keyup change','.search_sesgif',function(){
            var value = sesJqueryObject(this).val();
            if(!value){
              sesJqueryObject('.main_search_category_srn').show();
              sesJqueryObject('.main_search_cnt_srn').hide();
              return;
            }
            sesJqueryObject('.main_search_category_srn').hide();
            sesJqueryObject('.main_search_cnt_srn').show();
            if(typeof gifsearchAdvReq != 'undefined') {
              gifsearchAdvReq.cancel();
              isGifRequestSend = false;
            }
            document.getElementById('main_search_cnt_srn').innerHTML = '<div class="sesgifsearch sesbasic_loading_container" style="height:100%;"></div>';
            canPaginatePageNumber = 1;
            searchGifContent();
          });

          var isGifRequestSend = false;
          function searchGifContent(valuepaginate, searchscroll) {
            
            var value = '';
            var search_sesgif = sesJqueryObject('.search_sesgif').val();
            
            if(isGifRequestSend == true)
              return;
              
              //console.log(searchscroll);
            
            if(typeof valuepaginate != 'undefined') {
              value = 1;
              document.getElementById('main_search_cnt_srn').innerHTML = document.getElementById('main_search_cnt_srn').innerHTML;
            }
            
            isGifRequestSend = true;
            gifsearchAdvReq = (new Request.HTML({
              method: 'post',
              'url': en4.core.baseUrl + "sesfeedgif/index/search-gif/",
              'data': {
                format: 'html',
                  text: search_sesgif,
                  page: canPaginatePageNumber,
                  is_ajax: 1,
                  searchvalue: value,
              },
              onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
                
                sesJqueryObject('.sesgifsearch').remove();
                sesJqueryObject('.sesgifsearchpaginate').remove();

                if(sesJqueryObject('.sesfeedgif_search_results').length == 0) {
                  sesJqueryObject('#main_search_cnt_srn').append(responseHTML);
                } else {
                  sesJqueryObject('.sesfeedgif_search_results').append(responseHTML);
                }
                jqueryObjectOfSes(".sesbasic_custom_scroll").mCustomScrollbar({
                  theme:"minimal-dark",
                  callbacks:{
                      whileScrolling:function() {
                        if(this.mcs.topPct == 90 && canPaginateExistingPhotos == '1' &&   sesJqueryObject('.sesgifsearchpaginate').length == 0) {
                        sesJqueryObject('.sesbasic_loading_container').css('position','absolute').css('width','100%').css('bottom','5px');
                          searchGifContent(1, 'searchscroll');
                        }
                      },
                  }
                });
            
//                 sesJqueryObject('.main_search_cnt_srn').slimscroll({
//                   height: 'auto',
//                   alwaysVisible :true,
//                   color :'#000',
//                   railOpacity :'0.5',
//                   disableFadeOut :true,
//                 });
// 
//                 sesJqueryObject('.main_search_cnt_srn').slimscroll().bind('slimscroll', function(event, pos) {
//                   if(canPaginateExistingPhotos == '1' && pos == 'bottom' && sesJqueryObject('.sesgifsearchpaginate').length == 0) {
//                     sesJqueryObject('.sesbasic_loading_container').css('position','absolute').css('width','100%').css('bottom','5px');
//                     searchGifContent(1);
//                   }
//                 });
                isGifRequestSend = false;
              }
            })).send();
          }
        <?php } ?>
        <?php //GIF Work ?>

      <?php if(!$this->advcomment){ ?>
      var requestEmoji;
      sesJqueryObject('.emoji_comment_select').click(function(){
        sesJqueryObject('.emoji_content').removeClass('from_bottom');
        var topPositionOfParentDiv =  sesJqueryObject(this).offset().top + 35;
        topPositionOfParentDiv = topPositionOfParentDiv+'px';
        if(sesadvancedactivityDesign == 2){
          var leftSub = 55;  
        }else
          var leftSub = 264;
        var leftPositionOfParentDiv =  sesJqueryObject(this).offset().left - leftSub;
        leftPositionOfParentDiv = leftPositionOfParentDiv+'px';
        sesJqueryObject('._emoji_content').css('top',topPositionOfParentDiv);
        sesJqueryObject('._emoji_content').css('left',leftPositionOfParentDiv).css('z-index',99);
        sesJqueryObject('._emoji_content').show();
        var eTop = sesJqueryObject(this).offset().top; //get the offset top of the element
        var availableSpace = sesJqueryObject(document).height() - eTop;
        if(availableSpace < 400){
            sesJqueryObject('.emoji_content').addClass('from_bottom');
        }
          if(sesJqueryObject(this).hasClass('active')){
            sesJqueryObject(this).removeClass('active');
            sesJqueryObject('.emoji_content').hide();
            return false;
           }
            sesJqueryObject(this).addClass('active');
            sesJqueryObject('.emoji_content').show();
            if(sesJqueryObject(this).hasClass('complete'))
              return false;
             if(typeof requestEmoji != 'undefined')
              requestEmoji.cancel();
             var that = this;
             var url = '<?php echo $this->url(array('module' => 'sesadvancedactivity', 'controller' => 'ajax', 'action' => 'emoji'), 'default', true) ?>';
             requestEmoji = new Request.HTML({
              url : url,
              data : {
                format : 'html',
              },
              evalScripts : true,
              onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript) {
                sesJqueryObject('.ses_emoji_holder').html(responseHTML);
                sesJqueryObject(that).addClass('complete');
                sesJqueryObject('.emoji_content').show();
                jqueryObjectOfSes(".sesbasic_custom_scroll").mCustomScrollbar({
									theme:"minimal-dark"
								});
              }
            });
           requestEmoji.send();
      });
      //emoji select in comment
      sesJqueryObject(document).click(function(e){
        if(sesJqueryObject(e.target).attr('id') == 'sesadvancedactivityemoji-edit-a')
          return;
        var container = sesJqueryObject('.ses_emoji_container');
        if ((!container.is(e.target) && container.has(e.target).length === 0)){
           sesJqueryObject('.ses_emoji_container').parent().find('a').removeClass('active');
           sesJqueryObject('.ses_emoji_container').hide();
        }
      });
      <?php } ?>
    </script>
<script type="text/javascript">
sesJqueryObject('#discard_post').click(function(){
hideStatusBoxSecond();
sesJqueryObject('.sesact_confirmation_popup_overlay').hide();
sesJqueryObject('.sesact_confirmation_popup').hide();
sesJqueryObject('.sesact_post_media_options').removeClass('_sesadv_composer_active');
});
sesJqueryObject('#goto_post').click(function(){
sesJqueryObject('.sesact_confirmation_popup').hide();  
sesJqueryObject('.sesact_confirmation_popup_overlay').hide();
});
<?php if($this->allowprivacysetting){ ?>
//set default privacy of logged-in user
sesJqueryObject(document).ready(function(e){
var privacy = sesJqueryObject('#privacy').val();
if(privacy){
if(privacy == 'everyone')
  sesJqueryObject('.adv_privacy_optn >li[data-src="everyone"]').find('a').trigger('click');  
else if(privacy == 'networks')
  sesJqueryObject('.adv_privacy_optn >li[data-src="networks"]').find('a').trigger('click'); 
else if(privacy == 'friends')
  sesJqueryObject('.adv_privacy_optn >li[data-src="friends"]').find('a').trigger('click'); 
else if(privacy == 'onlyme')
  sesJqueryObject('.adv_privacy_optn >li[data-src="onlyme"]').find('a').trigger('click'); 
else if(privacy && privacy.indexOf('network_list_') > -1){
  var exploidV =  privacy.split(',');
  for(i=0;i<exploidV.length;i++){
     var id = exploidV[i].replace('network_list_','');
     sesJqueryObject('.sesadv_network[data-rel="'+id+'"]').addClass('active');
  }
 sesJqueryObject('#adv_pri_option').html("<?php echo $this->translate('Multiple Networks'); ?>");
 sesJqueryObject('.sesact_privacy_btn').attr('title',"<?php echo $this->translate('Multiple Networks'); ?>");;
 sesJqueryObject('#sesadv_privacy_icon').removeAttr('class').addClass('sesact_network');
}else if(privacy && privacy.indexOf('member_list_') > -1){
  var exploidV =  privacy.split(',');
  for(i=0;i<exploidV.length;i++){
     var id = exploidV[i].replace('member_list_','');
     sesJqueryObject('.sesadv_list[data-rel="'+id+'"]').addClass('active');
  }
  sesJqueryObject('#adv_pri_option').html('Multiple Lists');
 sesJqueryObject('.sesact_privacy_btn').attr('title','Multiple Lists');
 sesJqueryObject('#sesadv_privacy_icon').removeAttr('class').addClass('sesact_list');
}
}
privacySetAct = true;
});
<?php  }else{ ?>
var privacySetAct = true;
<?php } ?>
sesJqueryObject(document).on('click','.adv_privacy_optn li a',function(e){
e.preventDefault();
if(!sesJqueryObject(this).parent().hasClass('multiple')){
sesJqueryObject('.adv_privacy_optn > li').removeClass('active');
var text = sesJqueryObject(this).text();
<?php if(!$this->subject() || $this->subject() && ($this->subject()->getType() != "businesses" || $this->subject()->getType() != "sespage_page" || $this->subject()->getType() != "sesgroup_group" || $this->subject()->getType() != "stores")){ ?>
sesJqueryObject('.sesact_privacy_btn').attr('title',text);;
<?php } ?>
sesJqueryObject(this).parent().addClass('active');
sesJqueryObject('#adv_pri_option').html(text);
sesJqueryObject('#sesadv_privacy_icon').remove();
sesJqueryObject('<i id="sesadv_privacy_icon" class="'+sesJqueryObject(this).find('i').attr('class')+'"></i>').insertBefore('#adv_pri_option');

if(sesJqueryObject(this).parent().hasClass('sesadv_network'))
  sesJqueryObject('#privacy').val(sesJqueryObject(this).parent().attr('data-src')+'_'+sesJqueryObject(this).parent().attr('data-rel'));
else if(sesJqueryObject(this).parent().hasClass('sesadv_list'))
  sesJqueryObject('#privacy').val(sesJqueryObject(this).parent().attr('data-src')+'_'+sesJqueryObject(this).parent().attr('data-rel'));
else
sesJqueryObject('#privacy').val(sesJqueryObject(this).parent().attr('data-src'));
}
sesJqueryObject('.sesact_privacy_btn').parent().removeClass('sesact_pulldown_active');
});

sesJqueryObject(document).on('click','.mutiselect',function(e){
if(sesJqueryObject(this).attr('data-rel') == 'network-multi')
var elem = 'sesadv_network';
else
var elem = 'sesadv_list';
var elemens = sesJqueryObject('.'+elem);
var html = '';
for(i=0;i<elemens.length;i++){
html += '<li><input class="checkbox" type="checkbox" value="'+sesJqueryObject(elemens[i]).attr('data-rel')+'">'+sesJqueryObject(elemens[i]).text()+'</li>';
}
en4.core.showError('<form id="'+elem+'_select" class="_privacyselectpopup"><p>Please select network to display post</p><ul class="sesbasic_clearfix">'+html+'</ul><div class="_privacyselectpopup_btns sesbasic_clearfix"><button type="submit">Save</button><button class="close" onclick="Smoothbox.close();return false;">Close</button></div></form>');
sesJqueryObject ('._privacyselectpopup').parent().parent().addClass('_privacyselectpopup_wrapper');
//pre populate
var valueElem = sesJqueryObject('#privacy').val();
if(valueElem && valueElem.indexOf('network_list_') > -1 && elem == 'sesadv_network'){
var exploidV =  valueElem.split(',');
for(i=0;i<exploidV.length;i++){
   var id = exploidV[i].replace('network_list_','');
   sesJqueryObject('.checkbox[value="'+id+'"]').prop('checked', true);
}
}else if(valueElem && valueElem.indexOf('member_list_') > -1 && elem == 'sesadv_list'){
var exploidV =  valueElem.split(',');
for(i=0;i<exploidV.length;i++){
   var id = exploidV[i].replace('member_list_','');
   sesJqueryObject('.checkbox[value="'+id+'"]').prop('checked', true);
}
}
});
sesJqueryObject(document).on('submit','#sesadv_list_select',function(e){
e.preventDefault();
var isChecked = false;
var sesadv_list_select = sesJqueryObject('#sesadv_list_select').find('[type="checkbox"]');
var valueL = '';
for(i=0;i<sesadv_list_select.length;i++){
if(!isChecked)
  sesJqueryObject('.adv_privacy_optn > li').removeClass('active');
if(sesJqueryObject(sesadv_list_select[i]).is(':checked')){
  isChecked = true;
  var el = sesJqueryObject(sesadv_list_select[i]).val();
  sesJqueryObject('.lists[data-rel="'+el+'"]').addClass('active');
  valueL = valueL+'member_list_'+el+',';
}
}
if(isChecked){
 sesJqueryObject('#privacy').val(valueL);
 sesJqueryObject('#adv_pri_option').html("<?php echo $this->translate('Multiple Lists'); ?>");
 sesJqueryObject('.sesact_privacy_btn').attr('title',"<?php echo $this->translate('Multiple Lists'); ?>");;
sesJqueryObject(this).find('.close').trigger('click');
}
sesJqueryObject('#sesadv_privacy_icon').removeAttr('class').addClass('sesact_list');
});
sesJqueryObject(document).on('submit','#sesadv_network_select',function(e){
e.preventDefault();
var isChecked = false;
var sesadv_network_select = sesJqueryObject('#sesadv_network_select').find('[type="checkbox"]');
var valueL = '';
for(i=0;i<sesadv_network_select.length;i++){
if(!isChecked)
  sesJqueryObject('.adv_privacy_optn > li').removeClass('active');
if(sesJqueryObject(sesadv_network_select[i]).is(':checked')){
  isChecked = true;
  var el = sesJqueryObject(sesadv_network_select[i]).val();
  sesJqueryObject('.network[data-rel="'+el+'"]').addClass('active');
  valueL = valueL+'network_list_'+el+',';
}
}
if(isChecked){
 sesJqueryObject('#privacy').val(valueL);
 sesJqueryObject('#adv_pri_option').html('Multiple Network');
 sesJqueryObject('.sesact_privacy_btn').attr('title','Multiple Network');;
sesJqueryObject(this).find('.close').trigger('click');
}
sesJqueryObject('#sesadv_privacy_icon').removeAttr('class').addClass('sesact_network');
});
<?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 1)) { ?>
var input = document.getElementById('tag_location');
if(input){
var autocomplete = new google.maps.places.Autocomplete(input);
  google.maps.event.addListener(autocomplete, 'place_changed', function () {
    var place = autocomplete.getPlace();
    if (!place.geometry) {
      return;
    }
    sesJqueryObject('#locValues-element').html('<span class="tag">'+sesJqueryObject('#tag_location').val()+' <a href="javascript:void(0);" class="loc_remove_act notclose">x</a></span>');
    sesJqueryObject('#dash_elem_act').show();
    sesJqueryObject('#location_elem_act').show();
    sesJqueryObject('#location_elem_act').html('at <a href="javascript:;" class="seloc_clk">'+sesJqueryObject('#tag_location').val()+'</a>');
    sesJqueryObject('#tag_location').hide();
    document.getElementById('activitylng').value = place.geometry.location.lng();
    document.getElementById('activitylat').value = place.geometry.location.lat();
  
    //Feed Background Image Work
    if($('feedbgid')) {
      sesJqueryObject('#sesact_post_tags_sesadv').css('display', 'block');
      sesJqueryObject('#feedbgid_isphoto').val(0);
      sesJqueryObject('.sesact_post_box').css('background-image', 'none');
      sesJqueryObject('#activity-form').removeClass('feed_background_image');
      sesJqueryObject('#feedbg_main_continer').css('display','none');
    }
});
}
<?php } ?>
sesJqueryObject(document).on('click','.loc_remove_act',function(e){
sesJqueryObject('#activitylng').val('');
sesJqueryObject('#activitylat').val('');
sesJqueryObject('#tag_location').val('');
sesJqueryObject('#locValues-element').html('');
sesJqueryObject('#tag_location').show();
sesJqueryObject('#location_elem_act').hide();
if(!sesJqueryObject('#toValues-element').children().length && !sespageContentSelected)
   sesJqueryObject('#dash_elem_act').hide();
   
var feedbgid = sesJqueryObject('#feedbgid').val();
var feedagainsrcurl = sesJqueryObject('#feed_bg_image_'+feedbgid).attr('src');
sesJqueryObject('.sesact_post_box').css("background-image","url("+ feedagainsrcurl +")");
sesJqueryObject('#feedbgid_isphoto').val(1);
sesJqueryObject('#feedbg_main_continer').css('display','block');
if(feedbgid) {
  sesJqueryObject('#activity-form').addClass('feed_background_image');
}
if(feedbgid == 0) {
  sesJqueryObject('#activity-form').removeClass('feed_background_image');
}
})    

// Populate data
var maxRecipients = 50;
var to = {
id : false,
type : false,
guid : false,
title : false
};

function removeFromToValue(id) {    
//check for edit form
if(sesJqueryObject('#sessmoothbox_main').length){
  removeFromToValueEdit(id);
  return;
}
  
// code to change the values in the hidden field to have updated values
// when recipients are removed.
var toValues = $('toValues').value;
var toValueArray = toValues.split(",");
var toValueIndex = "";

var checkMulti = id.search(/,/);

// check if we are removing multiple recipients
if (checkMulti!=-1){
  var recipientsArray = id.split(",");
  for (var i = 0; i < recipientsArray.length; i++){
    removeToValue(recipientsArray[i], toValueArray);
  }
}
else{
  removeToValue(id, toValueArray);
}
$('tag_friends_input').disabled = false;
var firstElem = sesJqueryObject('#toValues-element > span').eq(0).text();
var countElem = sesJqueryObject('#toValues-element').children().length;
var html = '';

if(!firstElem.trim()){
  sesJqueryObject('#tag_friend_cnt').html('');
  sesJqueryObject('#tag_friend_cnt').hide();
  if(!sesJqueryObject('#tag_location').val() && !sespageContentSelected)
  sesJqueryObject('#dash_elem_act').hide();
  return;
}else if(countElem == 1){
  html = '<a href="javascript:;" class="sestag_clk">'+firstElem.replace('x','')+'</a>';
}else if(countElem > 2){
  html = '<a href="javascript:;" class="sestag_clk">'+firstElem.replace('x','')+'</a>';
  html = html + ' and <a href="javascript:;" class="sestag_clk">'+(countElem-1)+' others</a>';
}else{
  html = '<a href="javascript:;" class="sestag_clk">'+firstElem.replace('x','')+'</a>';
  html = html + ' and <a href="javascript:;" class="sestag_clk">'+sesJqueryObject('#toValues-element > span').eq(1).text().replace('x','')+'</a>';
}
sesJqueryObject('#sesact_post_tags_sesadv').css('display', 'block');
sesJqueryObject('#tag_friend_cnt').html('with '+html);
sesJqueryObject('#tag_friend_cnt').show();
sesJqueryObject('#dash_elem_act').show();
}

function removeToValue(id, toValueArray){
for (var i = 0; i < toValueArray.length; i++){
  if (toValueArray[i]==id) toValueIndex =i;
}

toValueArray.splice(toValueIndex, 1);
$('toValues').value = toValueArray.join();

if(sesFeedBgEnabled && toValueArray.length == 0 && !sesJqueryObject('#feelingactivityid').val() && !sespageContentSelected)
  sesJqueryObject('#sesact_post_tags_sesadv').css('display', 'none');
}

en4.core.runonce.add(function() {
  
  new Autocompleter.Request.JSON('tag_friends_input', '<?php echo $this->url(array('module' => 'sesadvancedactivity', 'controller' => 'index', 'action' => 'suggest'), 'default', true) ?>', {
    'minLength': 1,
    'delay' : 250,
    'selectMode': 'pick',
    'autocompleteType': 'message',
    'multiple': false,
    'className': 'sesadvactivity_autosuggest',
    'filterSubset' : true,
    'tokenFormat' : 'object',
    'tokenValueKey' : 'label',
    'cache': false,
    'injectChoice': function(token){
      if(token.type == 'user'){
        var choice = new Element('li', {
          'class': 'autocompleter-choices',
          'html': token.photo,
          'id':token.label
        });
        new Element('div', {
          'html': this.markQueryValue(token.label),
          'class': 'autocompleter-choice'
        }).inject(choice);
        this.addChoiceEvents(choice).inject(this.choices);
        choice.store('autocompleteChoice', token);
      }
      else {
        var choice = new Element('li', {
          'class': 'autocompleter-choices friendlist',
          'id':token.label
        });
        new Element('div', {
          'html': this.markQueryValue(token.label),
          'class': 'autocompleter-choice'
        }).inject(choice);
        this.addChoiceEvents(choice).inject(this.choices);
        choice.store('autocompleteChoice', token);
      }
        
    },
    onPush : function(choice){
      if( $('toValues').value.split(',').length >= maxRecipients ){
        $('tag_friends_input').disabled = true;
      }
      var firstElem = sesJqueryObject('#toValues-element > span').eq(0).text();
      var countElem = sesJqueryObject('#toValues-element  > span').children().length;
      var html = '';
      if(countElem == 1){
        html = '<a href="javascript:;" class="sestag_clk">'+firstElem.replace('x','')+'</a>';
      }else if(countElem > 2){
        html = '<a href="javascript:;" class="sestag_clk">'+firstElem.replace('x','')+'</a>';
        html = html + ' and <a href="javascript:;"  class="sestag_clk">'+(countElem-1)+' others</a>';
      }else{
        html = '<a href="javascript:;" class="sestag_clk">'+firstElem.replace('x','')+'</a>';
        html = html + ' and <a href="javascript:;" class="sestag_clk">'+sesJqueryObject('#toValues-element > span').eq(1).text().replace('x','')+'</a>';
      }
      sesJqueryObject('#sesact_post_tags_sesadv').css('display', 'block');
      sesJqueryObject('#tag_friend_cnt').html('with '+html);
      sesJqueryObject('#tag_friend_cnt').show();
      sesJqueryObject('#dash_elem_act').show();
    }
  });
  
  new Composer.OverText($('tag_friends_input'), {
    'textOverride' : '<?php echo $this->translate('') ?>',
    'element' : 'label',
    'isPlainText' : true,
    'positionOptions' : {
      position: ( en4.orientation == 'rtl' ? 'upperRight' : 'upperLeft' ),
      edge: ( en4.orientation == 'rtl' ? 'upperRight' : 'upperLeft' ),
      offset: {
        x: ( en4.orientation == 'rtl' ? -4 : 4 ),
        y: 2
      }
    }
  });

});

</script>
<script type="application/javascript">
var isMemberHomePage = <?php echo !empty($this->isMemberHomePage) ? $this->isMemberHomePage : 0; ?>;
var isOnThisDayPage = <?php echo !empty($this->isOnThisDayPage) ? $this->isOnThisDayPage : 0; ?>;
         function  preventSubmitOnSocialNetworking(){
           if(sesJqueryObject('.composer_facebook_toggle_active').length)
            sesJqueryObject('.composer_facebook_toggle').click();
           if(sesJqueryObject('.composer_twitter_toggle_active').length)
            sesJqueryObject('.composer_twitter_toggle_active').click();  
          }
          sesJqueryObject(document).on('click','.schedule_post_schedue',function(e){
           e.preventDefault();
           var value = sesJqueryObject('#scheduled_post').val();
           if(sesJqueryObject('.sesadvancedactivity_shedulepost_error').css('display') == 'block' || !value){
            return;   
           }
           sesJqueryObject('.sesadvancedactivity_shedulepost_overlay').hide();
           sesJqueryObject('.sesadvancedactivity_shedulepost_select').hide();
           sesJqueryObject('.sesadvancedactivity_shedulepost').addClass('active');
           preventSubmitOnSocialNetworking();
          });
          sesJqueryObject(document).on('click','#sesadvancedactivity_shedulepost',function(e){
           e.preventDefault();
           sesJqueryObject('.sesadvancedactivity_shedulepost_overlay').show();
           sesJqueryObject('.sesadvancedactivity_shedulepost_select').show();
           sesJqueryObject(this).addClass('active');
           makeDateTimePicker();
           sesadvtooltip();
          });
          sesJqueryObject(document).on('click','.schedule_post_close',function(e){
              e.preventDefault();
            sesJqueryObject('.sesadvancedactivity_shedulepost_overlay').hide();
            sesJqueryObject('.sesadvancedactivity_shedulepost_select').hide();
            if(sesJqueryObject('.sesadvancedactivity_shedulepost_error').css('display') == 'block')
              sesJqueryObject('.sesadvancedactivity_shedulepost_error').html('').hide();
            sesJqueryObject('#scheduled_post').val('');
             sesJqueryObject('#sesadvancedactivity_shedulepost').removeClass('active');
             sesJqueryObject('.bootstrap-datetimepicker-widget').hide();
          });
          var schedule_post_datepicker;
          function makeDateTimePicker(){
            if(sesJqueryObject('.sesadvancedactivity_shedulepost_edit_overlay').length){
              var elem = 'scheduled_post_edit';
              var datepicker = 'datetimepicker_edit';
            }else{
              var elem = 'scheduled_post';
              var datepicker  = 'datetimepicker';
            }
            //if(!sesJqueryObject('#'+elem).val()){
              var now = new Date();
              now.setMinutes(now.getMinutes() + 10);
           // }
            schedule_post_datepicker = sesJqueryObject('#'+datepicker).datetimepicker({
            format: 'dd/MM/yyyy hh:mm:ss',
            maskInput: false,           // disables the text input mask
            pickDate: true,            // disables the date picker
            pickTime: true,            // disables de time picker
            pick12HourFormat: true,   // enables the 12-hour format time picker
            pickSeconds: true,         // disables seconds in the time picker
            startDate: now,      // set a minimum date
            endDate: Infinity          // set a maximum date
          });
          schedule_post_datepicker.on('changeDate', function(e) {
            var time = e.localDate.toString();
            var timeObj = new Date(time).getTime();
            //add 10 minutes
            var now = new Date();
            now.setMinutes(now.getMinutes() + 10);
            if(sesJqueryObject('.sesadvancedactivity_shedulepost_edit_overlay').length){
              var error = 'sesadvancedactivity_shedulepost_edit_error';
            }else{
              var error = 'sesadvancedactivity_shedulepost_error';
            }
            if(timeObj < now.getTime()){
              sesJqueryObject('.'+error).html("<?php echo $this->translate('choose time 10 minutes greater than current time.'); ?>").show();
              return false;
            }else{
             sesJqueryObject('.'+error).html('').hide();
            }
          });  
          }
          </script>      
      <script type="application/javascript">
         function  preventSubmitOnSocialNetworking(){
           if(sesJqueryObject('.composer_facebook_toggle_active').length)
            sesJqueryObject('.composer_facebook_toggle').click();
           if(sesJqueryObject('.composer_twitter_toggle_active').length)
            sesJqueryObject('.composer_twitter_toggle_active').click();  
          }
          sesJqueryObject(document).on('click','.schedule_post_schedue',function(e){
           e.preventDefault();
           var value = sesJqueryObject('#scheduled_post').val();
           if(sesJqueryObject('.sesadvancedactivity_shedulepost_error').css('display') == 'block' || !value){
            return;   
           }
           sesJqueryObject('.sesadvancedactivity_shedulepost_overlay').hide();
           sesJqueryObject('.sesadvancedactivity_shedulepost_select').hide();
           sesJqueryObject('.sesadvancedactivity_shedulepost').addClass('active');
           preventSubmitOnSocialNetworking();
          });
          sesJqueryObject(document).on('click','#sesadvancedactivity_shedulepost',function(e){
           e.preventDefault();
           sesJqueryObject('.sesadvancedactivity_shedulepost_overlay').show();
           sesJqueryObject('.sesadvancedactivity_shedulepost_select').show();
           sesJqueryObject(this).addClass('active');
           makeDateTimePicker();
          });
          sesJqueryObject(document).on('click','.schedule_post_close',function(e){
              e.preventDefault();
            sesJqueryObject('.sesadvancedactivity_shedulepost_overlay').hide();
            sesJqueryObject('.sesadvancedactivity_shedulepost_select').hide();
            if(sesJqueryObject('.sesadvancedactivity_shedulepost_error').css('display') == 'block')
              sesJqueryObject('.sesadvancedactivity_shedulepost_error').html('').hide();
            sesJqueryObject('#scheduled_post').val('');
             sesJqueryObject('#sesadvancedactivity_shedulepost').removeClass('active');
             sesJqueryObject('.bootstrap-datetimepicker-widget').hide();
          });
          var schedule_post_datepicker;
          function makeDateTimePicker(){
            if(sesJqueryObject('.sesadvancedactivity_shedulepost_edit_overlay').length){
              var elem = 'scheduled_post_edit';
              var datepicker = 'datetimepicker_edit';
            }else{
              var elem = 'scheduled_post';
              var datepicker  = 'datetimepicker';
            }
            //if(!sesJqueryObject('#'+elem).val()){
              var now = new Date();
              now.setMinutes(now.getMinutes() + 10);
           // }
            schedule_post_datepicker = sesJqueryObject('#'+datepicker).datetimepicker({
            format: 'dd/MM/yyyy hh:mm:ss',
            maskInput: false,           // disables the text input mask
            pickDate: true,            // disables the date picker
            pickTime: true,            // disables de time picker
            pick12HourFormat: true,   // enables the 12-hour format time picker
            pickSeconds: true,         // disables seconds in the time picker
            startDate: now,      // set a minimum date
            endDate: Infinity          // set a maximum date
          });
          schedule_post_datepicker.on('changeDate', function(e) {
            var time = e.localDate.toString();
            var timeObj = new Date(time).getTime();
            //add 10 minutes
            var now = new Date();
            now.setMinutes(now.getMinutes() + 10);
            if(sesJqueryObject('.sesadvancedactivity_shedulepost_edit_overlay').length){
              var error = 'sesadvancedactivity_shedulepost_edit_error';
            }else{
              var error = 'sesadvancedactivity_shedulepost_error';
            }
            if(timeObj < now.getTime()){
              sesJqueryObject('.'+error).html("<?php echo $this->translate('choose time 10 minutes greater than current time.'); ?>").show();
              return false;
            }else{
             sesJqueryObject('.'+error).html('').hide();
            }
          });  
          }
          </script>

<?php if(empty($this->subjectGuid) && !$this->isOnThisDayPage){ ?>

  <?php if($this->isMemberHomePage){ 
    echo $this->partial(
        '_homesuggestions.tpl',
        'sesadvancedactivity',
        array()
        );
        }
  ?>
    <?php echo $this->partial(
            '_homefeedtabs.tpl',
            'sesadvancedactivity',
            array('identity'=>$this->identity,'lists'=>$this->lists)
          );
     ?>
<?php }else if(!$this->isOnThisDayPage && $this->subject() && ($this->subject()->getType() == 'user' || $this->subject()->getType() == 'businesses' || $this->subject()->getType() == 'sespage_page' ||  $this->subject()->getType() == 'sesgroup_group' ||  $this->subject()->getType() == 'stores' ||  $this->subject()->getType() == 'sesevent_event' ||  $this->subject()->getType() == 'classroom')){
  echo $this->partial(
        '_subjectfeedtabs.tpl',
        'sesadvancedactivity',
        array('identity'=>$this->identity,'lists'=>$this->lists)
        );
    }else{ ?>
<div class="sesact_feed_filters sesbasic_clearfix sesbasic_bxs sesbm displayN" style="display: none">
  <ul class="sesadvancedactivity_filter_tabs sesbasic_clearfix">
    <li class="sesadvancedactivity_filter_tabsli sesadv_active_tabs"><a href="javascript:;" class="sesadv_tooltip" data-src="all">
        <span></span></a></li>
  </ul>
</div>
<script type="application/javascript">
  var filterResultrequest;
  sesJqueryObject(document).on('click','ul.sesadvancedactivity_filter_tabs li a',function(e){
//    if(sesJqueryObject(this).parent().hasClass('active') || sesJqueryObject(this).hasClass('viewmore'))
//     return false;
    if(sesJqueryObject(this).hasClass('viewmore'))
      return false;

    sesJqueryObject('.sesadvancedactivity_filter_img').show();
    sesJqueryObject('.sesadvancedactivity_filter_tabsli').removeClass('active sesadv_active_tabs');
    sesJqueryObject(this).parent().addClass('active sesadv_active_tabs');
    var filterFeed = sesJqueryObject(this).attr('data-src');
    if(typeof filterResultrequest != 'undefined')
      filterResultrequest.cancel();
    var url = '<?php echo $this->url(array('module' => 'core', 'controller' => 'widget', 'action' => 'index', 'content_id' => $this->identity), 'default', true) ?>';
    var hashTag = sesJqueryObject('#hashtagtextsesadv').val();
    var adsIds = sesJqueryObject('.sescmads_ads_listing_item');
    var adsIdString = "";
    if(adsIds.length > 0){
      sesJqueryObject('.sescmads_ads_listing_item').each(function(index){
        if(typeof dataFeedItem == "undefined")
          adsIdString = sesJqueryObject(this).attr('rel')+ "," + adsIdString ;
      });
    }
    filterResultrequest = new Request.HTML({
      url : url+"?hashtag="+hashTag+'&isOnThisDayPage='+isOnThisDayPage+'&isMemberHomePage='+isMemberHomePage,
      data : {
        format : 'html',
        'filterFeed' : filterFeed,
        'feedOnly' : true,
        'action_id':sesAdvancedActivityGetAction_id,
        'getUpdates':1,
        'nolayout' : true,
        'ads_ids': adsIdString,
        'subject' : en4.core.subject.guid,
      },
      evalScripts : true,
      onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript) {

        if(!sesAdvancedActivityGetFeeds){
          sesJqueryObject('#activity-feed').append(responseHTML);
        }else{
          sesJqueryObject('#activity-feed').html(responseHTML);
        }
        if(sesJqueryObject('#activity-feed').children().length)
          sesJqueryObject('.sesadv_noresult_tip').hide();
        else
          sesJqueryObject('.sesadv_noresult_tip').show();
        //initialize feed autoload counter
        counterLoadTime = 0;
        sesadvtooltip();
        initSesadvAnimation();
        Smoothbox.bind($('activity-feed'));
        sesJqueryObject('.sesadvancedactivity_filter_img').hide();
        activateFunctionalityOnFirstLoad();
      }
    });
    filterResultrequest.send();
  });
</script>
<style>
  .displayN{
    display: none !important;
  }
</style>
<?php
}
 ?>
<?php if ($this->updateSettings && !$this->action_id && !$this->isOnThisDayPage): // wrap this code around a php if statement to check if there is live feed update turned on ?>
  <script type="text/javascript">
    var SesadvancedactivityUpdateHandler;
    en4.core.runonce.add(function() {
      try {
          SesadvancedactivityUpdateHandler = new SesadvancedactivityUpdateHandler({
            'baseUrl' : en4.core.baseUrl,
            'basePath' : en4.core.basePath,
            'identity' : 4,
            'delay' : <?php echo $this->updateSettings;?>,
            'last_id': <?php echo sprintf('%d', $this->firstid) ?>,
            'subject_guid' : '<?php echo $this->subjectGuid ?>'
          });
          setTimeout("SesadvancedactivityUpdateHandler.start()",1250);
          //activityUpdateHandler.start();
          window._SesadvancedactivityUpdateHandler = SesadvancedactivityUpdateHandler;
      } catch( e ) {
        //if( $type(console) )
      }
      // if(sesJqueryObject('#activity-feed').children().length && <?php echo (int)$this->getUpdates; ?> == 1)
      //  sesJqueryObject('.sesadv_noresult_tip').hide();
      // else
      //  sesJqueryObject('.sesadv_noresult_tip').show();
    });
  </script>
<?php endif;?>

<?php if( $this->post_failed == 1 ): ?>
  <div class="tip">
    <span>
      <?php $url = $this->url(array('module' => 'user', 'controller' => 'settings', 'action' => 'privacy'), 'default', true) ?>
      <?php echo $this->translate('The post was not added to the feed. Please check your %1$sprivacy settings%2$s.', '<a href="'.$url.'">', '</a>') ?>
    </span>
  </div>
<?php endif; ?>

<?php // If requesting a single action and it does not exist, show error ?>
<?php if( !$this->activity ): ?>
  <?php if( $this->action_id ): ?>
    <span style="display: none" class="no_content_activity_id">
      <h2><?php echo $this->translate("Activity Item Not Found") ?></h2>
      <p>
        <?php echo $this->translate("The page you have attempted to access could not be found.") ?>
      </p>
    </span>
  <?php endif; ?>
<?php endif; ?>

<div class="sesadv_content_load_img sesbasic_loading_container">
</div>
<div class="sesadv_tip sesact_tip_box sesadv_noresult_tip" style="display:<?php echo !sprintf('%d', $this->activityCount) && $this->getUpdates ? 'block' : 'none'; ?>;">
<?php if(!$this->isOnThisDayPage){ ?>
  <span>
    <?php echo $this->translate("Nothing has been posted here yet - be the first!") ?>
  </span>
 <?php }else{ ?>
 <span>
    <?php echo $this->translate('No memories for you on this day.') ?>
  </span>
 <?php } ?>
</div>
<div id="feed-update"></div>
<?php echo $this->activityLoop($this->activity, array(
  'action_id' => $this->action_id,
  'communityadsIds' => $this->communityadsIds,
  'viewAllComments' => $this->viewAllComments,
  'viewAllLikes' => $this->viewAllLikes,
  'getUpdate' => $this->getUpdate,
  'getUpdates' => $this->getUpdates,
  'isOnThisDayPage'=>$this->isOnThisDayPage,
  'isMemberHomePage' => $this->isMemberHomePage,
  'userphotoalign' => $this->userphotoalign,
  'filterFeed'=>$this->filterFeed,
  'feeddesign'=>$this->feeddesign,
)) ?>
<?php if(!$this->isOnThisDayPage): ?>
<div class="sesact_view_more sesadv_tip sesact_tip_box" id="feed_viewmore_activityact" style="display: none;">
	<a href="javascript:void(0);" id="feed_viewmore_activityact_link" class="sesbasic_animation sesbasic_linkinherit"><i class="fa fa-sync"></i><span><?php echo $this->translate('View More');?></span></a>
</div>
<div class="sesadv_tip sesact_tip_box" id="feed_loading" style="display: none;">
  <span><i class="fas fa-circle-notch fa-spin"></i></span>
</div>
<?php if( !$this->feedOnly && $this->isMemberHomePage && !$this->isOnThisDayPage): ?>
</div>
<?php endif; ?>
<div class="sesadv_tip sesact_tip_box" id="feed_no_more_feed" style="display:none;">
	<span>No more post</span>
</div>
<script type="application/javascript">

  sesJqueryObject(document).ready(function() {
    var welcomeactive = sesJqueryObject('#sesadv_tabs_cnt li.active');
    if(sesJqueryObject(welcomeactive).attr('data-url') == 1) {
      sesJqueryObject(welcomeactive).find('a').trigger('click');
    }
  });

  sesJqueryObject(document).on('click','#sesadv_tabs_cnt li a',function(e) {
    var id = sesJqueryObject(this).parent().attr('data-url');
    var instid = sesJqueryObject(this).parent().parent().attr('data-url');

    if(instid == 4) return;

    sesJqueryObject('.sesadv_tabs_content').hide();


    sesJqueryObject('#sesadv_tabs_cnt > li').removeClass('active');
    sesJqueryObject(this).parent().addClass('active');
    sesJqueryObject('#sesadv_tab_'+id).show();

    if(id == 1 || id == 3) {
      sesJqueryObject('#feed_no_more_feed').addClass('dNone');
    }else
      sesJqueryObject('#feed_no_more_feed').removeClass('dNone');
    if(id == 3) return;
    if(sesJqueryObject('#sesadv_tab_'+id).find('.sesadv_loading_img').length){
      var url = en4.core.baseUrl+sesJqueryObject('#sesadv_tab_'+id).find('.sesadv_loading_img').attr('data-href');
      //get content
      if(typeof requestsent != 'undefined')
        requestsent.cancel();
      requestsent = (new Request.HTML({
      method: 'post',
      'url': url,
      'data': {
        format: 'html'
      },
      onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
       sesJqueryObject('#sesadv_tab_'+id).html(responseHTML);
      }
    }));
     requestsent.send();
    }
  });

</script>
<?php endif; ?>

<script type="application/javascript">
if(typeof initSesadvAnimation != "undefined")
initSesadvAnimation();
</script>

<?php if($this->isOnThisDayPage){ ?>
<div class="sesact_feed_thanks_block centerT">
	<img src="application/modules/Sesadvancedactivity/externals/images/thanks.png"alt="" />
  <span><?php echo $this->translate("Thanks for coming!"); ?></span>
</div>
<?php } ?>
<?php if($this->enablewidthsetting): ?>
  <style type="text/css">
  .sesact_feed ul.feed .feed_attachment_album_photo img {   
    max-width: <?php echo $this->sesact_image1_width ?>px !important;
    max-height: <?php echo $this->sesact_image1_height ?>px !important;
    width: auto;
  }
	div.feed_images_2 > [class*='feed_attachment_'] .feed_attachment_photo img,
	div.feed_images_3 > [class*='feed_attachment_'] .feed_attachment_photo img,
	div.feed_images_4 > [class*='feed_attachment_'] .feed_attachment_photo img,
	div.feed_images_5 > [class*='feed_attachment_'] .feed_attachment_photo img,
	div.feed_images_6 > [class*='feed_attachment_'] .feed_attachment_photo img,
	div.feed_images_7 > [class*='feed_attachment_'] .feed_attachment_photo img,
	div.feed_images_8 > [class*='feed_attachment_'] .feed_attachment_photo img,
	div.feed_images_9 > [class*='feed_attachment_'] .feed_attachment_photo img{
		max-width:100% !important;
		max-height:inherit !important;
	}
  .feed_images_2 > [class*='feed_attachment_'] {
    height:<?php echo $this->sesact_image2_height ?>px;
    width:<?php echo $this->sesact_image2_width ?>px;
  }
  .feed_images_3 > [class*='feed_attachment_']:first-child{
    height:<?php echo $this->sesact_image3_bigheight ?>px;
    width:<?php echo $this->sesact_image3_bigwidth ?>px;
  }
  .feed_images_3 > [class*='feed_attachment_']{
    height:<?php echo $this->sesact_image3_smallheight ?>px;
    width:<?php echo $this->sesact_image3_smallwidth ?>px;
  }
  .feed_images_4 > [class*='feed_attachment_']:first-child{
    height:<?php echo $this->sesact_image4_bigheight ?>px;
    width:<?php echo $this->sesact_image4_bigwidth ?>px;
  }
  .feed_images_4 > [class*='feed_attachment_']{
    height:<?php echo $this->sesact_image4_smallheight ?>px;
    width:<?php echo $this->sesact_image4_smallwidth ?>px;
  }
  .feed_images_5 > [class*='feed_attachment_']:first-child{
    height:<?php echo $this->sesact_image5_bigheight ?>px;
    width:<?php echo $this->sesact_image5_bigwidth ?>px;
  }
  .feed_images_5 > [class*='feed_attachment_']{
    height:<?php echo $this->sesact_image5_smallheight ?>px;
    width:<?php echo $this->sesact_image5_smallwidth ?>px;
  }
  .feed_images_6 > [class*='feed_attachment_']{
    height:<?php echo $this->sesact_image6_height ?>px;
    width:<?php echo $this->sesact_image6_width ?>px;
  }
  .feed_images_7 > [class*='feed_attachment_']:nth-child(4),
  .feed_images_7 > [class*='feed_attachment_']:nth-child(5),
  .feed_images_7 > [class*='feed_attachment_']:nth-child(6),
  .feed_images_7 > [class*='feed_attachment_']:nth-child(7){
    height:<?php echo $this->sesact_image7_smallheight ?>px;
    width:<?php echo $this->sesact_image7_smallwidth ?>px;
  }
  .feed_images_7 > [class*='feed_attachment_']:nth-child(1),
  .feed_images_7 > [class*='feed_attachment_']:nth-child(2),
  .feed_images_7 > [class*='feed_attachment_']:nth-child(3){
    height:<?php echo $this->sesact_image7_bigheight ?>px;
    width:<?php echo $this->sesact_image7_bigwidth ?>px;
  }
  .feed_images_8 > [class*='feed_attachment_']{
    height:<?php echo $this->sesact_image8_height ?>px;
    width:<?php echo $this->sesact_image8_width ?>px;
  }
  .feed_images_9 > [class*='feed_attachment_']{
    height:<?php echo $this->sesact_image9_height ?>px;
    width:<?php echo $this->sesact_image9_width ?>px;
  }
  </style>
<?php endif;
 ?>

<?php if($this->feeddesign == 2){ ?>
	<script type="application/javascript">
		var wookmark = undefined;
		var isactivityloadedfirst= true;
	 //Code for Pinboard View
		var wookmark<?php echo $randonNumber ?>;
		function pinboardLayoutFeed_<?php echo $randonNumber ?>(force){
			if(isactivityloadedfirst == true){
				sesJqueryObject('#activity-feed').append('<li id="sesact_feed_loading" style="margin-bottom:20px;"><div class="sesbasic_loading_container" style="height:100px;"></div></li>')
			}
			//sesJqueryObject('.new_image_pinboard').css('display','none');
			var imgLoad = imagesLoaded('._sesactpinimg');
			var imgleangth = imgLoad.images.length;
			if(imgleangth > 0){
				var counter = 1; 
				imgLoad.on('progress',function(instance,image){
					sesJqueryObject(image.img).removeClass('_sesactpinimg');
					sesJqueryObject(image.img).closest('.sesact_pinfeed_hidden').removeClass('sesact_pinfeed_hidden');
					imageLoadedAll<?php echo $randonNumber ?>();
					if(counter == 1){
						//sesJqueryObject('.sesact_pinfeed_hidden').removeClass('sesact_pinfeed_hidden');
						//sesJqueryObject('._sesactpinimg').removeClass('._sesactpinimg');
					}
					if(counter == imgleangth){
						sesJqueryObject('#sesact_feed_loading').remove();
					}
					counter = counter +1;
				});
			}else{
				sesJqueryObject('.sesact_pinfeed_hidden').removeClass('sesact_pinfeed_hidden');
				sesJqueryObject('._sesactpinimg').removeClass('._sesactpinimg');
				imageLoadedAll<?php echo $randonNumber ?>();
				sesJqueryObject('#sesact_feed_loading').remove();
			}
		}
		function imageLoadedAll<?php echo $randonNumber ?>(force){
		 sesJqueryObject('#activity-feed').addClass('sesbasic_pinboard_<?php echo $randonNumber; ?>');
		 if (typeof wookmark<?php echo $randonNumber ?> == 'undefined') {
				(function() {
					function getWindowWidth() {
						return Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
					}				
					wookmark<?php echo $randonNumber ?> = new Wookmark('.sesbasic_pinboard_<?php echo $randonNumber; ?>', {
						itemWidth: <?php echo isset($this->sesact_pinboard_width) ? str_replace(array('px','%'),array(''),$this->sesact_pinboard_width) : '300'; ?>, // Optional min width of a grid item
						outerOffset: 0, // Optional the distance from grid to parent
						align:'left',
						flexibleWidth: function () {
							// Return a maximum width depending on the viewport
							return getWindowWidth() < 1024 ? '100%' : '40%';
						}
					});
				})();
			} else {
				wookmark<?php echo $randonNumber ?>.initItems();
				wookmark<?php echo $randonNumber ?>.layout(true);
			}
	}
  function feedUpdateFunction(){
    setTimeout(function(){pinboardLayoutFeed_<?php echo $randonNumber ?>();},200);
  }
	sesJqueryObject(document).ready(function(){
		pinboardLayoutFeed_<?php echo $randonNumber ?>();
	});
	sesJqueryObject(document).click(function(){
		pinboardLayoutFeed_<?php echo $randonNumber ?>();
	});
	sesJqueryObject(document).bind("paste", function(e){
		pinboardLayoutFeed_<?php echo $randonNumber ?>();
	});
	sesJqueryObject(document).on('click','.tab_layout_activity_feed',function (event) {
		pinboardLayoutFeed_<?php echo $randonNumber ?>();
	});
	sesJqueryObject('#activity-feed').one("DOMSubtreeModified",function(){
		// do something after the div content has changed
	 imageLoadedAll<?php echo $randonNumber ?>();
	});
	</script>
<?php } ?>
<script type="application/javascript">

sesJqueryObject(document).ready(function(e){
  if(typeof complitionRequestTrigger == 'function'){
    complitionRequestTrigger();  
  }  
})

sesJqueryObject('.selectedTabClick').click(function(e){
  var rel = sesJqueryObject(this).data('rel');
  if(rel != 'all'){
    document.getElementById('compose-'+rel+'-activator').click();  
    if(rel == "photo"){
      document.getElementById('dragandrophandler').click();  
    }
  }  
})
</script>
