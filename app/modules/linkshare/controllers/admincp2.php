<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Content Control Panel
 *
 * @category Admin Controller
 * @package  Linkshare
 * @author   Weblight <office@weblight.ro>
 * @license  License http://www.weblight.ro/
 * @link     http://www.weblight.ro/
 *
 */
use app\third_party\LOG\Log;

require_once APPPATH . 'third_party/OAUTH2/LinkshareConfig.php';
require_once APPPATH . 'third_party/OAUTH2/CurlApi.php';

class Admincp2 extends Admincp_Controller {

    /**
     * Default coupon-land
     * 
     * @var int 
     */
    private $siteID = 2531438;

    public function __construct() {
        parent::__construct();

        $this->admin_navigation->parent_active('linkshare');

        $siteID = $this->input->cookie('siteID');
        // change if value already in cookie
        if ($siteID) {
            $this->siteID = $siteID;
        }
    }

    public function index() {
        redirect('admincp2/linkshare/listCategories');
    }

    public function listCategories() {
        $this->admin_navigation->module_link('Add category', site_url('admincp2/linkshare/addCategory'));
        $this->admin_navigation->module_link('Parse linkshare category', site_url('admincp2/linkshare/parseCategories'));
        $this->admin_navigation->module_link('Back', site_url('admincp/linkshare/info'));

        $this->load->library('dataset');

        $columns = array(
            array(
                'name' => 'ID #',
                'width' => '15%'),
            array(
                'name' => 'Categories ID #',
                'width' => '15%'),
            array(
                'name' => 'Name',
                'width' => '40%'),
            array(
                'name' => 'Actions',
                'width' => '30%'
            )
        );

        $this->dataset->columns($columns);
        $this->dataset->datasource('category_model', 'getCategories');
        $this->dataset->base_url(site_url('admincp2/linkshare/listCategories'));
        $this->dataset->rows_per_page(1000);

        // total rows
        $total_rows = $this->db->get('linkshare_categories')->num_rows();
        $this->dataset->total_rows($total_rows);

        $this->dataset->initialize();

        // add actions
        $this->dataset->action('Delete', 'admincp2/linkshare/deleteCategory');

        $this->load->view('listCategories');
    }

    public function addCategory() {
        $this->load->library('admin_form');
        $form = new Admin_form;

        $form->fieldset('New Categories');
        $form->text('Category ID linkshare', 'id_category', '', 'Insert category name according to linkshare', true, 'e.g. 17', true);
        $form->text('Name', 'name', '', 'Insert category name', true, 'e.g. Business & Career', true);

        $data = array(
            'form' => $form->display(),
            'form_title' => 'Add category',
            'form_action' => site_url('admincp2/linkshare/addCategoryValidate'),
            'action' => 'new'
        );

        $this->load->view('addCategory', $data);
    }

    public function addCategoryValidate($action = 'new', $id = false) {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('name', 'Name', 'required|trim');
        $this->form_validation->set_rules('id_category', 'Id categorie linkshare', 'required|trim');

        if ($this->form_validation->run() === false) {
            $this->notices->SetError('Required fields.');
            $error = true;
        }

        if (isset($error)) {
            if ($action == 'new') {
                redirect('admincp2/linkshare/addCategory');
                return false;
            } else {
                redirect('admincp2/linkshare/editCategory/' . $id);
                return false;
            }
        }

        $this->load->model('category_model');

        $fields['name'] = $this->input->post('name');
        $fields['id_category'] = $this->input->post('id_category');

        if ($action == 'new') {
            $this->category_model->newCategory($fields);

            $this->notices->SetNotice('Category added successfully.');

            redirect('admincp2/linkshare/listCategories/');
        } else {
            $this->category_model->updateCategory($fields, $id);
            $this->notices->SetNotice('Category updated successfully.');

            redirect('admincp2/linkshare/listCategories/');
        }

        return true;
    }

    public function editCategory($id) {
        $this->load->model('category_model');
        $categorie = $this->category_model->getCategory($id);

        if (empty($categorie)) {
            die(show_error('No category with this ID.'));
        }

        $this->load->library('admin_form');
        $form = new Admin_form;

        $form->fieldset('Category');
        $form->text('Id category linkshare', 'id_category', $categorie['id_category'], 'Insert category id according to linkshare.', true, 'e.g., 17', true);
        $form->text('Name', 'name', $categorie['name'], 'Insert category name.', true, 'e.g., Business & Career', true);

        $data = array(
            'form' => $form->display(),
            'form_title' => 'Edit Category',
            'form_action' => site_url('admincp2/linkshare/addCategoryValidate/edit/' . $categorie['id']),
            'action' => 'edit',
        );

        $this->load->view('addCategory', $data);
    }

    public function deleteCategory($contents, $return_url) {

        $this->load->library('asciihex');
        $this->load->model('category_model');

        $contents = unserialize(base64_decode($this->asciihex->HexToAscii($contents)));
        $return_url = base64_decode($this->asciihex->HexToAscii($return_url));

        foreach ($contents as $content) {
            $this->category_model->deleteCategory($content);
        }

        $this->notices->SetNotice('Category successfully removed.');

        redirect($return_url);

        return true;
    }

    public function parseCategories() {
        $this->admin_navigation->module_link('Update linkshare categories', site_url('admincp2/linkshare/refreshCategories/'));

        $categories = $this->parseCategoryUrl();

        $this->load->library('dataset');

        $columns = array(
            array(
                'name' => 'Category ID #',
                'width' => '35%'),
            array(
                'name' => 'Name',
                'width' => '65%'),
        );

        $filters['categories'] = $categories;
        $filters['limit'] = 50;
        if (isset($_GET['offset']))
            $filters['offset'] = $_GET['offset'];
        $this->dataset->rows_per_page(50);

        $this->dataset->columns($columns);
        $this->dataset->datasource('category_model', 'getCategoriesParse', $filters);
        $this->dataset->base_url(site_url('admincp2/linkshare/parseCategories'));


        // total rows
        $total_rows = count($categories);
        $this->dataset->total_rows($total_rows);

        $data['cate'] = $total_rows;
        $this->dataset->initialize();

        $this->load->view('parseCategories', $data);
    }

    public function refreshCategories() {
        $this->db->query("TRUNCATE TABLE linkshare_categories");
        $categories = $this->parseCategoryUrl();

        foreach ($categories as $cat) {
            $this->db->query("INSERT INTO  linkshare_categories (id_category,name) VALUES ('{$cat['id_category']}','{$cat['name']}')");
        }

        $data['cate'] = count($categories);

        $this->load->view('refreshCategories', $data);
    }

    public function listTempCreativeCategory() {
        $this->load->model(array('site_model', 'category_creative_model', 'advertiser_model'));
        $siteRow = $this->site_model->getSiteBySID($this->siteID);
        $retryCount = $this->advertiser_model->checkPCC(2, $siteRow['id']);

        $this->admin_navigation->module_link('Clear Temp', site_url('admincp2/linkshare/clearTempCC/'));
        $this->admin_navigation->module_link('Retry PCC (' . $retryCount . ')', site_url('admincp2/linkshare/parseCreativeCategories/2'));
        $this->admin_navigation->module_link('Parse ALL Creative Categories', site_url('admincp2/linkshare/parseCreativeCategories/'));
        $this->admin_navigation->module_link('Move CC', site_url('admincp2/linkshare/moveTempCategoryCreative'));

        $this->load->library('dataset');

        $columns = array(
            array(
                'name' => 'ID #',
                'width' => '15%'),
            array(
                'name' => 'Site',
                'width' => '15%'),
            array(
                'name' => 'Category ID #',
                'width' => '10%'),
            array(
                'name' => 'Name',
                'width' => '20%',
                'type' => 'text',
                'filter' => 'nume'),
            array(
                'name' => 'Mid',
                'width' => '15%',
                'type' => 'text',
                'filter' => 'mid'),
            array(
                'name' => 'Nid',
                'width' => '5%'),
            array(
                'name' => 'Actions',
                'width' => '20%'
            )
        );

        $filters = array();
        $filters['limit'] = 50;
        $filters['id_site'] = $siteRow['id'];
        $filters['name'] = true;

        $this->dataset->columns($columns);
        $this->dataset->datasource('category_creative_model', 'getTempCreativeCategories', $filters);
        $this->dataset->base_url(site_url('admincp2/linkshare/listTempCreativeCategory/'));
        $this->dataset->rows_per_page(50);

        if (isset($_GET['offset']))
            $filters['offset'] = $_GET['offset'];

        if (isset($_GET['nume']))
            $filters['nume'] = $_GET['nume'];
        if (isset($_GET['mid']))
            $filters['mid'] = $_GET['mid'];

        $this->load->library('asciihex');
        $this->load->model('forms/form_model');

        if (isset($_GET['filters'])) {
            $aux = unserialize(base64_decode($this->asciihex->HexToAscii($_GET['filters'])));
            if (isset($aux['nume']))
                $filters['nume'] = $aux['nume'];
        }

        // total rows
        $this->db->where('id_site', $siteRow['id']);
        $total_rows = $this->category_creative_model->getTempCategoriesLines($filters);
        $this->dataset->total_rows($total_rows);

        $this->dataset->initialize();

        // add actions
        $this->dataset->action('Delete', 'admincp2/linkshare/deleteCreativeCategory');
        $data = array(
            'form_title' => 'Temporary Creative Category for',
            'site_name' => $siteRow['name']
        );

        $this->load->view('listCreativeCategory', $data);
    }

    public function clearTempCC() 
    {
        $this->load->model('category_creative_model');
        $this->category_creative_model->deleteTempCreativeCategories();
        redirect('admincp2/linkshare/listTempCreativeCategory/');
    }

    public function moveTempCategoryCreative() 
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $this->load->library('admin_form');
        $this->load->model(array('site_model', 'advertiser_model', 'category_creative_model'));
        $siteID = $this->site_model->getSiteBySID($this->siteID);

        $tempCC = $this->category_creative_model->getTempCreativeCategories(array('id_site' => $siteID['id']));

        $currentCC = $this->category_creative_model->getCategories(array('id_site' => $siteID['id']));

        foreach ($currentCC as $val) {
            $existsTempCC = $this->category_creative_model->existsTempCC($siteID['id'], $val['cat_id'], $val['mid']);

            if (!empty($existsTempCC)) {
                // Update creative categories from  linkshare_categories_creative_temp by triplet (site_id,cat_id,mid)
                $tempRow = $this->category_creative_model->getTempCreativeCategory($siteID['id'], $val['cat_id'], $val['mid']);
                $this->category_creative_model->updateCreativeCategoriesFromTemp($tempRow, $siteID['id'], $val['cat_id'], $val['mid']);
                $this->category_creative_model->deleteTempCreativeCategory($siteID['id'], $val['cat_id'], $val['mid']);
            } else {
                if ($val['live'] == 1) {
                    // Mark live items to be deleted by a later cron
                    $this->category_creative_model->updateCreativeCategoriesFromTemp(array('deleted' => 1), $siteID['id'], $val['cat_id'], $val['mid']);
                } else {
                    // Delete advertiser from linkshare_categories_creative_temp by triplet (site_id,cat_id,mid)             
                    $this->category_creative_model->deleteTempCreativeCategory($siteID['id'], $val['cat_id'], $val['mid']);
                }
            }
        }

        $tempCC = $this->category_creative_model->getTempCreativeCategories(array('id_site' => $siteID['id']));

        foreach ($tempCC as $val) {
            $this->category_creative_model->newCreativeCategory($val);
        }

        $this->advertiser_model->resetPCC(0, $siteID['id']);
        
        $this->category_creative_model->deleteTempCreativeCategories();
        redirect('admincp2/linkshare/listTempCreativeCategory/');
    }

    public function listCreativeCategory() {
        $this->load->model(array('site_model', 'category_creative_model', 'advertiser_model'));
        $siteRow = $this->site_model->getSiteBySID($this->siteID);
        $retryCount = $this->advertiser_model->checkPCC(2, $siteRow['id']);

        $this->admin_navigation->module_link('Add creative category', site_url('admincp2/linkshare/addCreativeCategory'));
        $this->admin_navigation->module_link('Parse Creative Categories', site_url('admincp2/linkshare/listTempCreativeCategory/'));

        $this->load->library('dataset');

        $columns = array(
            array(
                'name' => 'ID #',
                'width' => '5%'),
            array(
                'name' => 'Site',
                'width' => '15%'),
            array(
                'name' => 'Category ID #',
                'width' => '10%',
                'type' => 'text',
                'filter' => 'cat_id'),
            array(
                'name' => 'Name',
                'width' => '30%',
                'type' => 'text',
                'filter' => 'nume'),
            array(
                'name' => 'Mid',
                'width' => '10%',
                'type' => 'text',
                'filter' => 'mid'),
            array(
                'name' => 'Nid',
                'width' => '3%'),
            array(
                'name' => 'Parse products',
                'width' => '7%'),
            array(
                'name' => 'Products',
                'width' => '7%'),
            array(
                'name' => 'No',
                'width' => '3%'),
            array(
                'name' => 'Actions',
                'width' => '10%'
            )
        );

        $filters = array();
        $filters['limit'] = 50;
        $filters['id_site'] = $siteRow['id'];
        $filters['name'] = true;

        $this->dataset->columns($columns);
        $this->dataset->datasource('category_creative_model', 'getCategories', $filters);
        $this->dataset->base_url(site_url('admincp2/linkshare/listCreativeCategory/'));
        $this->dataset->rows_per_page(50);

        if (isset($_GET['offset']))
            $filters['offset'] = $_GET['offset'];
        if (isset($_GET['nume']))
            $filters['nume'] = $_GET['nume'];
        if (isset($_GET['cat_id']))
            $filters['cat_id'] = $_GET['cat_id'];
        if (isset($_GET['mid']))
            $filters['mid'] = $_GET['mid'];

        $this->load->library('asciihex');
        $this->load->model('forms/form_model');

        if (isset($_GET['filters'])) {
            $aux = unserialize(base64_decode($this->asciihex->HexToAscii($_GET['filters'])));
            if (isset($aux['nume']))
                $filters['nume'] = $aux['nume'];
        }

        // total rows
        $this->db->where('id_site', $siteRow['id']);
        $total_rows = $this->category_creative_model->getCategoriesLines($filters);
        $this->dataset->total_rows($total_rows);

        $this->dataset->initialize();

        // add actions
        $this->dataset->action('Delete', 'admincp2/linkshare/deleteCreativeCategory');
        $data = array(
            'form_title' => 'Creative Category for',
            'site_name' => $siteRow['name']
        );

        $this->load->view('listCreativeCategory', $data);
    }

    public function addCreativeCategory() {
        $this->load->library('admin_form');
        $form = new Admin_form;

        $form->fieldset('New creative category');
        $form->text('Site ID linkshare', 'id_site', '', 'Insert site ID', true, 'e.g., 1', true);
        $form->text('Creative category ID', 'cat_id', '', 'Insert creative category ID according to linkshare', true, 'e.g., 200229205', true);
        $form->text('Name', 'name', '', 'Insert category name', true, 'e.g., MAT (Mission Against Terror)', true);
        $form->text('Mid linkshare', 'mid', '', 'Insert mid', true, 'e.g., 37517', true);
        $form->text('Nid linkshare', 'nid', '', 'Insert nid', true, 'e.g., 1', true);

        $data = array(
            'form' => $form->display(),
            'form_title' => 'Add Category',
            'form_action' => site_url('admincp2/linkshare/addCreativeCategoryValidate'),
            'action' => 'new'
        );

        $this->load->view('addCreativeCategory', $data);
    }

    public function addCreativeCategoryValidate($action = 'new', $id = false) {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('name', 'Name', 'required|trim');
        $this->form_validation->set_rules('cat_id', 'Creative category ID linkshare', 'required|trim');

        if ($this->form_validation->run() === false) {
            $this->notices->SetError('Required fields.');
            $error = true;
        }

        if (isset($error)) {
            if ($action == 'new') {
                redirect('admincp2/linkshare/addCreativeCategory');
                return false;
            } else {
                redirect('admincp2/linkshare/editCreativeCategory/' . $id);
                return false;
            }
        }

        $this->load->model('category_creative_model');

        $fields['id_site'] = $this->input->post('id_site');
        $fields['cat_id'] = $this->input->post('cat_id');
        $fields['name'] = $this->input->post('name');
        $fields['mid'] = $this->input->post('mid');
        $fields['nid'] = $this->input->post('nid');

        if ($action == 'new') {
            $type_id = $this->category_creative_model->newCreativeCategory($fields);

            $this->notices->SetNotice('Creative category added successfully.');

            redirect('admincp2/linkshare/listCreativeCategory/');
        } else {
            $this->category_creative_model->updateCategory($fields, $id);
            $this->notices->SetNotice('Creative category updated successfully.');

            redirect('admincp2/linkshare/listCreativeCategory/');
        }

        return true;
    }

    public function editCreativeCategory($id) {
        $this->load->model('category_creative_model');
        $categorie = $this->category_creative_model->getCategory($id);

        if (empty($categorie)) {
            die(show_error('No category with this ID.'));
        }

        $this->load->library('admin_form');
        $form = new Admin_form;

        $form->fieldset('Creative category');
        $form->text('Site ID linkshare', 'id_site', $categorie['id_site'], 'Insert site ID', true, 'e.g., 1', true);
        $form->text('Creative category ID', 'cat_id', $categorie['cat_id'], 'Insert category ID according to linkshare', true, 'e.g., 200229205', true);
        $form->text('Name', 'name', $categorie['name'], 'Insert category name', true, 'e.g., MAT (Mission Against Terror)', true);
        $form->text('Mid linkshare', 'mid', $categorie['mid'], 'Insert mid', true, 'e.g., 37517', true);
        $form->text('Nid linkshare', 'nid', $categorie['nid'], 'Insert nid', true, 'e.g., 1', true);

        $data = array(
            'form' => $form->display(),
            'form_title' => 'Edit creative category',
            'form_action' => site_url('admincp2/linkshare/addCreativeCategoryValidate/edit/' . $categorie['id']),
            'action' => 'edit',
        );

        $this->load->view('addCreativeCategory', $data);
    }

    public function deleteCreativeCategory($contents, $return_url) {

        $this->load->library('asciihex');
        $this->load->model('category_creative_model');

        $contents = unserialize(base64_decode($this->asciihex->HexToAscii($contents)));
        $return_url = base64_decode($this->asciihex->HexToAscii($return_url));

        foreach ($contents as $content) {
            $this->category_creative_model->deleteCategory($content);
        }

        $this->notices->SetNotice('Creative category removed successfully.');

        redirect($return_url);

        return true;
    }

    public function updateFilters() {
        $this->load->library('asciihex');

        // old filters
        $filters_decode = unserialize(base64_decode($this->asciihex->HexToAscii($_POST['filterz'])));

        $filters = array();

        foreach ($_POST as $key => $val) {
            if (in_array($val, array('filter results'))) {
                unset($_POST[$key]);
            }
        }
        if (!empty($_POST['merged_category'])) {
            $filters['merged_category'] = $_POST['merged_category'];
        }

        if (!empty($_POST['all_page_category'])) {
            $filters['all_page_category'] = $_POST['all_page_category'];
        }
        $all_page_category_ok = array();
        $all_page_category = explode(',', $filters['all_page_category']);

        // remove 0
        foreach ($all_page_category as $cat) {
            if ($cat) {
                // current page checkboxes
                $all_page_category_ok[] = $cat;
            }
        }

        if (!empty($_POST['check_category'])) {
            $filters['check_category'] = $_POST['check_category'];
        }


        $check_category_ok = array();
        $check_category = explode(',', $filters['check_category']);

        // remove 0
        foreach ($check_category as $cat) {
            if ($cat) {
                //current checkboxes
                $check_category_ok[] = $cat;
            }
        }

        $filters_decode_check_category_ok = array();
        $filters_decode_check_category = explode(',', $filters_decode['check_category']);

        // remove 0
        foreach ($filters_decode_check_category as $cat) {
            if ($cat) {
                //past checkboxes
                $filters_decode_check_category_ok[] = $cat;
            }
        }

        // add new checkboxes
        foreach ($check_category_ok as $cat) {
            if (!in_array($cat, $filters_decode_check_category_ok)) {
                $filters_decode_check_category_ok[] = $cat;
            }
        }

        $checkboxes_to_remove = array();
        $checkboxes_to_remove = array_diff($all_page_category_ok, $check_category_ok);
        // remove old checkboxes from this page
        $filters_decode_check_category_ok = array_diff($filters_decode_check_category_ok, $checkboxes_to_remove);

        $filters_decode_check_category_ok = implode(',', $filters_decode_check_category_ok);

        $filters['check_category'] = $filters_decode_check_category_ok;

        if (!empty($_POST['nume'])) {
            $filters['nume'] = $_POST['nume'];
        }
        if (!empty($_POST['mid'])) {
            $filters['mid'] = $_POST['mid'];
        }
        if (!empty($_POST['offset'])) {
            $filters['offset'] = $_POST['offset'];
        }

        if (!empty($_POST['limit'])) {
            $filters['limit'] = $_POST['limit'];
        }

        //print_r($filters);

        $filters = $this->CI->asciihex->AsciiToHex(base64_encode(serialize($filters)));
        echo $filters;
    }
    
    /**
     * Used to set the cookie for the merged categories after 
     * choosing the categories by clicking the checkboxes
     * 
     * @return void
     */
    public function joinMerge()
    {
        $this->load->helper('cookie');
        $cookie = $this->input->cookie('mergedCategories');
        $selected = $this->input->post('selected');
        $value = null;
        $category = $this->input->post('checkbox');
               
        if ($selected == 'true') {
            if (empty($cookie)) {
                $value = $category;
            } else {
               $categories = explode(',', $cookie);
               if (!in_array($category, $categories)) {
                    $value = $this->input->cookie('mergedCategories') . ',' . $category;
                }
            }                                                
        } else {
            if (empty($cookie)) {
                return;
            }
            
            $categories = explode(',', $cookie);
            
            if (in_array($category, $categories)) {                
                unset($categories[array_search($category, $categories)]);
                $value = implode(',', $categories);
            }
        }
        
        if (!is_null($value)) {
            $this->input->set_cookie("mergedCategories", $value, 2592000);
        }        
    }
    
    /**
     * Reset join merge categories cookies
     * 
     * @return void
     */
    public function joinMergeReset()
    {
        $this->load->helper('cookie');
        delete_cookie('mergedCategories');
        delete_cookie('mergingCategory');        
    }

    public function joinCreativeCategory($action = 'insert', $mergedCreativeCategoryId = 0) 
    {
        $this->admin_navigation->module_link('View Merged Categories', site_url('admincp2/linkshare/listMergedCategories/'));

        $this->load->library('dataset');
        $this->load->model(array('category_creative_model','site_model'));
        $this->load->helper('cookie');

        $siteRow = $this->site_model->getSiteBySID($this->siteID);

        $columns = array(
            array(
                'name' => 'ID #',
                'width' => '5%'),
            array(
                'name' => 'SELECT TO MERGE',
                'width' => '8%'),
            array(
                'name' => 'SITE',
                'width' => '10%'),
            array(
                'name' => 'CATEGORY ID #',
                'width' => '25%',
                'type' => 'text',
                'filter' => 'cat_creativ_id'),
            array(
                'name' => 'Name',
                'width' => '18%',
                'type' => 'text',
                'filter' => 'nume'),
            array(
                'name' => 'Mid',
                'width' => '10%',
                'type' => 'text',
                'filter' => 'mid'),
            array(
                'name' => 'Nid',
                'width' => '5%'),
            array(
                'name' => 'See Products',
                'width' => '7%'),
            array(
                'name' => 'No. Products',
                'width' => '7%'),
            array(
                'name' => 'Actions',
                'width' => '5%')
        );

        $filters = $filters_decode = array();
        //$filters['limit'] = 50;
        $filters['id_site'] = $siteRow['id'];
        $filters['name'] = true;

        if (isset($_GET['offset'])) {
            $filters['offset'] = $_GET['offset'];
        }
        
        if ($action == 'killCookies') {
            $this->joinMergeReset();
            redirect(site_url('admincp2/linkshare/joinCreativeCategory'));            
            die;
        }
        
        // we edit an already merged category
        if ($action == 'edit') {
            $mergedCategory = $this->category_creative_model->getMergedCategoryByID($mergedCreativeCategoryId);
            $site = $this->site_model->getSite($mergedCategory[0]['id_site']);
            // set cookie site
            $this->input->set_cookie("siteID", $site['SID'], 2592000);
            // set category merged name
            $this->input->set_cookie("mergingCategory", $mergedCategory[0]['name'] . ',' . $site['SID'] . ',edit', 2592000);
            // set merged creative categories checkboxes (set cookie corresponding to checkboxes)
            $linkshareCategoriesJoined = $this->category_creative_model->getJoinsCategory($mergedCreativeCategoryId);
            
            $checkboxes = array();
            foreach ($linkshareCategoriesJoined as $linkshareCategorieJoined) {
              $temp = $this->category_creative_model->getCategoryCreativeByCatId($linkshareCategorieJoined['cat_id']);
              $checkboxes[] = $temp['id'];
            }
            // set checkboxes in cookie
            $this->input->set_cookie("mergedCategories",implode(',', $checkboxes), 2592000);
            
            // redirect with new cookies set
            redirect(site_url('admincp2/linkshare/joinCreativeCategory'));
        }
        
        // begin when finish common to both insert and edit actions        
        if ($this->input->post('saving') == 'finish') {
            $mergedCategoryCookie = explode(',', $this->input->cookie('mergingCategory'));
            $action = $mergedCategoryCookie[2];
        }    
        // insert action
        if ($this->input->post('saving') == 'finish' && $action == 'insert') {
            $id_merged_category = $this->category_creative_model->newMergedCategory($mergedCategoryCookie[0], $siteRow['id']);
            $this->category_creative_model->newJoinCategory($id_merged_category, $this->input->cookie('mergedCategories'));
            
            $this->joinMergeReset();
        }
        // edit action
        if ($this->input->post('saving') == 'finish' && $action == 'edit') {            
            $site = $this->site_model->getSiteBySID($mergedCategoryCookie[1]);            
            $mergedCategory = $this->category_creative_model->getMergedCategory($site['id'], $mergedCategoryCookie[0]);
            
            $existingCategories = $this->category_creative_model->getJoinsCategory($mergedCategory[0]['ID']);
            
            foreach ($existingCategories as $existingCategory) {
                $tempCreativeCategory = $this->category_creative_model->getCreativeForMerge(
                    array(
                        'id_site' => $site['id'],
                        'cat_creativ_id' => $existingCategory['cat_id'],
                        'mid' => $existingCategory['mid']
                    )
                );
                                
                $existingCategoriesIds[] = $tempCreativeCategory[0]['id'];
            }
            
            $cookieCategoriesIds = explode(',', $this->input->cookie('mergedCategories'));

            $categoriesIdsToDelete = array_diff($existingCategoriesIds, $cookieCategoriesIds);
            $categoriesIdsToInsert = array_diff($cookieCategoriesIds, $existingCategoriesIds);

            /*print '<pre>';
            print_r($site);
            print_r($mergedCategory);
            print_r($existingCategories);
            print_r($existingCategoriesIds);
            print_r($cookieCategoriesIds);
            print_r($categoriesIdsToDelete);
            print_r($categoriesIdsToInsert);*/
            
            foreach ($categoriesIdsToDelete as $categoryIdToDelete) {
                $categoryCreativeToDelete = $this->category_creative_model->getCategory($categoryIdToDelete);
                //print '<pre>$categoryCreativeToDelete';
                //print_r($categoryCreativeToDelete);
                
                $this->category_creative_model->deleteJoinCategory($categoryIdToDelete, $mergedCategory[0]['ID'], $categoryCreativeToDelete['mid']);                
            }
            
            $this->category_creative_model->newJoinCategory($mergedCategory[0]['ID'], implode(',', $categoriesIdsToInsert));
            
            $this->joinMergeReset();
        }                

        if ($this->input->post('saving') == 'ok') {
            if (!$this->input->cookie('siteID')) {
                $this->notices->SetError('You must first choose a site');
                header("Refresh:0");
                die;
            }
            
            // empty cookies as we may be editing a category for instance, so we give it up
            $this->joinMergeReset();
            
            $this->input->set_cookie("mergingCategory", $this->input->post('merged_category') . ',' . $this->input->cookie('siteID') . ',' . 'insert', 2592000);
            header("Refresh:0");
            die;
        }
        
        $mergingCategoryTemp = explode(',', $this->input->cookie('mergingCategory'));
        
        // cookie for another channel
        if ($mergingCategoryTemp[1] != $this->input->cookie('siteID')) {
            if ($this->input->cookie('mergingCategory')) {
                $this->joinMergeReset();
                header("Refresh:0");
                die;
            }
        }
        
        $this->dataset->columns($columns);
        $this->dataset->datasource('category_creative_model', 'getCreativeForMerge', $filters);
        $this->dataset->base_url(site_url('admincp2/linkshare/joinCreativeCategory/'));
        
        if (isset($_GET['cat_creativ_id'])) {
            $filters['cat_creativ_id'] = $_GET['cat_creativ_id'];
        }

        if (isset($_GET['nume'])) {
            $filters['nume'] = $_GET['nume'];
        }

        if (isset($_GET['mid'])) {
            $filters['mid'] = $_GET['mid'];
        }

        // total rows
        $this->db->where('id_site', $siteRow['id']);
        $total_rows = $this->category_creative_model->getCategoriesLines($filters);

        $this->dataset->total_rows($total_rows);

        $this->dataset->initialize();
        
        $data = array(
            'cat_creativ_id' => $filters['cat_creativ_id'],
            'name_search' => $filters['nume'],
            'mid_search' => $filters['mid'],
            'name_merged' => $filters['merged_category'],
            'site_name' => $siteRow['name'],
            'mergingCategory' => $mergingCategoryTemp[0],
            'mergedCategories' => explode(',', $this->input->cookie('mergedCategories'))
        );

        $this->load->view('joinCreativeCategory', $data);
    }
    
    public function listMergedCategories()
    {
        $this->load->model(array('category_creative_model','site_model'));

        $this->admin_navigation->module_link('ADD ANOTHER Merged Categories', site_url('admincp2/linkshare/joinCreativeCategory'));

        $siteRow = $this->site_model->getSiteBySID($this->siteID);

        $this->load->library('dataset');

        $columns = array(
            array(
                'name' => 'Merged Category Name',
                'width' => '20%'),
            array(
                'name' => 'Total Creat Cat merged',
                'width' => '10%'),
            array(
                'name' => 'Join categories',
                'width' => '50%'),
            array(
                'name' => 'Total prod merged',
                'width' => '10%'),
            array(
                'name' => 'Actions',
                'width' => '10%'
            )
        );

        $filters = array();
        $filters['id_site'] = $siteRow['id'];
                
        $this->dataset->columns($columns);
        $this->dataset->datasource('category_creative_model', 'listMergedCategory', $filters);
        $this->dataset->base_url(site_url('admincp2/linkshare/listMergedCategories'));
        $this->dataset->rows_per_page(10);

        // total rows
        //$total_rows = $this->db->get('linkshare_categories')->num_rows();
        $this->dataset->total_rows($total_rows);

        $this->dataset->initialize();

        // add actions
        $this->dataset->action('Delete', 'admincp2/linkshare/deleteMergedCatory');
        
        $data = array(
            'site_name' => $siteRow['name']
        );
        
        $this->load->view('listMergedCategory', $data);
    }

    public function editMergedCategory($id) {
        $this->load->model('category_creative_model');
        $this->admin_navigation->module_link('Back to merged categories list', site_url('admincp2/linkshare/listMergedCategories/'));
        
        $fields['name'] = $this->input->post('category_name');
        $fields['description'] = $this->input->post('category_description');
        $fields['meta_title'] = $this->input->post('category_meta_title');
        $fields['meta_keywords'] = $this->input->post('category_meta_keywords');
        $fields['meta_description'] = $this->input->post('category_meta_description');
        $fields['url_rewritten'] = $this->input->post('category_url_rewritten');

        if (count(array_filter($fields)) == count($fields)) {
            $this->category_creative_model->updateMergedCategory($fields, $id);
            $this->notices->SetNotice('Category updated successfully.');

            redirect('admincp2/linkshare/listMergedCategories/');
            return true;
        }        
        
        $category = $this->category_creative_model->getMergedCategoryByID($id);
        if(count(array_filter($category)) != count($category)){ $this->notices->SetError('Please fill all fields!'); }
 
        $data = array(
            'category' => $category,
            'form_action' => site_url('admincp2/linkshare/editMergedCategory/' . $id),
        );

        $this->load->view('editMergedCategory', $data);
    }

    function deleteMergedCatory($contents, $return_url) {
        $this->load->library('asciihex');
        $this->load->model('category_creative_model');

        $contents = unserialize(base64_decode($this->asciihex->HexToAscii($contents)));
        $return_url = base64_decode($this->asciihex->HexToAscii($return_url));

        foreach ($contents as $content) {
            $this->category_creative_model->deleteMergedCategory($content);
        }

        $this->notices->SetNotice('Merged Category deleted successfully.');
        redirect($return_url);
        return true;
    }

    function ajaxDeleteCategory($MergedCategory_id, $JoinsCategory_id) {
        $this->load->model('category_creative_model');
        $this->category_creative_model->deleteJoinCategory($MergedCategory_id, $JoinsCategory_id);

        $message = "Joins Category id:" . $JoinsCategory_id . " from Merged Category id:" . $MergedCategory_id . " deleted successfully.";
        $this->notices->SetNotice($message);

        $return_url = site_url('admincp2/linkshare/listMergedCategories');
        redirect($return_url);
        return true;
    }

    public function parseCreativeCategories($pcc = 0) {
        //error_reporting(E_ALL);
        //ini_set('display_errors',1);
        include "app/third_party/LOG/Log.php";
        $CI = & get_instance();

        $this->load->model(array('site_model', 'advertiser_model', 'category_creative_model'));
        $siteRow = $this->site_model->getSiteBySID($this->siteID);

        $config = new LinkshareConfig();
        $accessToken = $config->setSiteCookieAndGetAccessToken($CI, $this->siteID);
        if ($pcc == 2) {
            $current = $this->advertiser_model->getAdvertisers(array('id_status' => 1, 'id_site' => $siteRow['id'], 'pcc' => 2, 'retry' => true));
        } else {
            $current = $this->advertiser_model->getAdvertisers(array('id_status' => 1, 'id_site' => $siteRow['id'], 'pcc' => 0));
        }

        $valCurrent = array_shift($current);

        //echo"<pre>";print_r($valCurrent);die;
        if (count($valCurrent)!=0 && $valCurrent['retry']!=0) {
            $categoriesRequestUrl = 'https://api.rakutenmarketing.com/linklocator/1.0/getCreativeCategories/' . $valCurrent['mid'];

            $request = new CurlApi($categoriesRequestUrl);
            $request->setHeaders($config->getMinimalHeaders($accessToken));
            $request->setGetData();
            $request->send();

            $responseObj = $request->getFormattedResponse();

            $cats = array();
            $aux = $responseObj['body'];

            $categories = simplexml_load_string($aux, "SimpleXMLElement", LIBXML_NOCDATA);
            //echo $categories->getName().'<br/>';die;
            if (isset($categories) && is_object($categories)) {
                $kids = $categories->children('ns1', true);
                //echo"<pre>";print_r($kids);die;
                foreach ($kids as $child) {
                    $temp = array(
                        'id_site' => $siteRow['id'],
                        'cat_id' => (string) $child->catId,
                        'name' => (string) $child->catName,
                        'mid' => (string) $child->mid,
                        'nid' => (string) $child->nid
                    );

                    $cats[] = $temp;
                    $i++;
                }

                // delete old categories for this mid and this site id
                $this->category_creative_model->deleteTempCategoryByMid($siteRow['id'], $valCurrent['mid']);
                //echo"<pre>";print_r($cats);die;
                foreach ($cats as $key => $cat) {
                    if ($cat['name'] == 'Default') {
                        unset($cats[$key]);
                    } elseif ($cat['mid'] == 0) {
                        unset($cats[$key]);
                    } else {
                        $this->category_creative_model->newTempCreativeCategory($cat);
                    }
                }

                $this->advertiser_model->changePCC(1, $valCurrent['mid'], $siteRow['id']);
            } else {
                if ($pcc != 2) {
                    $this->advertiser_model->changePCC(2, $valCurrent['mid'], $siteRow['id']);
                } else { }
                $message = 'http://lld2.linksynergy.com/services/restLinks/getCreativeCategories/' . $valCurrent['mid'] . ' xml error Site name: ' . $valCurrent['name_site'];
                Log::error($message);
            }
            
            if ($pcc == 2) {
                    $retry = --$valCurrent['retry'];
                    //echo"<pre>";print_r($retry);die;
                    $this->advertiser_model->changePCC(2, $valCurrent['mid'], $siteRow['id'], $retry);
                    echo '<META http-equiv="refresh" content="10; URL=/admincp2/linkshare/parseCreativeCategories/2">';
            } else {
                echo '<META http-equiv="refresh" content="10; URL=/admincp2/linkshare/parseCreativeCategories/">';
            }
        } else {
            redirect('admincp2/linkshare/listTempCreativeCategory/');
        }
    }

    public function getXmlCreativeCategories() {
        $CI = & get_instance();
        $this->load->library('admin_form');
        $form = new Admin_form;

        $this->load->model(array('site_model', 'advertiser_model', 'advertiser_temp_model'));
        $this->admin_navigation->module_link('Back', site_url('admincp/linkshare/getXML/'));
        $this->admin_navigation->module_link('Parse ALL Creative Categories', site_url('admincp2/linkshare/parseCreativeCategories'));

        $siteRow = $this->site_model->getSiteBySID($this->siteID);

        $temp = $this->advertiser_temp_model->getTempAdvertisers($siteID['id']);
        $current = array_merge($this->advertiser_model->getAdvertisers(array('id_status' => 1, 'id_site' => $siteRow['id'])));

        foreach ($current as $val) {
            $allMids[] = array('mid' => $val['mid'], 'name' => $val['name']);
        }

        $responseObj = array();

        if (isset($_GET['mid'])) {

            $categoriesRequestUrl = 'https://api.rakutenmarketing.com/linklocator/1.0/getCreativeCategories/' . $_GET['mid'];

            $config = new LinkshareConfig();

            $accessToken = $config->setSiteCookieAndGetAccessToken($CI, $this->siteID);

            $request = new CurlApi($categoriesRequestUrl);
            $request->setHeaders($config->getMinimalHeaders($accessToken));
            $request->setGetData();
            $request->send();

            $responseObj = $request->getFormattedResponse();

            if (!$responseObj) {
                $this->notices->SetError('Refresh token');
                $responseObj['header'] = $responseObj['body'] = 'ERROR';
            } else {
                //print '<pre>';
                //print_r($responseObj['header']);
            }
        }

        $form->fieldset('Xml');
        $form->textarea('Header', 'header', $responseObj['header'], 'header', true, 'e.g., header', true);
        $form->textarea('Body', 'body', $responseObj['body'], 'body', true, 'e.g., body', true);

        $data = array(
            'form' => $form->display(),
            'form_title' => 'Categories XML',
            'form_scope' => 'category',
            'site_name' => $siteRow['name'],
            'allMids' => $allMids,
            'form_action' => site_url('admincp/linkshare/')
        );

        $this->load->view('xml', $data);
    }

}
