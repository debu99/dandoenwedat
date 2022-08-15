<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sessociallogin
 * @package    Sessociallogin
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: statistic.tpl 2017-07-04 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

?>
<?php $settings = Engine_Api::_()->getApi('settings', 'core'); ?>
<?php include APPLICATION_PATH .  '/application/modules/Sessociallogin/views/scripts/dismiss_message.tpl';?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sessocialshare/externals/scripts/chart.js'); 
$this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/sesJquery.js');
?>
  
<h3><?php echo $this->translate("Social Media Login Statistics") ?></h3>
<p><?php echo $this->translate("Below are the statistics about the login via social media on your website.") ?></p>
<br />  
<?php 
$totalCount = $settings->getSetting('sessociallogin.facebooksignup') + $settings->getSetting('sessociallogin.twittersignup') + $settings->getSetting('sessociallogin.pinterestsignup') + $settings->getSetting('sessociallogin.googlesignup') + $settings->getSetting('sessociallogin.linkedinsignup') + $settings->getSetting('sessociallogin.instagramsignup') + $settings->getSetting('sessociallogin.flickrsignup') + $settings->getSetting('sessociallogin.hotmailsignup') + $settings->getSetting('sessociallogin.vksignup');

?>
<div style="position:relative;" id="sessociallogin_main_stats">
	<div class="sessociallogin_stats_container">
    <div id="sessosh_statscontent" class="sessociallogin_stats_table">
    	<div class="sessociallogin_stats_total"><?php echo $this->translate('Total Registrations Count: %s', $totalCount); ?></div>
      <div style="border-color:#3B5998;">
        <span><?php echo "Facebook" ?></span>
        <span style="color:#3B5998;"><?php if($settings->getSetting('sessociallogin.facebooksignup')) { echo $settings->getSetting('sessociallogin.facebooksignup'); } else { echo '0'; } ?></span>
      </div>
      <div style="border-color:#4099FF;">
        <span><?php echo "Twitter" ?></span>
        <span style="color:#4099FF;"><?php if($settings->getSetting('sessociallogin.twittersignup')) { echo $settings->getSetting('sessociallogin.twittersignup'); } else { echo '0'; } ?></span>
      </div>
      <div style="border-color:#cb2027;">
        <span><?php echo "Pinterest" ?></span>
        <span style="color:#cb2027;"><?php if($settings->getSetting('sessociallogin.pinterestsignup')) { echo $settings->getSetting('sessociallogin.pinterestsignup'); } else { echo '0'; } ?></span>
      </div>
      <div style="border-color:#DC4E41;">
        <span><?php echo "Google" ?></span>
        <span style="color:#DC4E41;"><?php if($settings->getSetting('sessociallogin.googlesignup')) { echo $settings->getSetting('sessociallogin.googlesignup'); } else { echo '0'; } ?></span>
      </div>
      <div style="border-color:#0077b5;">
        <span><?php echo "Linkedin" ?></span>
        <span style="color:#0077b5;"><?php if($settings->getSetting('sessociallogin.linkedinsignup')) { echo $settings->getSetting('sessociallogin.linkedinsignup'); } else { echo '0'; } ?></span>
      </div>
      <div style="border-color:#2E5E86;">
        <span><?php echo "Instagram" ?></span>
        <span style="color:#2E5E86;"><?php if($settings->getSetting('sessociallogin.instagramsignup')) { echo $settings->getSetting('sessociallogin.instagramsignup'); } else { echo '0'; } ?></span>
      </div>
      <div style="border-color:#0063dc;">
        <span><?php echo "Flickr" ?></span>
        <span style="color:#0063dc;"><?php if($settings->getSetting('sessociallogin.flickrsignup')) { echo $settings->getSetting('sessociallogin.flickrsignup'); } else { echo '0'; } ?></span>
      </div>
      <div style="border-color:#F89839;">
        <span><?php echo "Hotmail" ?></span>
        <span style="color:#F89839;"><?php if($settings->getSetting('sessociallogin.hotmailsignup')) { echo $settings->getSetting('sessociallogin.hotmailsignup'); } else { echo '0'; } ?></span>
      </div>
      <div style="border-color:#436eab;">
        <span><?php echo "VK" ?></span>
        <span style="color:#436eab;"><?php if($settings->getSetting('sessociallogin.vksignup')) { echo $settings->getSetting('sessociallogin.vksignup'); } else { echo '0'; } ?></span>
      </div>
      <div style="border-color:#7f40bd;">
        <span><?php echo "Yahoo" ?></span>
        <span style="color:#7f40bd;"><?php if($settings->getSetting('sessociallogin.yahoosignup')) { echo $settings->getSetting('sessociallogin.yahoosignup'); } else { echo '0'; } ?></span>
      </div>
    </div>
  	<div id="piechart" class="sessociallogin_stats_chart"></div>
  	<div class="sesbasic_loading_cont_overlay" id="sessociallogin_loading_cont_overlay"></div>
  	<div id="error_message" class="tip" style="display:none;">
      <span>
        <?php echo "There are no no results.";?>
      </span>
    </div>
	</div>
</div>

<script type="text/javascript">


  google.charts.load("current", {packages:["corechart"]});
  google.charts.setOnLoadCallback(drawChart);
  
  function drawChart() {
    var data = google.visualization.arrayToDataTable([
      ['Language', 'Speakers (in millions)'],
      ['Facebook', <?php if($settings->getSetting('sessociallogin.facebooksignup')) { echo $settings->getSetting('sessociallogin.facebooksignup'); } else { echo '0'; } ?>], 
      ['Twitter', <?php if($settings->getSetting('sessociallogin.twittersignup')) { echo $settings->getSetting('sessociallogin.twittersignup'); } else { echo '0'; } ?>],
      ['Pinterest', <?php if($settings->getSetting('sessociallogin.pinterestsignup')) { echo $settings->getSetting('sessociallogin.pinterestsignup'); } else { echo '0'; } ?>],
      ['Google', <?php if($settings->getSetting('sessociallogin.googlesignup')) { echo $settings->getSetting('sessociallogin.googlesignup'); } else { echo '0'; } ?>],
      ['Linkedin', <?php if($settings->getSetting('sessociallogin.linkedinsignup')) { echo $settings->getSetting('sessociallogin.linkedinsignup'); } else { echo '0'; } ?>],
      ['Instagram', <?php if($settings->getSetting('sessociallogin.instagramsignup')) { echo $settings->getSetting('sessociallogin.instagramsignup'); } else { echo '0'; } ?>],
      ['Flickr', <?php if($settings->getSetting('sessociallogin.flickrsignup')) { echo $settings->getSetting('sessociallogin.flickrsignup'); } else { echo '0'; } ?>], 
      ['Hotmail', <?php if($settings->getSetting('sessociallogin.hotmailsignup')) { echo $settings->getSetting('sessociallogin.hotmailsignup'); } else { echo '0'; } ?>],
      ['VK', <?php if($settings->getSetting('sessociallogin.vksignup')) { echo $settings->getSetting('sessociallogin.vksignup'); } else { echo '0'; } ?>],
      ['Yahoo', <?php if($settings->getSetting('sessociallogin.yahoosignup')) { echo $settings->getSetting('sessociallogin.yahoosignup'); } else { echo '0'; } ?>],
    ]);

     var options = {
      chartArea:{left:200,top:20},
        slices: {
          0: { color: '#3B5998' },
          1: { color: '#4099FF' },
          2: { color: '#cb2027' },
          3: { color: '#DC4E41' },
          4: { color: '#0077b5' },
          5: { color: '#2E5E86' },
          6: { color: '#0063dc' },
          7: { color: '#F89839' },
          8: { color: '#436eab' },
          9: { color: '#7f40bd' },
        }
     };

    var chart = new google.visualization.PieChart(document.getElementById('piechart'));
    chart.draw(data, options);
  }
</script>