<?=$this->load->view(branded_view('cp/header'));?>
<h1><?=$form_title;?> for <?=$site_name?>
<?php if($form_scope == 'advertiser'){ ?>
<h4><input type="button" value="ALL APPROVED" onclick="javascript:location.href='admincp/linkshare/parseAdvertisers/1'"></h4>
<h4>Select advertisers status</h4>
    <form method="GET" action="admincp/linkshare/getXmlAdvertiser/">
    <select name="status">
        <?php
            foreach ($allStatus as $status){
            ?>
                <option value="<?=$status['id_status']?>" <?php if(isset($_GET['status']) && $_GET['status']==$status['id_status']){ echo 'selected';} ?> ><?=$status['id_status']?></option>
            <?php
                }
        ?>
    </select>
    <button type="submit">GET XML</button>
    </form>
<?php } if($form_scope == 'category'){ ?>
<h4>Select approved advertiser mids</h4>
    <form method="GET" action="admincp2/linkshare/getXmlCreativeCategories/">
    <select name="mid">
        <?php
            foreach ($allMids as $val){
            ?>
                <option value="<?=$val['mid']?>" <?php if(isset($_GET['mid']) && $_GET['mid']==$val['mid']){ echo 'selected';} ?> ><?php echo $val['mid'].' '.$val['name']; ?></option>
            <?php
                }
        ?>
    </select>
    <button type="submit">GET XML</button>
    </form>
<?php } ?>
</h1>
<form class="form validate" enctype="multipart/form-data" id="form_type" method="post" action="<?=$form_action;?>">

<?=$form;?>

<div class="submit">
    <input type="submit" class="button" name="go_type" value="XML" />    	
</div>
</form>
<?=$this->load->view(branded_view('cp/footer'));?>