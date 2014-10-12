<?=$this->load->view(branded_view('cp/header'));?>
<h1>Lista Advertiseri
<div style="padding: 0 0 0 40px; display:inline-block;">
    <h6>Select Site</h6>
    <form>
    <select name="sites">
        <?
            foreach ($allSites as $site){
            ?>
                        <option value="<?=$site['id']?>"><?=$site['name']?></option>
            <?
                }
        ?>
    </select>
    <button type="submit">Go</button>
    </form>
</div>
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
				<td class="options" align="center">
					<a href="<?=site_url('admincp/linkshare/editAdvertiser/' . $row['id']);?>">editeaza</a>
				</td>
                                <td align="center"><a href="<?=site_url('admincp3/linkshare/parse_product_search/' . $row['id_site'].'/'.$row['mid']);?>">parseaza produse</a></td>
                                <td align="center"><a href="<?=site_url('admincp3/linkshare/listProducts/'.$row['id_site'].'/' . $row['mid']);?>">vezi produse</a></td>
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