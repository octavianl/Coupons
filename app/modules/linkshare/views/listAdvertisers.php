<?=$this->load->view(branded_view('cp/header'));?>
<h1>Advertisers list for <?=$siteName?>
</h1>

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
                                <td align="center"><a href="<?=site_url('admincp3/linkshare/parseProductSearch/' . $row['id_site'].'/'.$row['mid']);?>">Parse products</a></td>
                                <td align="center"><a href="<?=site_url('admincp3/linkshare/listProducts/'.$row['id_site'].'/' . $row['mid']);?>">See products</a></td>
                                <td align="center"><?=number_format($row['nr_products'],0,'.',',');?></td>
                                <td class="options" align="center">
					<a href="<?=site_url('admincp/linkshare/editAdvertiser/' . $row['id']);?>">Edit</a>
				</td>
                                <td align="center"><a href="#"><img src="<?php if($row['live']==0){$liveflag = 'offline.gif';}else{$liveflag = 'live.gif';}?><?=site_url('app/modules/linkshare/assets/'.$liveflag);?>" /></a></td>
                                <td align="center"><a href="#"><img src="<?php if($row['deleted']==0){$deletedflag = 'nodelete.gif';}else{$deletedflag = 'delete.gif';}?><?=site_url('app/modules/linkshare/assets/'.$deletedflag);?>" /></a></td>
			</tr>
		<?
		}
	}
	else {
	?>
	<tr>
		<td colspan="10">No advertisers available.</td>
	</tr>
	<? } ?>
	<?=$this->dataset->table_close();?>
</div>
<?=$this->load->view(branded_view('cp/footer'));?>