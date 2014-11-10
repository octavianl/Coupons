<?=$this->load->view(branded_view('cp/header'));?>
<h1>Edit Merged Categories</h1>
    <form method="post" action="<?=$form_action;?>">
            <div>
                Merged Category Name &nbsp; <input type="text" name="category_name" value="<?=$merged_category_name?>"/>         
	    </div>
            <br/>      
            <div class="controls">
                                       <? if ($action == 'new') { ?>
                                <input type="submit" class="button" name="add" value="Add" />
                                <? } else { ?>
                                <input type="submit" class="button" name="edit" value="edit" />
                                <? } ?>
            </div>
    </form>
 
<?=$this->load->view(branded_view('cp/footer'));?>