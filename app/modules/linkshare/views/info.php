<?=$this->load->view(branded_view('cp/header'));?>
<h1>More Info </h1>

<input type="button" value="LIST SITES" onclick="javascript:location.href='admincp/linkshare/listSites'">

<input type="button" value="LIST NETWORKS" onclick="javascript:location.href='admincp/linkshare/listNetworks'">

<input type="button" value="LIST STATUS" onclick="javascript:location.href='admincp/linkshare/listStatus'">

<input type="button" value="LIST CATEGORIES" onclick="javascript:location.href='admincp2/linkshare/listCategories'">


</form>
<?=$this->load->view(branded_view('cp/footer'));?>