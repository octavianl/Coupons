<?=$this->load->view(branded_view('cp/header'));?>
<h1>Channels List</h1>
<div style="width:1000px;text-align:center;" align="center">
	<?=$this->dataset->table_head();?>
	<?
		
	if (!empty($this->dataset->data)) {
		foreach ($this->dataset->data as $row) {
		?>
			<tr>							
				<td align="center"><?=$row['id'];?></td>
                                <td align="center"><?=$row['name'];?></td>
				<td align="center"><?=$row['token'];?></td>                               
				<td class="options" align="center">
					<a href="<?=site_url('admincp/linkshare/editSite/' . $row['id']);?>">Edit</a> 				
				</td>
                                <td class="options" align="center">
					<a href="<?=site_url('admincp/linkshare/infoSite/' . $row['id']);?>">Info</a> 				
				</td>
			</tr>
		<?
		}
	}
	else {
	?>
	<tr>
		<td colspan="7">No channels available</td>
	</tr>
	<? } ?>
	<?=$this->dataset->table_close();?>
</div>
<?=$this->load->view(branded_view('cp/footer'));?>