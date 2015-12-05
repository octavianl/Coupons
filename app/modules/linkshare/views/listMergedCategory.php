<?= $this->load->view(branded_view('cp/header')); ?>
<script>
    jQuery(document).ready(function () {
        $('button[id^="DeleteLink"]').click(function (event) {
            event.preventDefault();
            var url = $(this).val();
            window.location.replace(url)
            //alert(url);

        });

    });
</script>
<h1>List Merged Categories for <?= $site_name ?></h1>
<div>
    <?= $this->dataset->table_head(); ?>
    <?php		
    if (!empty($this->dataset->data)) {
    foreach ($this->dataset->data as $row) {
    
    $edit_link = site_url('admincp2/linkshare/joinCreativeCategory/edit/' . $row['category_merged_ID']);
    $export_joins_link = site_url('admincp3/linkshare/exportJoinsCategoriesCSV/' . $row['category_merged_ID']);
    $export_joins_products_link = site_url('admincp3/linkshare/exportJoinsProductsCSV/' . $row['category_merged_ID']);
    ?>
    <tr>			
        <td><input type="checkbox" name="check_<?= $row['category_merged_ID']; ?>" value="1" class="action_items" /></td>
        <td><a href="<?php echo $edit_link; ?>"><?= $row['category_merged_name']; ?></a><br><br>
        <a href="<?php echo $export_joins_link; ?>">EXPORT JOINS</a><br>
        <a href="<?php echo $export_joins_products_link; ?>">EXPORT JOINS PRODUCTS</a></td>
        <td><?= $row['count_merged']; ?></td>
        <td>
            <?php            
            echo '<table>';
            foreach ($row['categories_merged'] as $categ) {
                $delete_link = site_url('admincp2/linkshare/ajaxDeleteCategory/' . $categ['cat_id'] . '/' . $row['category_merged_ID']);
                $see_link = site_url('admincp3/linkshare/listProducts/' . $categ['mid'] . '/' . $categ['cat_id']);                
                echo '<tr>';
                echo "<td width='50%'>" . $categ['name'] . '</td><td>' . $categ['cat_id'] . "</td><td><button id='DeleteLink" . $categ['cat_id'] . "' value='" . $delete_link . "'>delete this category id</button></td>";
                echo "<td><a href='" . $see_link . "' target='_blank'>See products</a> &nbsp; Total parsed products : ".$categ['nr_products']."</td>";
                echo '</tr>';
            }
            echo '</table>';
            ?>
        </td>	
        <td><?= $row['nr_products']; ?></td>
        <td class="options" align="center">
            <a href="<?= site_url('admincp2/linkshare/editMergedCategory/' . $row['category_merged_ID']); ?>">edit category name</a>
        </td>
    </tr>
    <?php
    }
    }
    else {
    ?>
    <tr>
        <td colspan="7">NO Merged Categories</td>
    </tr>
    <?php } ?>
    <?= $this->dataset->table_close(); ?>
</div>
<?= $this->load->view(branded_view('cp/footer')); ?>