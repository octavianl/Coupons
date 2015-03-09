<?=$this->load->view(branded_view('cp/header'));?>
<h1>Categories list : according to <a href="http://helpcenter.linkshare.com/publisher/questions.php?questionid=709" target="_blank">Articol Linkshare</a></h1>
<div style="width:600px;text-align:center;" align="center">
	<?=$this->dataset->table_head();?>
	<?
		
	if (!empty($this->dataset->data)) {
		foreach ($this->dataset->data as $row) {
		?>
			<tr>			
				<td><input type="checkbox" name="check_<?=$row['id'];?>" value="1" class="action_items" /></td>
				<td align="center"><?=$row['id'];?></td>
                                <td align="center"><?=$row['id_category'];?></td>
				<td align="center"><?=$row['name'];?></td>			
				<td class="options" align="center">
					<a href="<?=site_url('admincp2/linkshare/editCategory/' . $row['id']);?>">Edit</a>
				</td>
			</tr>
		<?
		}
	}
	else {
	?>
	<tr>
		<td colspan="7">No categories available.</td>
	</tr>
	<? } ?>
	<?=$this->dataset->table_close();?>
</div>
<?=$this->load->view(branded_view('cp/footer'));?>