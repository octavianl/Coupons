<?=$this->load->view(branded_view('cp/header'));?>
<h1><?=$form_title;?> <?=$site_name?></h1>
<div>
        <?=$this->dataset->table_head();?>
	<?php		
            if (!empty($this->dataset->data)) {
                foreach ($this->dataset->data as $row) {
                ?>
                    <tr>			
                        <td><input type="checkbox" name="check_<?=$row['id'];?>" value="1" class="action_items" /></td>                        
                        <td><?=$row['id'];?></td>
                        <td><?=$row['id_site'];?></td>
                        <td><?=$row['cat_id'];?></td>
                        <td><?=$row['name'];?></td>
                        <td><?=$row['mid'];?></td>
                        <td><?=$row['nid'];?></td>
                        <td><a href="<?=site_url('admincp3/linkshare/parseShortProduct/' . $row['mid'].'/'.$row['cat_id']);?>">Parse products</a></td>
                        <td><a href="<?=site_url('admincp3/linkshare/listProducts/'.$row['mid'].'/' . $row['cat_id']);?>">See products</a></td>
                        <td><?=number_format($row['nr_products'],0,'.',',');?></td>
                        <td class="options" >
                            <a href="<?=site_url('admincp2/linkshare/editCreativeCategory/' . $row['id']);?>">Edit</a>
                        </td>
                    </tr>
                <?php
                }
            }
            else {
	?>
	<tr>
            <td colspan="11">No creative categories found.</td>
	</tr>
	<?php } ?>
	<?=$this->dataset->table_close();?>
</div>
<?=$this->load->view(branded_view('cp/footer'));?>