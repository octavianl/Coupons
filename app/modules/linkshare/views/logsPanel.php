<?=$this->load->view(branded_view('cp/header'));?>
<h1>Logs Panel </h1>
<div>
	<?=$this->dataset->table_head();?>
	<?

	if (!empty($this->dataset->data)) {
		foreach ($this->dataset->data as $row) {
			$color = "";
			if($row[2]['LogLevel'] === "ERROR"){$color = "#EEA2A2";}
			if($row[2]['LogLevel'] === "WARN"){$color = "#EED859";}
			if($row[2]['LogLevel'] === "INFO"){$color = "#60AAE8";}

		?>

			<tr>			
				<td align="left" style="background-color:<?=$color;?> !important;"><?=$row[0]['RowNo'];?></td>                                
				<td align="left" style="background-color:<?=$color;?> !important;"><?=$row[1]['DateTime'];?></td>
                <td align="left" style="background-color:<?=$color;?> !important;"><?=$row[2]['LogLevel'];?></td>
                <td align="left" style="background-color:<?=$color;?> !important;"><?=$row[3]['Message'];?></td>
			</tr>
		<?

		}
	}
	else {
	?>
	<tr>
		<td colspan="7">No logs available.</td>
	</tr>
	<? } ?>
	<?=$this->dataset->table_close();?>
</div>

<?=$this->load->view(branded_view('cp/footer'));?>