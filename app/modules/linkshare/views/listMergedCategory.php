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
    <?		
    if (!empty($this->dataset->data)) {
    foreach ($this->dataset->data as $row) {

    ?>
    <tr>			
        <td><input type="checkbox" name="check_<?= $row['category_merged_ID']; ?>" value="1" class="action_items" /></td>
        <td><?= $row['category_merged_name']; ?></td>
        <td><?= $row['count_merged']; ?></td>
        <td>
            <?php
            foreach ($row['categories_merged'] as $categ) {
                $delete_link = site_url('admincp2/linkshare/ajaxDeleteCategory/' . $categ['cat_id'] . '/' . $row['category_merged_ID']);
                $see_link = site_url('admincp3/linkshare/listProducts/' . $categ['mid'] . '/' . $categ['cat_id']);
                echo "<div style='margin:7px 0;'>" . $categ['cat_id'] . "&nbsp; <button id='DeleteLink" . $categ['cat_id'] . "' value='" . $delete_link . "'>delete this category id</button>";
                echo "&nbsp; <a href='" . $see_link . "' target='_blank'>See products</a> &nbsp; Total parsed products : ".$categ['nr_products']."</div>";
            }

            ?>
        </td>	
        <td><?= $row['nr_products']; ?></td>
        <td class="options" align="center">
            <a href="<?= site_url('admincp2/linkshare/editMergedCategory/' . $row['category_merged_ID']); ?>">edit category name</a>
        </td>
    </tr>
    <?
    }
    }
    else {
    ?>
    <tr>
        <td colspan="7">NO Merged Categories</td>
    </tr>
    <? } ?>
    <?= $this->dataset->table_close(); ?>
</div>
<?= $this->load->view(branded_view('cp/footer')); ?>