<?=$this->load->view(branded_view('cp/header'));?>
<script>
jQuery(document).ready(function(){
    $('.pagination .number a').on('click', function(event){
        event.preventDefault();
        var merged_category = $('input[name="merged_category"]').val();
        //alert(merged_category);
        //var check_category = $('input[name="check_category[]"]').val();
        var check_category = $('input[name="check_category[]"]:checked').map(function() {return this.value;}).get().join(',');
        //alert(check_category);
        $.ajax({
            type: 'post',
            url: '/admincp2/linkshare/update_filters/',
            data: 'check_category='+check_category+'&merged_category='+merged_category,
            dataType:'html',
            success: function(data, textStatus, XMLHttpRequest) {                
                console.log('zzz');
                console.log('before='+$('input[name="filters"]').val());
                //alert(data);
                //alert($('input[name="filters"]').val());
                $('input[name="filters"]').val(data);
                //alert($('input[name="filters"]').val());
                console.log('after='+$('input[name="filters"]').val());
                //console.log($("#dataset_form").attr("method"));
                document.forms['dataset_form'].submit();
            }
        });
        console.log('end='+$('input[name="filters"]').val());
        //alert($('input[name="filters"]').val());
    });
});
</script>
<h1>Join Creative Categories</h1>
<div><strong>INSTRUCTION</strong>: First search a keyword on Name filter, chose category ( checkboxes ) you want to merge then write a name for the NEW category and press SAVE !<br/><br/></div>

<?=$this->dataset->table_head();?>

    	<?
	if (!empty($this->dataset->data)) {
		foreach ($this->dataset->data as $row) {
		?>
			<tr>			
				<td align="left"><?=$row['id'];?></td>
                                <td align="left"><input type="checkbox" name="check_category[]" value="<?=$row['cat_id']?>" class="action_items" ></td>
                                <td align="left"><?=$row['id_site'];?></td>
                                <td align="left"><?=$row['cat_id'];?> 
                                    <?php if(!empty($row['merge_categories'])){ ?>
                                    <span style="color:#FF0000;">| included in: </span>
                                    <strong><?php foreach($row['merge_categories'] as $name){ echo $name." &nbsp;<span style='color:#ff0000'>/</span> &nbsp;"; }?></strong>
                                    <?php } else { echo "<span style='color:#ff0000'>|</span> Not merged!"; } ?>
                                </td>
				<td align="center"><?=$row['name'];?></td>
                                <td align="center"><?=$row['mid'];?></td>
                                <td align="left"><?=$row['nid'];?></td>
				<td  align="left" class="options">
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
        <tr>
        <tr>
            <td colspan="8" style="background-color: #B9E2FA;"><div><span style="font-size: 16px; font-weight:bold; ">Chose a name for the new category: </span><input type="text" name="merged_category"><button id="savex" type="button">Save</button></div></td>
        </tr>

<?=$this->dataset->table_close();?>

<?=$this->load->view(branded_view('cp/footer'));?>