<div class="wrap">
	<h2><img src="<?php echo WP_PLUGIN_URL . '/3d-viewer-configurator/img/3d-produkt-viewer.png'; ?>" alt="3D Produkt Viewer" /></h2>

<?php
	if (($_GET['action'] == 'edit') || ($_GET['action'] == 'create'))
	{
  		if($_GET['action'] == 'edit')
    		require(str_replace('admin.php', '', __FILE__).'admin_edit.php');
  		else
    		require(str_replace('admin.php', '', __FILE__).'admin_create.php');
	}
	else
	{
  		require(str_replace('admin.php', '', __FILE__).'admin_list.php');
	}
?>
</div>
