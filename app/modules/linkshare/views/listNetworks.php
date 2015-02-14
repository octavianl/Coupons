<?=$this->load->view(branded_view('cp/header'));?>
<h1>Networks list : according to <a href="https://rakutenlinkshare.zendesk.com/hc/en-us/articles/202022528-LinkLocator-Direct-Network-ID-NID-Table" target="_blank">Linkshare Article</a></h1>
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
					<a href="<?=site_url('admincp/linkshare/editNetwork/' . $row['id']);?>">edit</a> 				
				</td>
			</tr>
		<?
		}
	}
	else {
	?>
	<tr>
		<td colspan="7">No networks available.</td>
	</tr>
	<? } ?>
	<?=$this->dataset->table_close();?>
</div>
<?=$this->load->view(branded_view('cp/footer'));?>