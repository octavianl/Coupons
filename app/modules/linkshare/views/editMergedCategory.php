<?=$this->load->view(branded_view('cp/header'));?>
<h1>Edit Merged Categories</h1>
    <form method="post" action="<?=$form_action;?>">
            <div>
                <div style="margin: 10px 0;">
                    <div style="width:130px; display: inline-block">Category Name &nbsp; </div>
                    <input type="text" name="category_name" value="<?=$category['name']?>" style="width:200px;"/>
                </div>
                <div style="margin: 10px 0; display: table-cell;">
                    <div style="width:130px; display: inline-block; vertical-align: top;">Description &nbsp; </div>
                    <textarea  name="category_description" style="width:200px;"><?=$category['description']?></textarea>
                </div>
                <div style="margin: 10px 0;">
                    <div style="width:130px; display: inline-block">Meta title &nbsp; </div>
                    <input type="text" name="category_meta_title" value="<?=$category['meta_title']?>" style="width:200px;"/>
                </div>
                <div style="margin: 10px 0;">
                    <div style="width:130px; display: inline-block">Meta keywords &nbsp; </div>
                    <input type="text" name="category_meta_keywords" value="<?=$category['meta_keywords']?>" style="width:200px;"/>
                </div>
                <div style="margin: 10px 0; display: table-cell;">
                    <div style="width:130px; display: inline-block; vertical-align: top;">Meta description &nbsp; </div>
                    <textarea name="category_meta_description" style="width:200px;"><?=$category['meta_description']?></textarea>
                </div>
                <div style="margin: 10px 0;">
                    <div style="width:130px; display: inline-block">URL rewritten &nbsp; </div>
                    <input type="text" name="category_url_rewritten" value="<?=$category['url_rewritten']?>" style="width:200px;"/>
                </div>
	    </div>
            <br/>      
            <div class="controls">
                <input type="submit" class="button" name="edit" value="Edit" />
            </div>
    </form>
 
<?=$this->load->view(branded_view('cp/footer'));?>