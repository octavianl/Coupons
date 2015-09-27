<?=$this->load->view(branded_view('cp/header'));?>
  <script>
  $(function() {
    $( "#datepicker" ).datepicker({
      showOtherMonths: true,
      selectOtherMonths: true
    });
  });
  </script>
<h1><?=$form_title;?></h1>
    <form method="GET" action="admincp4/linkshare/logsPanel/">
        <h4 style="display:inline-block">Select date</h4>
        <input type="text" value="<?php if(isset($_GET['datepicker'])){ echo $_GET['datepicker']; } ?>" id="datepicker" name="datepicker">
        <h4 style="display:inline-block">Select type</h4>
        <select name="zone" style="display:inline-block">
            <?php
                foreach ($allZones as $zone){
                ?>
                    <option value="<?=$zone?>" <?php if(isset($_GET['zone']) && $_GET['zone']==$zone){ echo 'selected';} ?> ><?=$zone?></option>
                <?php
                    }
            ?>
        </select>
        <button type="submit">GET LOGS</button>
    </form>
<div>
	<?=$this->dataset->table_head();?>
	<?php

	if (!empty($this->dataset->data)) {
		foreach ($this->dataset->data as $row) {
			$color = "";
			if($row[2]['LogLevel'] === "ERROR"){$color = "#EEA2A2";}
			if($row[2]['LogLevel'] === "WARN"){$color = "#EED859";}
			if($row[2]['LogLevel'] === "INFO"){$color = "#C6F1FF";}
                        if($row[2]['LogLevel'] === "DEBUG"){$color = "#FFBFBF";}
			if($row[2]['LogLevel'] === "NOTICE"){$color = "#60AAE8";}

	?>

		<tr>			
                    <td align="left" style="background-color:<?=$color;?> !important;"><?=$row[0]['RowNo'];?></td>                                
                    <td align="left" style="background-color:<?=$color;?> !important;"><?=$row[1]['DateTime'];?></td>
                    <td align="left" style="background-color:<?=$color;?> !important;"><?=$row[2]['LogLevel'];?></td>
                    <td align="left" style="background-color:<?=$color;?> !important;"><?=$row[3]['FileName'];?></td>
                    <td align="left" style="background-color:<?=$color;?> !important;"><?=$row[4]['Line'];?></td>                                
                    <td align="left" style="background-color:<?=$color;?> !important;"><?=$row[5]['Class'];?></td>
                    <td align="left" style="background-color:<?=$color;?> !important;"><?=$row[6]['Method'];?></td>
                    <td align="left" style="background-color:<?=$color;?> !important;"><?=$row[7]['Message'];?></td>
		</tr>
	<?php
		}
	}
	else {
	?>
	<tr>
		<td colspan="7">No logs available.</td>
	</tr>
	<?php } ?>
	<?=$this->dataset->table_close();?>
</div>

<?=$this->load->view(branded_view('cp/footer'));?>