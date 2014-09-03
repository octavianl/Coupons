<?=$this->load->view(branded_view('cp/header'));?>
<h1>Lista site-uri</h1>
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
					<a href="<?=site_url('admincp/linkshare/edit_site/' . $row['id']);?>">editeaza</a> 				
				</td>
                                <td class="options" align="center">
					<a href="<?=site_url('admincp/linkshare/info_site/' . $row['id']);?>">info</a> 				
				</td>
			</tr>
		<?
		}
	}
	else {
	?>
	<tr>
		<td colspan="7">Nu sunt site-uri.</td>
	</tr>
	<? } ?>
	<?=$this->dataset->table_close();?>
</div>
<?=$this->load->view(branded_view('cp/footer'));?>