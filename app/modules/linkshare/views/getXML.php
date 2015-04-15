<?=$this->load->view(branded_view('cp/header'));?>
<h1>Choose XML </h1>

<input type="button" value="ADVERTISERS XML" onclick="javascript:location.href='admincp/linkshare/getXmlAdvertiser'">

<input type="button" value="CATEGORIES XML" onclick="javascript:location.href='admincp2/linkshare/getXmlCategories'">

<input type="button" value="PRODUCTS XML" onclick="javascript:location.href='admincp3/linkshare/getXmlProducts'">

</form>
<?=$this->load->view(branded_view('cp/footer'));?>