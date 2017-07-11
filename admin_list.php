<?php 
include('admin_buy.php');
?>

<p>
	<a class="button-secondary" href="<?php echo $main_link; ?>&action=create" title="<?php _e('Create new configurator', $this->localizationDomain); ?>"><?php _e('Create new configurator', $this->localizationDomain); ?></a>
</p>

<table class="widefat">
<thead>
    <tr>
        <th>ID</th>
        <th><?php _e('Name', $this->localizationDomain); ?></th>
        <th><?php _e('Code', $this->localizationDomain); ?></th>
        <th><?php _e('Actions', $this->localizationDomain); ?></th>
    </tr>
</thead>
<tfoot>
    <tr>
        <th>ID</th>
        <th><?php _e('Name', $this->localizationDomain); ?></th>
        <th><?php _e('Code', $this->localizationDomain); ?></th>
        <th><?php _e('Actions', $this->localizationDomain); ?></th>
    </tr>
</tfoot>
<tbody>
  <?php
  $c = 0;
  while(list(,$item) = @each($list))
  {
    $c++;
  ?>
   <tr <?php echo $c%2?' class="alternate"':''?>>
     <td><?php echo $item['v_id']; ?></td>
     <td><?php echo $item['v_name']; ?></td>
     <td>[wp_vtpkonfigurator id="<?php echo $item['v_id']; ?>"]</td>
     <td><a href="<?php echo $main_link; ?>&action=edit&vid=<?php echo $item['v_id']; ?>"><?php _e('Edit', $this->localizationDomain); ?></a> | 
     <span class="delete"><a href="<?php echo $main_link; ?>&action=delete&vid=<?php echo $item['v_id']; ?>" onclick="return window.confirm('<?php _e('Are you sure?', $this->localizationDomain); ?>');"><?php _e('Delete', $this->localizationDomain); ?></a></span></td>
   </tr>
  <?php
  }
  ?>
</tbody>
</table>