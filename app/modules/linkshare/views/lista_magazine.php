<?=$this->load->view(branded_view('cp/header'));?>
<br/><br/><br/>
<h1>Lista magazine</h1>
<div style="width:1800px;text-align:center;" align="center">
	<?=$this->dataset->table_head();?>
	<?
		
	if (!empty($this->dataset->data)) {
		foreach ($this->dataset->data as $row) {
		?>
			<tr>			
				<td><input type="checkbox" name="check_<?=$row['id'];?>" value="1" class="action_items" /></td>
				<td align="center"><?=$row['id'];?></td>
                                <td align="center"><?=$row['name_site'];?></td>
                                <td align="center"><?=$row['id_status'];?></td>
                                <td align="center"><?=$row['status'];?></td>
                                <td align="center"><?=$row['id_categories'];?></td>
                                <td align="center"><?=$row['mid'];?></td>
				<td align="center"><?=$row['name'];?></td>                                
                                <td align="center"><?=$row['commission'];?></td>
                                <td align="center"><?=$row['offer_id'];?></td>
                                <td align="center"><?=$row['offer_name'];?></td>
				<td class="options" align="center">
					<a href="<?=site_url('admincp/linkshare/edit_magazin/' . $row['id']);?>">editeaza</a> 				
				</td>
                                <td align="center"><a href="<?=site_url('admincp/linkshare/parse_product_search/' . $row['id_site'].'/'.$row['mid']);?>">parseaza produse</a></td>
                                <td align="center"><a href="<?=site_url('admincp/linkshare/listProducts/'.$row['id_site'].'/' . $row['mid']);?>">vezi produse</a></td>
                                <td align="center"><?=number_format($row['nr_products'],0,'.',',');?></td>
			</tr>
		<?
		}
	}
	else {
	?>
	<tr>
		<td colspan="10">Nu sunt magazine.</td>
	</tr>
	<? } ?>
	<?=$this->dataset->table_close();?>
</div>
<?=$this->load->view(branded_view('cp/footer'));?>