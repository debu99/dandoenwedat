
<?php include APPLICATION_PATH .  '/application/modules/Sesevent/views/scripts/dismiss_message.tpl';?>

<div class='settings'>
  <form class="global_form">
    <div>
      <h3><?php echo $this->translate("Events Statistics") ?> </h3>
      <p class="description">
        <?php echo $this->translate("Below are some valuable statistics for the Events created on this site:"); ?>
      </p>
      <table class='admin_table' style="width: 50%;">
        <tbody>
          <tr>
            <td><strong class="bold"><?php echo "Total Events:" ?></strong></td>
            <td><?php echo $this->totalevent ? $this->totalevent : 0; ?></td>
          </tr>
           <tr>
            <td><strong class="bold"><?php echo "Total Approved Events:" ?></strong></td>
            <td><?php echo $this->totalapprovedevent ? $this->totalapprovedevent : 0; ?></td>
          </tr>
          <tr>
            <td><strong class="bold"><?php echo "Total Featured Events:" ?></strong></td>
            <td><?php echo $this->totaleventfeatured ? $this->totaleventfeatured : 0; ?></td>
          </tr>
          <tr>
            <td><strong class="bold"><?php echo "Total Sponsored Events:" ?></strong></td>
            <td><?php echo $this->totalEventsponsored ? $this->totalEventsponsored : 0; ?></td>
          </tr>
          <tr>
            <td><strong class="bold"><?php echo "Total Verified Events:" ?></strong></td>
            <td><?php echo $this->totaleventverified ? $this->totaleventverified : 0; ?></td>
          </tr>
          <tr>
            <td><strong class="bold"><?php echo "Total event Albums:" ?></strong></td>
            <td><?php echo $this->totalalbums ? $this->totalalbums : 0; ?></td>
          </tr>
          <tr>
            <td><strong class="bold"><?php echo "Total event Photos:" ?></strong></td>
            <td><?php echo $this->totalphotos ? $this->totalphotos : 0; ?></td>
          </tr>
          <?php if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesvideo')):?>
						<tr>
							<td><strong class="bold"><?php echo "Total event Videos:" ?></strong></td>
							<td><?php echo $this->totalvideos ? $this->totalvideos : 0; ?></td>
						</tr>
          <?php endif;?>
          <tr>
            <td><strong class="bold"><?php echo "Total Reviews:" ?></strong></td>
            <td><?php echo $this->totaleventrated ? $this->totaleventrated : 0; ?></td>
          </tr>  
          <tr>
            <td><strong class="bold"><?php echo "Total Favourite Events:" ?></strong></td>
            <td><?php echo $this->totaleventfavourite ? $this->totaleventfavourite : 0; ?></td>
          </tr>
          <tr>
            <td><strong class="bold"><?php echo "Total Comments:" ?></strong></td>
            <td><?php echo $this->totaleventcomments ? $this->totaleventcomments : 0; ?></td>
          </tr>
          <tr>
            <td><strong class="bold"><?php echo "Total Views:" ?></strong></td>
            <td><?php echo $this->totaleventviews ? $this->totaleventviews : 0; ?></td>
          </tr>
          <tr>
            <td><strong class="bold"><?php echo "Total Likes:" ?></strong></td>
            <td><?php echo $this->totaleventlikes ? $this->totaleventlikes : 0; ?></td>
          </tr>        
        </tbody>
      </table>
    </div>
  </form>
</div>