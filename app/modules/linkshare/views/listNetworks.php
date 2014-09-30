<?=$this->load->view(branded_view('cp/header'));?>
<h1>Lista networks : conform <a href="http://helpcenter.linkshare.com/publisher/questions.php?questionid=906" target="_blank">Articol Linkshare</a></h1>
<div style="width:600px;text-align:center;" align="center">
	<?=$this->dataset->table_head();?>
	<?
		
	if (!empty($this->dataset->data)) {
		foreach ($this->dataset->data as $row) {
		?>
			<tr>			
				<td><input type="checkbox" name="check_<?=$row['id'];?>" value="1" class="action_items" /></td>
				<td align="center"><?=$row['id'];?></td>                                
				<td align="center"><?=$row['nid'];?></td>
                                <td align="center"><?=$row['name'];?></td>
				<td class="options" align="center">
					<a href="<?=site_url('admincp/linkshare/editNetwork/' . $row['id']);?>">editeaza</a> 				
				</td>
			</tr>
		<?
		}
	}
	else {
	?>
	<tr>
		<td colspan="7">Nu sunt networks.</td>
	</tr>
	<? } ?>
	<?=$this->dataset->table_close();?>
</div>
<?=$this->load->view(branded_view('cp/footer'));?>