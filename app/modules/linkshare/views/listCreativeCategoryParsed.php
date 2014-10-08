<?=$this->load->view(branded_view('cp/header'));?>
<div style="width:1400px;text-align:center;" align="center">
	<?=$this->dataset->table_head();?>
	<?
		
	if (!empty($this->dataset->data)) {
		foreach ($this->dataset->data as $row) {
                if(isset($row['cate']))  
                {
		?>
			<tr>						
				<td align="center"><?=$row['cate'];?> categorii parsate</td>
                                <td align="center"><?=$row['site'];?></td>                             
			</tr>
		<?
                }
		 }
	}
	else {
	?>
	<tr>
		<td colspan="7">Nu sunt categorii creative parsate.</td>
	</tr>
	<? } ?>
	<?=$this->dataset->table_close();?>
</div>
<?=$this->load->view(branded_view('cp/footer'));?>