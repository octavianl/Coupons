<?=$this->load->view(branded_view('cp/header'));?>
<h1><?=$form_title;?> for <?=$site_name?>
<h4>Select advertisers status</h4>
    <form method="GET" action="admincp/linkshare/getXmlCookie/">
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
</h1>
<form class="form validate" enctype="multipart/form-data" id="form_type" method="post" action="<?=$form_action;?>">

<?=$form;?>

<div class="submit">
    <input type="submit" class="button" name="go_type" value="XML" />    	
</div>
</form>
<?=$this->load->view(branded_view('cp/footer'));?>