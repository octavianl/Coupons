<?=$this->load->view(branded_view('cp/header'));?>
<h1>Refresh categorii according to : <a href="http://helpcenter.linkshare.com/publisher/questions.php?questionid=709" target="_blank">Linkshare Article</a></h1>

<div style="width:600px;text-align:center;" align="center">
	Categories table emptied. <?php echo $cate; ?> categories added.
        <h2>Back to <a href="admincp2/linkshare/listCategories">categories list</a></h2>
</div>

<?=$this->load->view(branded_view('cp/footer'));?>