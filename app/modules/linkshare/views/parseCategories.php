<?=$this->load->view(branded_view('cp/header'));?>

<div style="width:600px;text-align:center;" align="center">
    <?=$this->dataset->table_head();?>
    <?php
    if (!empty($this->dataset->data)) {        
        foreach ($this->dataset->data as $row) {            
            list ($nid, $nidName, $idCategory, $categoryName) = explode(',', $row);
            ?>
            <tr>
               <td align="center"><?=$nid;?></td>
               <td align="center"><?=$nidName;?></td>
               <td align="center"><?=$idCategory;?></td>
               <td align="center"><?=$categoryName;?></td>							
            </tr>
            <?php
        }
    } else {
        ?>
            <tr>
                <td colspan="7">No categories available.</td>
            </tr>
    <?php 
    } 
    ?>
    <?=$this->dataset->table_close();?>
</div>
<?= $this->load->view(branded_view('cp/footer')); ?>