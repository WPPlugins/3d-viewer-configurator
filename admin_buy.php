<script type="text/javascript">
	jQuery(document).ready(function() {
		// open enter license key form
		jQuery('#openregisterform_btn').click(function(e) {
			jQuery('#register').slideToggle('slow');
			e.preventDefault();
		});

		// handle form submit
		jQuery('#registerform input[type="submit"]').click(function(e) {
			// send data to the server
			var user_id = jQuery('#registerform input[name="username"]').val();
			var serial = jQuery('#registerform input[name="serial"]').val();
			
			jQuery.ajax({
				type: "POST",
				dataType: "json",
				url: jQuery('#registerform').attr('action'),
				data: { user_id: user_id, serial: serial }
			}).done(function(result) {
				if (result.success) {
					location.reload();
				}
				else {
					alert('<?php _e('The license data is not valid!', $this->localizationDomain); ?>');
				}
			});
			e.preventDefault(); // prevent form to be submtited
		});
	});
</script>

<link rel="stylesheet" href="<?php echo WP_PLUGIN_URL . '/3d-viewer-configurator/css/vtpkonfigurator.css'; ?>" type="text/css" media="all" />

<?php
if ($this->licensed):
// licensed version
?>
<p style="font-weight: bold;">
	<?php _e('Thank you for using the PRO version of the 3D Produkt Viewer!', $this->localizationDomain); ?> -
	<?php _e('Registered to: ', $this->localizationDomain); echo '<i>'.$this->registered_to.'</i>'; ?>
</p>

<?php
else:
// display buying options
?>
<div id="buy">
	<p><?php _e('Thank you for using the 3D Produkt Viewer FREE!', $this->localizationDomain); ?></p>
	<p><?php _e('Upgrade to the PRO Version now:', $this->localizationDomain); ?></p>
	
	<table cellpadding="0" cellspacing="0">
		<tr>
			<th style="width: 160px; text-align: left; vertical-align: top;"><?php _e('Function', $this->localizationDomain); ?></th>
			<th style="width: 180px;"><?php _e('3D Produkt Viewer', $this->localizationDomain); ?><br/>
				FREE
			</th>
			<th style="width: 180px;"><?php _e('3D Produkt Viewer', $this->localizationDomain); ?><br/>
				<b>PRO</b>
			</th>
		</tr>
		<tr class="first">
			<td></td>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td class="left"><?php _e('Item display in 3D', $this->localizationDomain); ?></td>
			<td class="tick-grey"></td>
			<td class="tick-green"></td>
		</tr>
		<tr>
			<td class="left"><?php _e('Interactive', $this->localizationDomain); ?></td>
			<td class="tick-grey"></td>
			<td class="tick-green"></td>
		</tr>
		<tr>
			<td class="left"><?php _e('Zoom', $this->localizationDomain); ?></td>
			<td class="tick-grey"></td>
			<td class="tick-green"></td>
		</tr>
		<tr>
			<td class="left"><?php _e('Suitable for eShop', $this->localizationDomain); ?></td>
			<td class="tick-grey"></td>
			<td class="tick-green"></td>
		</tr>
		<tr>
			<td class="left"><?php _e('Number of products', $this->localizationDomain); ?></td>
			<td class="red">1</td>
			<td class="green"><b><?php _e('Unlimited', $this->localizationDomain); ?></b></td>
		</tr>
		<tr>
			<td class="left"><?php _e('Number of variants', $this->localizationDomain); ?></td>
			<td class="red">5</td>
			<td class="green"><b><?php _e('Unlimited', $this->localizationDomain); ?></b></td>
		</tr>
		<tr>
			<td class="left"><?php _e('Price', $this->localizationDomain); ?></td>
			<td class="green"><b><?php _e('Free', $this->localizationDomain); ?></b></td>
			<td class="green"><b>79 <?php _e('Euro', $this->localizationDomain); ?></b></td>
		</tr>
	</table>

	<div style="margin: 15px 0 0 380px;">
		<form action="<?php echo $main_link; ?>&action=paypal" method="POST">
			<input type="hidden" name="paymentType" value="Sale" />
			<input type="hidden" name="totalAmount" value="79.00" />
			<input type="hidden" name="currencyCodeType" value="EUR" />
			<input type="image" name="submit" src="https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif" />
		</form>
<?php
// Donate link
/*		<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<input type="hidden" name="cmd" value="_s-xclick" />
			<input type="hidden" name="hosted_button_id" value="SW8D4VEGBG34N" />
			<input type="image" src="https://www.paypalobjects.com/de_DE/DE/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="Jetzt einfach, schnell und sicher online bezahlen – mit PayPal." />
			<img alt="" border="0" src="https://www.paypalobjects.com/de_DE/i/scr/pixel.gif" width="1" height="1" />
		</form>
*/
?>
	</div>
</div> <!-- /buy -->

<br style="clear: both;" />
<?php
endif;
// end display upgrade table for free version
?>

<?php if ( ! $this->licensed): ?>
	<a id="openregisterform_btn" class="button-secondary" style="position: absolute; margin-top: -40px;" href="#" title="<?php _e('Enter license key', $this->localizationDomain); ?>"><?php _e('Enter license key', $this->localizationDomain); ?></a>
	<div id="register">
		<form action="<?php echo $main_link_ajax; ?>&action=register" name="registerform" id="registerform">
		<table>
			<tr>
				<td>Username:</td>
				<td><input type="text" name="username" /></td>
			</tr>
			<tr>
				<td>Serial:</td>
				<td><input type="text" name="serial" /></td>
			</tr>
			<tr>
				<td></td>
				<td><input type="submit" value="Register" /></td>
			</tr>
		</table>
		</form>
	</div>
<?php endif; ?>

<p style="margin-bottom: 40px;">
	<a href="http://3d-konfigurator.eu/" class="btn" style="margin-right: 20px;"><?php _e('3D Configurator Homepage', $this->localizationDomain); ?></a>
	<a href="http://produktbilder-fuer-onlineshops.de/angebotsformular/" class="btn" style="margin-right: 20px;"><?php _e('Create New Product Pictures', $this->localizationDomain); ?></a>
	<a href="http://www.visualtektur.de/" class="btn"><?php _e('3D Agency', $this->localizationDomain); ?></a>
</p>


