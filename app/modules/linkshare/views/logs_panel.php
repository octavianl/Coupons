<?=$this->load->view(branded_view('cp/header'));?>
<h1>Logs Panel </h1>
<div>
	<?=$this->dataset->table_head();?>
	<?

	if (!empty($this->dataset->data)) {
		foreach ($this->dataset->data as $row) {
			$color = "";
			if($row[2]['Log Level'] === "ERROR"){$color = "#EEA2A2";}
			if($row[2]['Log Level'] === "WARN"){$color = "#EED859";}
			if($row[2]['Log Level'] === "INFO"){$color = "#60AAE8";}

		?>

			<tr>			
				<td align="left" style="background-color:<?=$color;?> !important;"><?=$row[0]['Row No'];?></td>                                
				<td align="left" style="background-color:<?=$color;?> !important;"><?=$row[1]['Date & Time'];?></td>
                <td align="left" style="background-color:<?=$color;?> !important;"><?=$row[2]['Log Level'];?></td>
                <td align="left" style="background-color:<?=$color;?> !important;"><?=$row[3]['Message'];?></td>
			</tr>
		<?

		}
	}
	else {
	?>
	<tr>
		<td colspan="7">Nu sunt loguri.</td>
	</tr>
	<? } ?>
	<?=$this->dataset->table_close();?>
</div>

<?=$this->load->view(branded_view('cp/footer'));?>