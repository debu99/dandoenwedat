<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php
$base_url = $this->layout()->staticBaseUrl;
$this->headLink()->appendStylesheet($base_url . 'application/modules/Sesevent/externals/styles/styles.css'); 
$this->headScript()->appendFile($base_url . 'application/modules/Sesevent/externals/scripts/jquery1.5.js');
$this->headScript()->appendFile($base_url . 'application/modules/Sesevent/externals/scripts/jquery.easing-1.3.js');
$this->headScript()->appendFile($base_url . 'application/modules/Sesevent/externals/scripts/jquery.heroCarousel-1.3.js');

$this->headScript()->appendFile($base_url . 'application/modules/Sesbasic/externals/scripts/jquery.min.js');
$this->headScript()->appendFile($base_url . 'application/modules/Sesbasic/externals/scripts/customscrollbar.concat.min.js');
?>
<?php
$this->headScript()
->appendFile($base_url . 'externals/autocompleter/Observer.js')
->appendFile($base_url . 'externals/autocompleter/Autocompleter.js')
->appendFile($base_url . 'externals/autocompleter/Autocompleter.Local.js')
->appendFile($base_url . 'externals/autocompleter/Autocompleter.Request.js');
?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/styles/customscrollbar.css'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/jquery.timepicker.css'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/bootstrap-datepicker.css'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/jquery1.11.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/scripts/jquery.timepicker.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/scripts/bootstrap-datepicker.js'); ?>
<?php $this->headScript()->appendFile('externals/tinymce/tinymce.min.js'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/bootstrap-datepicker.css'); ?>
<style>
.displayB{display:inline-block !important;}
</style>
<div class="sesevent_hero_carousel_wrapper sesbasic_bxs sesbasic_clearfix <?php echo $this->isfullwidth ? 'isfull_width' : '' ; ?> isbtm_bar" style="height:<?php echo $this->heightChange.'px'; ?><?php echo $this->margin_top ? ';margin-top:'.str_replace('px','',$this->margin_top).'px' : ''; ?>">
  <div class="sesevent_slideshow_loading_container" style="height:<?php echo $this->heightChange.'px'; ?>;"><div class="sesbasic_loading_container" style="height: 100%;"></div></div>
  <div class="sesevent_hero_carousel sesbasic_clearfix" style="display:none;">
  <?php if(in_array('findVenue',$this->info)){ ?>
    <div class="sesevent_hero_carousel_drop_box sesevent_hero_carousel_drop_box_vfm" style="display:none;height:<?php echo $this->height.'px'; ?>;">
    	<div class="sesevent_hero_carousel_drop_box_inner">
      	<a href="javascript:;" class="sesevent_hero_carousel_drop_box_close sesevent_carousel_vfm_btn_close_btn fa fa-times"></a>
        <div class="sesevent_hero_carousel_drop_box_content sesbasic_custom_scroll">
        	<span class="sesevent_hero_carousel_drop_box_heading centerT"><?php echo $this->translate('Let us find events for you, Free.'); ?></span>
          <div>
          <form id="search_form_venue_global" class="sesevent_hero_carousel_drop_box_form centerT" method="get" action="<?php echo $this->url(array('action'=>'browse'), 'sesevent_general'); ?>">
            <div><input type="text" name="search_text" id="search_text" value="" placeholder="<?php echo $this->translate("What are you planning?"); ?>" /></div>
            <div><input type="text" name="start_date" id="start_date" class="displayB" value="<?php echo date("m/d/Y");  ?>" placeholder="<?php echo $this->translate("Start Date"); ?>" /></div>
            <div><input type="text" name="end_date" id="end_date" class="displayB" value="" placeholder="<?php echo $this->translate("End Date"); ?>" /></div>
            <div>
             <select name="category_id" id="category_id" onchange="showSubCategory(this.value);">
                <option value=""><?php echo $this->translate("Select Category"); ?></option>
                <?php $categories = Engine_Api::_()->getDbtable('categories', 'sesevent')->getCategoriesAssoc();
                if(count($categories)){
                  foreach($categories as $key=>$category){?>
                    <option value="<?php echo $key; ?>"><?php echo $this->translate($category); ?></option> 
                <?php    
                  }
                }
              ?> 
             </select>
            </div>
            <div>
             <select name="view" id="view">
                <option value="0"><?php echo $this->translate("Everyone's Events"); ?></option>
                <?php if(Engine_Api::_()->user()->getViewer()->getIdentity()){ ?>
                  <option value="1"><?php echo $this->translate("Only My Friend's Events"); ?></option>
                <?php } ?>
                <option value="ongoing"><?php echo $this->translate("Ongoing Events"); ?></option>
                <option value="past"><?php echo $this->translate("Past Events"); ?></option>
                <option value="week"><?php echo $this->translate("This Week"); ?></option>
                <option value="weekend"><?php echo $this->translate("This Weekends"); ?></option>
                <option value="future"><?php echo $this->translate("Upcomming Events"); ?></option>
                <option value="month"><?php echo $this->translate("This Month"); ?></option>
             </select>
             </div>
             <div><input type="text" name="venue" value="" placeholder="<?php echo $this->translate("Venue"); ?>" /></div>
             <div><input type="text" name="country" value="" placeholder="<?php echo $this->translate("Country"); ?>" /></div>
             <div><input type="text" name="state" value="" placeholder="<?php echo $this->translate("State"); ?>" /></div>
             <div><input type="text" name="city" value="" placeholder="<?php echo $this->translate("City"); ?>" /></div>
						<button type="submit" style="color:<?php echo $this->fvbtextcolor; ?>;background-color:<?php echo $this->fvbbtncolor; ?>" ><?php echo $this->translate("Search"); ?></button>
           </form>
          </div>
        </div>
      </div>
    </div>
    <?php } ?>
    <?php if(in_array('searchForVenue',$this->info)){ ?>
    <div class="sesevent_hero_carousel_drop_box sesevent_hero_carousel_drop_box_sfv" style="display:none;height:<?php echo $this->height.'px'; ?>">
    	<div class="sesevent_hero_carousel_drop_box_inner">
      	<a href="javascript:;" class="sesevent_hero_carousel_drop_box_close sesevent_carousel_sfv_btn_close_btn fa fa-times"></a>
        <div class="sesevent_hero_carousel_drop_box_vlist sesbasic_custom_scroll">
					<ul>
          	<?php
            	$locale = Zend_Registry::get('Zend_Translate')->getLocale();
              $territories = Zend_Locale::getTranslationList('territory', $locale, 2);
              asort($territories);
              if(count($territories)){
                foreach($territories as $key=>$valCon){?>
            		<li class="floatL"><a href="<?php echo $this->url(array('action'=>'browse'), 'sesevent_general'); ?>?country=<?php echo $valCon; ?>"><?php echo $valCon; ?></a></li>              <?php    
              	}
              }
    				?>            
          </ul>
        </div>
      </div>
    </div>
     <?php } ?>
  	<div class="sesevent_hero_carousel_content" style="height:<?php echo $this->height.'px'; ?>">
    	<div class="sesevent_hero_carousel_content_inner">
      	<section class="centerT">
          <span class="sesevent_hero_carousel_content_heading" style="color:<?php echo $this->titlecolor ; ?>"><?php echo $this->translate($this->titleS); ?></span>
          <span class="sesevent_hero_carousel_content_description" style="color:<?php echo $this->descriptioncolor ; ?>"><?php echo $this->translate($this->descriptionS); ?></span>
           <?php if(in_array('searchForVenue',$this->info) || in_array('searchForVenue',$this->info)){ ?>
          <span class="sesevent_hero_carousel_content_btns">
          <?php if(in_array('searchForVenue',$this->info)) { ?>
            <button class="sesevent_carousel_sfv_btn" style="color:<?php echo $this->sfvtextcolor; ?>;background-color:<?php echo $this->sfvbtncolor; ?>"><?php echo $this->translate("Search by Country"); ?></button>
          <?php } ?>
          <?php if(in_array('findVenue',$this->info)) { ?>
            <button class="sesevent_carousel_vfm_btn" style="color:<?php echo $this->fvbtextcolor; ?>;background-color:<?php echo $this->fvbbtncolor; ?>"><?php echo $this->translate("Find Events Near You"); ?></button>
          <?php } ?>
          </span>
          <?php } ?>
        </section>
    	</div>    
    </div>    
  	<div class="sesevent_hero_carousel_inner">
      <?php foreach($this->slides as $slide){ ?>
      <article style="height:<?php echo $this->height.'px'; ?>">
        <img src="<?php echo Engine_Api::_()->storage()->get($slide->photo_id, '')->getPhotoUrl(); ?>" alt="slide 1" width="100%" height="100%" />
      </article>
   <?php } ?>
   </div>
   <?php if(in_array('getStarted',$this->info)) { ?>
    <div class="sesevent_hero_carousel_btm_bar centerT" style="background-color:<?php echo $this->gstbgcolor; ?>">
    	<?php if(Engine_Api::_()->user()->getViewer()->getIdentity() && $this->getStartedLink){ ?>
      	<a href="<?php echo $this->url(array('action'=>'create'), 'sesevent_general'); ?>" class="sessmoothbox" style="color:<?php echo $this->gsttextcolor; ?>"><?php echo $this->translate('Get Started'); ?> <i class="fa fa-chevron-circle-right "></i></a>
      <?php }else{ ?>
     	<a href="<?php echo $this->url(array('action'=>'create'), 'sesevent_general'); ?>" style="color:<?php echo $this->gsttextcolor; ?>"><?php echo $this->translate('Get Stared'); ?> <i class="fa fa-chevron-circle-right"></i></a>
     <?php } ?>
    </div>
    <?php } ?>
  </div>
</div>
<script type="application/javascript">
sesJquerySlideshowEve(document).ready(function(){
	//set height according to width
	function changeWidth(){
		<?php if(!$this->isfullwidth){ ?>
		var totalWidth	= sesJquerySlideshowEve('.sesevent_hero_carousel_wrapper').width();
		<?php }else{ ?>
		var totalWidth = sesJquerySlideshowEve(window).width();
		<?php } ?>
		var percentageWidth = '<?php echo $this->percentageWidth ?>';
		var widthitem = (totalWidth*percentageWidth)/100;
		var totalElem = sesJqueryObject('.sesevent_hero_carousel_inner > article');
		for(i=0;i<totalElem.length;i++){
				sesJqueryObject(totalElem[i]).css('width',widthitem+'px');
		}
	}
	changeWidth();
	<?php if($this->isfullwidth){ ?>
		var htmlElement = document.getElementsByTagName("body")[0];
  	htmlElement.addClass('sesevent_slideshow');
	<?php } ?>
	<?php if($this->navigation){  ?>
		var navigation = true;
	<?php }else{ ?>
		var navigation = false;
	<?php } ?>
	sesJquerySlideshowEve('.sesevent_hero_carousel_inner').heroCarousel({
		easing:'easeOutExpo',
		css3pieFix:true,
		animationSpeed:<?php echo $this->animationSpeed; ?>,
		onLoad:function(){
			//slider end sliding.
			sesJqueryObject('.sesevent_slideshow_loading_container').hide();
			sesJqueryObject('.sesevent_hero_carousel').show();
		},
		onComplete:function(){
			//slide start sliding.
		},
		onStart:function(){
			//slide end sliding.
		},
	});
	if(!navigation){
		sesJquerySlideshowEve('.sesevent_hero_carousel_nav').hide();
	}
});
sesJquerySlideshowEve(document).ready(function(){
    sesJquerySlideshowEve(".sesevent_carousel_vfm_btn_close_btn").click(function(){
        sesJquerySlideshowEve(".sesevent_hero_carousel_drop_box_vfm").slideUp();
    });
    sesJquerySlideshowEve(".sesevent_carousel_vfm_btn").click(function(){
				sesJquerySlideshowEve(".sesevent_hero_carousel_drop_box_sfv").slideUp();
        sesJquerySlideshowEve(".sesevent_hero_carousel_drop_box_vfm").slideDown();
    });
    sesJquerySlideshowEve(".sesevent_carousel_sfv_btn_close_btn").click(function(){
        sesJquerySlideshowEve(".sesevent_hero_carousel_drop_box_sfv").slideUp();
    });
    sesJquerySlideshowEve(".sesevent_carousel_sfv_btn").click(function(){
				sesJquerySlideshowEve(".sesevent_hero_carousel_drop_box_vfm").slideUp();
        sesJquerySlideshowEve(".sesevent_hero_carousel_drop_box_sfv").slideDown();
    });
});
var selectedDate =  new Date(sesJqueryObject('#start_date').val());
var FromEndDate;
var startCalanderDate = new Date('<?php echo date("m/d/Y");  ?>');
sesBasicAutoScroll('#start_date').datepicker({
			format: 'm/d/yyyy',
			weekStart: 1,
			autoclose: true,
			startDate: startCalanderDate,
			endDate: FromEndDate, 
	}).on('changeDate', function(ev){
		selectedDate = ev.date;
		FromEndDate = new Date(sesBasicAutoScroll('#end_date').val());
		sesBasicAutoScroll('#end_date').datepicker('setStartDate', selectedDate);
	});
	sesBasicAutoScroll('#end_date').datepicker({
			format: 'm/d/yyyy',
			weekStart: 1,
			autoclose: true,
			startDate: selectedDate,
	}).on('changeDate', function(ev){
		FromEndDate = new Date(ev.date.valueOf());
		FromEndDate.setDate(FromEndDate.getDate(new Date(ev.date.valueOf())));
		sesBasicAutoScroll('#start_date').datepicker('setEndDate', FromEndDate);
	});
  en4.core.runonce.add(function() {
    var searchAutocomplete = new Autocompleter.Request.JSON('search_text', "<?php echo $this->url(array('module' => 'sesevent', 'controller' => 'index', 'action' => 'search'), 'default', true) ?>", {
      'postVar': 'text',
      'delay' : 250,      
      'minLength': 1,
      'selectMode': 'pick',
      'autocompleteType': 'tag',
      'customChoices': true,
      'filterSubset': true,
      'multiple': false,
      'className': 'sesbasic-autosuggest',
			'indicatorClass':'input_loading',
      'injectChoice': function(token) {
				var choice = new Element('li', {
					'class': 'autocompleter-choices',
					'html': token.photo,
					'id': token.label
				});
				new Element('div', {
					'html': this.markQueryValue(token.label),
					'class': 'autocompleter-choice'
				}).inject(choice);
				new Element('div', {
					'html': this.markQueryValue(token.resource_type),
					'class': 'autocompleter-choice bold'
				}).inject(choice);
				choice.inputValue = token;
				this.addChoiceEvents(choice).inject(this.choices);
				choice.store('autocompleteChoice', token);        
      }
    });
	})
</script>