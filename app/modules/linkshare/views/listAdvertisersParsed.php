<?php echo $this->load->view(branded_view('cp/header')); ?>
<h1>Temporary Advertisers for <?php echo $site_name; ?>
<h4><input type="button" value="ALL APROVED"  onclick="javascript:location.href='admincp/linkshare/parseAdvertisers/1'"/></h4>
<h4>Select advertisers status</h4>
    <form method="GET" action="admincp/linkshare/parseAdvertisers/">
    <select name="status">
        <?php
            foreach ($allStatus as $status){
            ?>
                <option value="<?php echo $status['id_status']?>" <?php if(isset($selectStatus) && $selectStatus == $status['name']){ echo 'selected';} ?> ><?php echo $status['id_status']; ?></option>
            <?php
            }
        ?>
    </select>
    <button type="submit">Parse</button>
    </form>
</h1>
<div align="center">
	<?php echo $this->dataset->table_head();?>
	<?php if (!empty($this->dataset->data)) {
		foreach ($this->dataset->data as $row) {
		?>
                    <tr>							
                        <td align="center"><?=$row['id'];?></td>
                        <td align="center"><?=$row['name_site'];?></td>
                        <td align="center"><?=$row['id_status'];?></td>
                        <td align="center"><?=$row['status'];?></td>
                        <td align="center"><?=$row['id_categories'];?></td>
                        <td align="center"><?=$row['mid'];?></td>
                        <td align="center"><?=$row['name'];?></td>                                
                        <td align="center"><?=$row['offer_also'];?></td>
                        <td align="center"><?=$row['commission'];?></td>
                        <td align="center"><?=$row['offer_id'];?></td>
                        <td align="center"><?=$row['offer_name'];?></td>
                    </tr>
		<?php
		}
	}
	else {
	?>
	<tr>
		<td colspan="11">No parsed advertisers available.</td>
	</tr>
	<?php } ?>
	<?php echo $this->dataset->table_close(); ?>
</div>
<?php echo $this->load->view(branded_view('cp/footer'));?>