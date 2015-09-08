<?=$this->load->view(branded_view('cp/header'));?>
<h1>Products List</h1>
<div style="width:1800px;text-align:center;" align="center">
	<?=$this->dataset->table_head();?>
	<? 
            $url = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"];
            $url = base64_encode($url);
		
	if (!empty($this->dataset->data)) {
		foreach ($this->dataset->data as $row) {
		?>
			<tr>			
				<td align="center"><input type="checkbox" name="check_<?=$row['id'];?>" value="1" class="action_items" /></td>
				<td align="center"><?=$row['id'];?></td>
                                <td align="center"><?=$row['id_site'];?></td>
                                <td align="center"><?=$row['mid'];?></td>
                                <td align="center"><?=$row['merchantname'];?></td>
                                <td align="center"><?=$row['cat_creative_id'];?></td>
                                <td align="center"><?=$row['cat_creative_name'];?></td>
                                <td align="center"><?=$row['categ_primary'];?></td>
                                <td align="center"><?=str_replace('~',' ',$row['categ_secondary']);?></td>                                                                
				<td align="center"><?=$row['productname'];?></td>			
                                <td align="center"><img src="<?=$row['imageurl'];?>" width="100" height="100"/></td>
                                <td align="center"><?=substr($row['description_short'],0,100);?>...</td>
                                <td align="center"><?=$row['price'];?></td>
                                <td align="center"><?=$row['price_list'];?></td>
                                <td align="center"><?=$row['currency'];?></td>                                
                                <td align="center"><?=$row['linkid'];?></td>                                
                                <td align="center"><?=$row['sku'];?></td>
                                <td align="center"><a href="<?=$row['click_url'];?>" target="_blank">Link</a></td>
                                <td align="center">
                                    <?php if($row['available'] == 'yes') { ?><a href="<?= 'admincp/linkshare/changeProductStatus/'.$row['id'].'/'.$url; ?>"><img src="<?=branded_include('images/ok.png');?>"/></a> <?php } ?>
                                    <?php if($row['available'] == 'no'){ ?><a href="<?= 'admincp/linkshare/changeProductStatus/'.$row['id'].'/'.$url; ?>"><img src="<?=branded_include('images/ko.png');?>"/></a> <?php } ?>
                                </td>                                
				<td class="options" align="center">
					<a href="<?=site_url('admincp/linkshare/edit_produs/' . $row['id']);?>">Edit</a> 				
				</td>
			</tr>
		<?
		}
	}
	else {
	?>
	<tr>
		<td colspan="20">No products for this advertiser <?php echo $magazin; ?>.</td>
	</tr>
	<? } ?>
	<?=$this->dataset->table_close();?>
</div>
<?=$this->load->view(branded_view('cp/footer'));?>