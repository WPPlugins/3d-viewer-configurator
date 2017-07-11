<h3>
<?php
if ($_GET['action'] == 'edit') 
	_e('Edit configurator', $this->localizationDomain);
else
	_e('Create new configurator', $this->localizationDomain);
?> 
</h3>
<div style="float: left; width: 470px;">
<form name="edit_form" method="POST" action="<?php echo $main_link; ?>" enctype="multipart/form-data">
  <input type="hidden" name="subaction" value="edit" />
  <input type="hidden" name="f_id" value="<?php echo $item['v_id']; ?>" />
  <table cellspacing="6" cellpadding="6">
  	<tr>
  		<td colspan="2"><b><?php echo _e('Configurator settings:', $this->localizationDomain); ?></b></td>
  	</tr>
    <tr>
      <td width="120"><label for="f_name"><?php _e('Name', $this->localizationDomain); ?></label></td><td><input id="f_name" size="40" maxlength="255" type="text" name="f_name" title="<?php _e('Internal name for the configurator.', $this->localizationDomain); ?>" value="<?php echo $item['v_name']; ?>" /></td>
    </tr>
    <tr>
      <td valign="top"><label for="f_options"><?php _e('Options', $this->localizationDomain); ?></label></td>
      <td>
        <input type="checkbox" id="f_invert" name="f_options[]" value="invert"<?php echo @in_array('invert', $item['options'])?' checked':''; ?> /> <label for="f_invert"><?php _e('Invert order of images', $this->localizationDomain); ?></label><br />
        <input type="checkbox" id="f_play" name="f_options[]" value="play"<?php echo @in_array('play', $item['options'])?' checked':''; ?> />  <label for="f_play"><?php _e('Start rotation automatically', $this->localizationDomain); ?></label>
      </td>
    </tr>
    <tr>
      <td><label for="f_rpm"><?php _e('Speed', $this->localizationDomain); ?></label></td><td><input id="f_rpm" type="text" size="10" name="f_rpm" title="<?php _e('Speed of the rotation.', $this->localizationDomain); ?>" value="<?php echo empty($item['v_rpm']) ? '30' : $item['v_rpm']; ?>" /></td>
    </tr>
    <tr>
      <td></td>
      <td>
        <input class="button-primary" type="submit" name="f_ok" title="<?php _e('OK', $this->localizationDomain); ?>" value="<?php _e('Save & go to upload', $this->localizationDomain); ?>" id="submitbutton" />
        <a class="button-secondary" href="<?php echo $main_link; ?>" title="<?php _e('Cancel', $this->localizationDomain); ?>"><?php _e('Cancel', $this->localizationDomain); ?></a>
      </td>
    </tr>
  </table>  
</form>
</div>


<?php 
include('admin_buy.php');
?>