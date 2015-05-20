<?=$this->load->view(branded_view('cp/header'));?>
<h1><?=$form_title;?> <?=$site_name?></h1>
<div style="width:1800px;text-align:center;" align="center">
        <?=$this->dataset->table_head();?>
	<?
		
	if (!empty($this->dataset->data)) {
		foreach ($this->dataset->data as $row) {
		?>
			<tr>			
				<td><input type="checkbox" name="check_<?=$row['id'];?>" value="1" class="action_items" /></td>
				<td align="center"><?=$row['id'];?></td>
                                <td align="center"><?=$row['id_site'];?></td>
                                <td align="center"><?=$row['cat_id'];?></td>
				<td align="center"><?=$row['name'];?></td>
                                <td align="center"><?=$row['mid'];?></td>
                                <td align="center"><?=$row['nid'];?></td>
                                <td align="center"><a href="<?=site_url('admincp3/linkshare/parseShortProduct/' . $row['mid'].'/'.$row['cat_id']);?>">Parse products</a></td>
                                <td align="center"><a href="<?=site_url('admincp3/linkshare/listProducts/'.$row['mid'].'/' . $row['cat_id']);?>">See products</a></td>
                                <td align="center"><?=number_format($row['nr_products'],0,'.',',');?></td>
				<td class="options" align="center">
					<a href="<?=site_url('admincp2/linkshare/editCreativeCategory/' . $row['id']);?>">editeaza</a>
				</td>
			</tr>
		<?
		}
	}
	else {
	?>
	<tr>
		<td colspan="7">Nu sunt categorii creative.</td>
	</tr>
	<? } ?>
	<?=$this->dataset->table_close();?>
</div>
<?=$this->load->view(branded_view('cp/footer'));?>