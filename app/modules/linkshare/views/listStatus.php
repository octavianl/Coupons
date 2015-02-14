<?=$this->load->view(branded_view('cp/header'));?>
<h1>Status list according to : <a href="https://rakutenlinkshare.zendesk.com/hc/en-us/articles/201078137-LinkLocator-Direct-Application-Status-Table" target="_blank">Linkshare Article</a></h1>
<div style="width:1400px;text-align:center;" align="center">
	<?=$this->dataset->table_head();?>
	<?
		
	if (!empty($this->dataset->data)) {
		foreach ($this->dataset->data as $row) {
		?>
			<tr>			
				<td><input type="checkbox" name="check_<?=$row['id'];?>" value="1" class="action_items" /></td>
				<td align="center"><?=$row['id'];?></td>                                
				<td align="center"><?=$row['id_status'];?></td>
            <td align="center"><?=$row['name'];?></td>
            <td align="center"><?=$row['description'];?></td>
				<td class="options" align="center">
					<a href="<?=site_url('admincp/linkshare/editStatus/' . $row['id']);?>">Edit</a> 				
				</td>
			</tr>
		<?
		}
	}
	else {
	?>
	<tr>
		<td colspan="7">No status present.</td>
	</tr>
	<? } ?>
	<?=$this->dataset->table_close();?>
</div>
<?=$this->load->view(branded_view('cp/footer'));?>