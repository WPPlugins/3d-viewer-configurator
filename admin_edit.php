<h3>
<?php
if ($_GET['action'] == 'edit') _e('Edit configurator', $this->localizationDomain);
else _e('Create configurator', $this->localizationDomain);
?> 
</h3>
<div style="float: left; width: 470px;">
<form name="edit_form" method="POST" action="<?php echo $main_link; ?>&action=edit&vid=<?php echo $item['v_id']; ?>" enctype="multipart/form-data">
  <input type="hidden" name="subaction" value="edit" />
  <input type="hidden" name="f_id" value="<?php echo $item['v_id']; ?>" />
  <table cellspacing="6" cellpadding="6">
  	<tr>
  		<td colspan="2"><b><?php echo _e('Configurator settings:', $this->localizationDomain); ?></b></td>
  	</tr>
    <tr>
      <td width="120"><label for="f_name"><?php _e('Name', $this->localizationDomain); ?></label></td><td><input id="f_name" size="40" maxlength="255" type="text" name="f_name" title="<?php _e('Internal name for configurator.', $this->localizationDomain); ?>" value="<?php echo $item['v_name']; ?>" /></td>
    </tr>
    <tr>
      <td valign="top"><label for="f_options"><?php _e('Options', $this->localizationDomain); ?></label></td>
      <td>
        <input type="checkbox" id="f_invert" name="f_options[]" value="invert"<?php echo @in_array('invert', $item['options'])?' checked':''; ?> /> <label for="f_invert"><?php _e('Invert order of images', $this->localizationDomain); ?></label><br />
        <input type="checkbox" id="f_play" name="f_options[]" value="play"<?php echo @in_array('play', $item['options'])?' checked':''; ?> />  <label for="f_play"><?php _e('Start rotation automatically', $this->localizationDomain); ?></label>
      </td>
    </tr>
    <tr>
      <td><label for="f_rpm"><?php _e('Speed', $this->localizationDomain); ?></label></td><td><input id="f_rpm" type="text" size="10" name="f_rpm" title="<?php _e('Speed of the rotation.', $this->localizationDomain); ?>" value="<?php echo $item['v_rpm']; ?>" /></td>
    </tr>
    <tr>
      <td></td>
      <td>
        <input class="button-primary" type="submit" name="f_ok" title="<?php _e('OK', $this->localizationDomain); ?>" value="<?php _e('OK', $this->localizationDomain); ?>" id="submitbutton" />
        <a class="button-secondary" href="<?php echo $main_link; ?>" title="<?php _e('Cancel', $this->localizationDomain); ?>"><?php _e('Cancel', $this->localizationDomain); ?></a>
      </td>
    </tr>
    <tr>
    	<td colspan="2">
    		<h3><?php echo _e('Option 1: Upload the whole file structure', $this->localizationDomain); ?></h3>
    	</td>
    </tr>
    <tr>
      <td valign="top">
        <label for="f_file"><?php _e('File', $this->localizationDomain); ?></label></td><td><input id="f_file" type="file" name="f_files[]" title="<?php _e('You can select one zip file.', $this->localizationDomain); ?>" />
        <br />
        <a href="#" onclick="jQuery('#conf-fileupload-complete-help').toggle('fast'); return false;"><b><?php _e('Show/hide example', $this->localizationDomain); ?></b></a>
        <div id="conf-fileupload-complete-help" style="display: none;">
          <?php _e('Zip archive, file structure example', $this->localizationDomain); ?>:<br />
          <b>configuration1/</b> <i><?php _e('(1st configuration main folder)', $this->localizationDomain); ?></i><br />
          -- <b>view/</b> <i><?php _e('(pictures of 1st configuration)', $this->localizationDomain); ?></i><br />
          -- -- <b>zoom/</b> <i><?php _e('(zoom pictures of 1st configuration)', $this->localizationDomain); ?></i><br />
          -- -- -- img0.jpg<br />
          -- -- -- img1.jpg<br />
          -- -- img0.jpg<br />
          -- -- img1.jpg<br />
          -- thumb.jpg<br />
          <b>configuration2/</b> <i><?php _e('(2nd configuration main folder)', $this->localizationDomain); ?></i><br />
          -- ...
        </div>
      </td>
    </tr>
    <tr>
      <td></td>
      <td>
        <input class="button-primary" type="submit" name="f_ok" title="<?php _e('OK', $this->localizationDomain); ?>" value="<?php _e('OK', $this->localizationDomain); ?>" id="submitbutton" />
        <a class="button-secondary" href="<?php echo $main_link; ?>" title="<?php _e('Cancel', $this->localizationDomain); ?>"><?php _e('Cancel', $this->localizationDomain); ?></a>
      </td>
    </tr>
  </table>  
</form>
</div>


<?php 
include('admin_buy.php');
?>

<h3 style="margin-left: 6px; clear: both;"><?php echo _e('Option 2: Manage configurations directly', $this->localizationDomain); ?></h3>

<form action="<?php echo $main_link; ?>&action=edit&vid=<?php echo $item['v_id']; ?>" method="post">
  <input type="hidden" name="subaction" value="create_configuration" />
  <input type="hidden" name="f_id" value="<?php echo $item['v_id']; ?>" />
  <div class="metabox-holder" style="margin-left: 6px;">
      <div class="stuffbox">
      <h3><label for="confname"><?php echo _e('Configuration name:', $this->localizationDomain); ?></label></h3>
      <div class="inside" style="padding: 5px;">
      	<input type="text" id="confname" value="" name="confname" /> <input type="submit" value="<?php echo _e('Create configuration', $this->localizationDomain);?>" class="button-primary" />
          <p>Use <code>lower case characters</code>, <code>numbers</code>, <code>dashes (-)</code> and <code>underscores (_)</code> only.</p>
      </div>
    	</div>
  </div>
</form>

<div style="margin-left: 6px;">
  	<table class="widefat fixed configurations" id="configurations-table">
  	  <thead>
  	  	<tr>
  	  		<th colspan="4">
  	  			<?php echo _e('Current Configurations:', $this->localizationDomain); ?>
  	  		</th>
  	  	</tr>
  	  </thead>
  	  <tbody>
          <?php if(count($configurations) == 0): ?>
            <tr>
              	<td colspan="4">
          	<?php echo _e('No configurations found.', $this->localizationDomain); ?>
          		</td>
          	</tr>
          <?php endif; ?>
          
          <?php foreach($configurations AS $configuration): ?>
            <tr>
            	<td><b><?php echo $configuration['name']; ?></b><br />
            	<div class="row-actions"><span class='delete'><a class='submitdelete' href='<?php echo $main_link.'&action=edit&vid='.$item['v_id'].'&confname='.urlencode($configuration['name']).'&subaction=delete_configuration'; ?>' onclick="if ( confirm( 'You are about to delete this configuration\n  \'Cancel\' to stop, \'OK\' to delete.' ) ) { return true;}return false;">Delete</a></span></div></td>
            	<td>
            		<input type="button" value="<?php echo _e('Zoom pictures', $this->localizationDomain); ?>" class="button-secondary" />
            		<?php if(isset($_POST['subaction']) && $_POST['subaction'] == 'upload_zoom' && $_POST['confname'] == $configuration['name'] && count($errors) > 0): ?>
            		<div>
            			<b>Upload errors:</b><br />
            			<ul>
            				<?php foreach($errors AS $error): ?>
            				  <li><?php echo $error; ?></li>
            				<?php endforeach; ?>
            			</ul>
            		</div>
            		<?php endif; ?>
            		<div style="display: <?php echo isset($_REQUEST['confname']) && $_REQUEST['confname'] == $configuration['name'] && isset($_REQUEST['subaction']) && $_REQUEST['subaction'] == 'delete_zoom_picture' ? 'block' : 'none'; ?>;">
            			<form action="<?php echo $main_link; ?>&action=edit&vid=<?php echo $item['v_id']; ?>" method="post" enctype="multipart/form-data">
            				<input type="hidden" name="vid" value="<?php echo $item['v_id']; ?>" />
            				<input type="hidden" name="confname" value="<?php echo $configuration['name']; ?>" />
            				<input type="hidden" name="subaction" value="upload_zoom" />
            				<input type="file" name="files[]" multiple="multiple" /> <input type="submit" class="button-primary" value="Upload" />
            			</form>
            		  	<ul>
            		  	<?php if(count($configuration['zoom_pictures']) == 0): ?>
            		  		<li><?php echo _e('No pictures found.', $this->localizationDomain); ?></li>
            		  	<?php else: ?>
            		  	<li><span class="delete"><a class="deletesubmit" href="<?php echo $main_link; ?>&action=edit&vid=<?php echo $item['v_id']; ?>&subaction=delete_zoom_picture&confname=<?php echo $configuration['name']; ?>&all=1" onclick="return confirm('You are about to delete all pictures. \n \'Cancel\' to stop, \'OK\' to delete.');"><?php _e('Delete all pictures', $this->localizationDomain); ?></a></span></li>
            		  	<?php endif; ?>
            			<?php foreach($configuration['zoom_pictures'] AS $pic): ?>
            			  	<li><span class="delete"><a class="submitdelete" href="<?php echo $main_link; ?>&action=edit&vid=<?php echo $item['v_id']; ?>&subaction=delete_zoom_picture&confname=<?php echo $configuration['name']; ?>&picture=<?php echo urlencode($pic); ?>" onclick="return confirm('You are about to delete this picture. \n \'Cancel\' to stop, \'OK\' to delete.');">X</a></span> <a href="<?php echo $this->plugin_url.'/data/'.$item['v_id'].'/'.$configuration['name'].'/view/zoom/'.$pic; ?>" onclick="window.open(this.href); return false;"><?php echo $pic; ?></a></li> 
            			<?php endforeach; ?>
            			</ul>
            		</div>
            	</td>
            	<td>
            		<input type="button" value="<?php echo _e('360° pictures', $this->localizationDomain); ?>" class="button-secondary" />
            		<?php if(isset($_POST['subaction']) && $_POST['subaction'] == 'upload_normal' && $_POST['confname'] == $configuration['name'] && count($errors) > 0): ?>
            		<div>
            			<b>Upload errors:</b><br />
            			<ul>
            				<?php foreach($errors AS $error): ?>
            				  <li><?php echo $error; ?></li>
            				<?php endforeach; ?>
            			</ul>
            		</div>
            		<?php endif; ?>
            		<div style="display: <?php echo (isset($_REQUEST['confname']) && $_REQUEST['confname'] == $configuration['name'] && isset($_REQUEST['subaction']) && $_REQUEST['subaction'] == 'delete_normal_picture') ? 'block' : 'none'; ?>;">
            			<form action="<?php echo $main_link; ?>&action=edit&vid=<?php echo $item['v_id']; ?>" method="post" enctype="multipart/form-data">
            				<input type="hidden" name="vid" value="<?php echo $item['v_id']; ?>" />
            				<input type="hidden" name="confname" value="<?php echo $configuration['name']; ?>" />
            				<input type="hidden" name="subaction" value="upload_normal" />
            				<input type="file" name="files[]" multiple="multiple" /> <input type="submit" class="button-primary" value="Upload" />
            			</form>
            		  	<ul>
            		  	<?php if(count($configuration['normal_pictures']) == 0): ?>
          		  			<li><?php echo _e('No pictures found.', $this->localizationDomain); ?></li>
          		  		<?php else: ?>
            		  		<li><span class="delete"><a class="deletesubmit" href="<?php echo $main_link; ?>&action=edit&vid=<?php echo $item['v_id']; ?>&subaction=delete_normal_picture&confname=<?php echo $configuration['name']; ?>&all=1" onclick="return confirm('You are about to delete all pictures. \n \'Cancel\' to stop, \'OK\' to delete.');"><?php _e('Delete all pictures', $this->localizationDomain); ?></a></span></li>
            		  	<?php endif; ?>
            			<?php foreach($configuration['normal_pictures'] AS $pic): ?>
            			  	<li><span class="delete"><a class="submitdelete" href="<?php echo $main_link; ?>&action=edit&vid=<?php echo $item['v_id']; ?>&subaction=delete_normal_picture&confname=<?php echo $configuration['name']; ?>&picture=<?php echo urlencode($pic); ?>" onclick="return confirm('You are about to delete this picture. \n \'Cancel\' to stop, \'OK\' to delete.');">X</a></span> <a href="<?php echo $this->plugin_url.'/data/'.$item['v_id'].'/'.$configuration['name'].'/view/'.$pic; ?>" onclick="window.open(this.href); return false;"><?php echo $pic; ?></a></li> 
            			<?php endforeach; ?>
            			</ul>
            		</div>
            	</td>
            	<td>
            		<input type="button" value="<?php echo _e('Thumbnail', $this->localizationDomain); ?>" class="button-secondary" />
            		<?php if(isset($_POST['subaction']) && $_POST['subaction'] == 'upload_thumbnail' && $_POST['confname'] == $configuration['name'] && count($errors) > 0): ?>
            		<div>
            			<b><?php _e('Upload errors:', $this->localizationDomain); ?></b><br />
            			<ul>
            				<?php foreach($errors AS $error): ?>
            				  <li><?php echo $error; ?></li>
            				<?php endforeach; ?>
            			</ul>
            		</div>
            		<?php endif; ?>
            		<div style="display: <?php echo isset($_REQUEST['confname']) && $_REQUEST['confname'] == $configuration['name'] && isset($_REQUEST['subaction']) && $_REQUEST['subaction'] == 'delete_thumbnail' ? 'block' : 'none'; ?>;">
            			<form action="<?php echo $main_link; ?>&action=edit&vid=<?php echo $item['v_id']; ?>" method="post" enctype="multipart/form-data">
            				<input type="hidden" name="vid" value="<?php echo $item['v_id']; ?>" />
            				<input type="hidden" name="confname" value="<?php echo $configuration['name']; ?>" />
            				<input type="hidden" name="subaction" value="upload_thumbnail" />
            				<input type="file" name="files[]" /> <input type="submit" class="button-primary" value="Upload" />
            			</form>
            		  	<ul>
            		  		<?php if( ! empty($configuration['thumbnail'])): ?>
            			  	<li><span class="delete"><a class="submitdelete" href="<?php echo $main_link; ?>&action=edit&vid=<?php echo $item['v_id']; ?>&subaction=delete_thumbnail&confname=<?php echo $configuration['name']; ?>" onclick="return confirm('You are about to delete this thumbnail. \n \'Cancel\' to stop, \'OK\' to delete.');">X</a></span> <a href="<?php echo $this->plugin_url.'/data/'.$item['v_id'].'/'.$configuration['name'].'/'.$configuration['thumbnail']; ?>" onclick="window.open(this.href); return false;"><?php echo $configuration['thumbnail']; ?></a></li> 
            				<?php else: ?>
            				<li><?php echo _e('No thumbnail found.', $this->localizationDomain); ?></li>
            				<?php endif; ?>
            			</ul>
            		</div>
            	</td>
            </tr>
          <?php endforeach; ?>
      </tbody>
	</table>
</div>

<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery('#configurations-table tbody tr td input[type=button]').click(function() {
			// Scope: clicked button
			// Go up, fetch the div and toggle it
			jQuery('div', this.parentNode).toggle('fast');
		});
	});
</script>