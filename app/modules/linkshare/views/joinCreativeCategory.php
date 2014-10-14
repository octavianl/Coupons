<?=$this->load->view(branded_view('cp/header'));?>
<h1>Join Creative Categories</h1>
<div><strong>INSTRUCTION</strong>: First search a keyword on Name filter, chose category ( checkboxes ) you want to merge then write a name for the NEW category and press SAVE !<br/><br/></div>

<?=$this->dataset->table_head();?>
<form>
    	<?
	if (!empty($this->dataset->data)) {
		foreach ($this->dataset->data as $row) {
		?>
			<tr>			
				<td align="left"><?=$row['id'];?></td>
                                <td align="left"><input type="checkbox" name="check_category[]" value="<?=$row['cat_id']?>" class="action_items" ></td>
                                <td align="left"><?=$row['id_site'];?></td>
                                <td align="left"><?=$row['cat_id'];?></td>
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
            <td colspan="8" style="background-color: #B9E2FA;"><div><span style="font-size: 16px; font-weight:bold; ">Chose a name for the new category: </span><input type="text" name="merged_category"><button type="submit">Save</button></div></td>
        </tr>
</form>
<?=$this->dataset->table_close();?>

<?=$this->load->view(branded_view('cp/footer'));?>