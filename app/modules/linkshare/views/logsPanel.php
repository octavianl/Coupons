<?=$this->load->view(branded_view('cp/header'));?>
  <script>
      
      $(document).ready(function() {
          
        $( ".pagination .number a, .pagination .last a, .pagination .first a, .pagination .next a, .pagination .previous a" ).unbind("click").bind( "click", function( event ) {
            event.preventDefault();
            
            var datepicker = $('input[name="datepicker"]').val();
            var zone = $('select[name="zone"]').val();
            //var filterz = $('input[name="filterz"]').val();
            var link_array = this.href;
            console.log(link_array);
            var filter_var = link_array.split('=');
            var offset = parseInt(filter_var[5]);
            if(true === isNaN(offset)) {
                offset = 0;
            }
            var limit = parseInt(filter_var[4]); 
            console.log ('offset = ' +  offset);
            console.log ('limit = ' +  limit);
        
                        
            $.ajax({
                type: 'POST',
                url: '/admincp4/linkshare/updateFilters/',
                data: { datepicker:datepicker, zone:zone, offset:offset, limit:limit },
                dataType:'html',
                    complete: function(data, textStatus, XMLHttpRequest) {
                        console.log('filters ');
                        console.log(data);
                        $('input[name="filterz"]').val(data.responseText);
                        $('form[id="dataset_form"]').attr('method', 'POST');
                        $('form[id="dataset_form"]').submit();
                     },
                     error: function( jqXHR, textStatus, errorThrown ){                        
                        console.log('Text status '+jqXHR.status);
                        console.log('Text status '+textStatus);
                        console.log('Text thrown '+errorThrown);
                     }
                });
            
        });
        
        $(function() {
            $( "#datepicker" ).datepicker({
              showOtherMonths: true,
              selectOtherMonths: true
            });
        });
  
    });

  
  </script>
<h1><?=$form_title;?></h1>
<form method="POST" action="admincp4/linkshare/logsPanel/" name="LogCalendar">
        <?php
            if (isset($_POST['datepicker'])) {
                $datepicker = $_POST['datepicker'];
            }
            
            if (isset($_GET['datepicker'])) {
                $datepicker = $_GET['datepicker'];
            }
            
            if (isset($_POST['zone'])) {
                $zoneRequest = $_POST['zone'];
            }
            
            if (isset($_GET['zone'])) {
                $zoneRequest = $_GET['zone'];
            }
        ?>
        <h4 style="display:inline-block">Select date</h4>
        <input type="text" value="<?php echo $datepicker; ?>" id="datepicker" name="datepicker">
        <h4 style="display:inline-block">Select type</h4>
        <select name="zone" style="display:inline-block">
            <?php
                foreach ($allZones as $zone){
                ?>
                    <option value="<?=$zone?>" <?php if($zoneRequest == $zone){ echo 'selected';} ?> ><?=$zone?></option>
                <?php
                    }
            ?>
        </select>
        <button type="submit">GET LOGS</button>
    </form>
<div>
	<?=$this->dataset->table_head();?>
        <input type="hidden" name="filterz"" />
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