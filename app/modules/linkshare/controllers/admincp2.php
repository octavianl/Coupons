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

class Admincp2 extends Admincp_Controller
{
    /**
     * Default coupon-land
     * 
     * @var int 
     */
    private $siteID = 2531438;
    
    public function __construct()
    {
        parent::__construct();

        $this->admin_navigation->parent_active('linkshare');
        
        $siteID = $this->input->cookie('siteID');
        // change if value already in cookie
        if ($siteID) {
            $this->siteID = $siteID;
        }                

    }

    public function index()
    {
        redirect('admincp2/linkshare/listCategories');
    }

    public function listCategories()
    {
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

    public function addCategory()
    {
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

    public function addCategoryValidate($action = 'new', $id = false)
    {
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

    public function editCategory($id)
    {
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

    public function deleteCategory($contents, $return_url)
    {

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

    public function parseCategoryUrl()
    {
        $url = "http://helpcenter.linkshare.com/publisher/questions.php?questionid=709";

        $cUrl = curl_init();
        curl_setopt($cUrl, CURLOPT_URL, $url);
        curl_setopt($cUrl, CURLOPT_HTTPGET, 1);
        //curl_setopt($cUrl, CURLOPT_USERAGENT,'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.2; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0)');
        //curl_setopt($cUrl, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
        curl_setopt($cUrl, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($cUrl, CURLOPT_TIMEOUT, '3');
        //$pageContent = trim(curl_exec($cUrl));
        $pageContent = curl_exec($cUrl);
        curl_close($cUrl);

        //preg_match_all('|<a href="(.*)" title="View all (.*)">|',$pageContent,$out);
        preg_match_all('|<span>(.*)</span>|', $pageContent, $out);
        $categories = array();
        //$data['content'] = print_r($out,1);

        foreach ($out[1] as $k => $v) {
            if ($k > 2) {
                if ($k % 2)
                    $ids[] = $v;
                else
                    $cat[] = $v;
            }
        }

        $categories = array();
        foreach ($ids as $k => $v) {
            $categories[] = array('id_category' => $v,
                'name' => $cat[$k]
            );
        }

        //print '<pre>';print_r($categories);die;

        return $categories;
    }

    public function parseCategories()
    {
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

    public function parseCreativeCategorySite($id)
    {
        $this->admin_navigation->module_link('Update linkshare categories', site_url('admincp2/linkshare/refresh_categorii_site/' . $id));

        $aux = '';
        $aux = file_get_contents('http://lld2.linksynergy.com/services/restLinks/getCreativeCategories/c9b4a2805e6d69846a3b7c9f0c23c26249cb86bc50fe864ff13746a8ab7dc92f/216');

        $categories = simplexml_load_string($aux, "SimpleXMLElement", LIBXML_NOCDATA);
        //echo $categories->getName().'<br/>';
        $kids = $categories->children('ns1', true);
        //var_dump(count($kids));
        foreach ($kids as $child) {
            echo $child->catId . '<br/>';
            echo $child->catName . '<br/>';
            echo $child->mid . '<br/>';
            echo $child->nid . '<br/>----<br/>';
        }

        die;

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
    public function listCreativeCategory($id = 1)
    {
        $this->admin_navigation->module_link('Add creative category', site_url('admincp2/linkshare/addCreativeCategory'));
        $this->admin_navigation->module_link('Parse creative categories add them into DB', site_url('admincp2/linkshare/parseCreativeCategories/' . $id));
        $this->admin_navigation->module_link('Export CSV', site_url('admincp2/linkshare/export_csv/category_creative'));
        
        $this->load->library('dataset');
        $this->load->model('category_creative_model');

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
        $filters['id_site'] = $id;
        $filters['name'] = true;

        $this->dataset->columns($columns);
        $this->dataset->datasource('category_creative_model', 'getCategories', $filters);
        $this->dataset->base_url(site_url('admincp2/linkshare/listCreativeCategory/' . $id));
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
        $this->db->where('id_site', $id);
        $total_rows = $this->category_creative_model->getCategoriesLines($filters);
        $this->dataset->total_rows($total_rows);

        $this->dataset->initialize();

        // add actions
        $this->dataset->action('Delete', 'admincp2/linkshare/deleteCreativeCategory');

        $this->load->view('listCreativeCategory');
    }

    public function addCreativeCategory()
    {
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

    public function addCreativeCategoryValidate($action = 'new', $id = false)
    {
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
            $type_id = $this->category_creative_model->newCategory($fields);

            $this->notices->SetNotice('Creative category added successfully.');

            redirect('admincp2/linkshare/listCreativeCategory/');
        } else {
            $this->category_creative_model->updateCategory($fields, $id);
            $this->notices->SetNotice('Creative category updated successfully.');

            redirect('admincp2/linkshare/listCreativeCategory/');
        }

        return true;
    }

    public function editCreativeCategory($id)
    {
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

    public function deleteCreativeCategory($contents, $return_url)
    {

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

    public function parseCreativeCategories($id)
    {
        //error_reporting(E_ALL);
        //ini_set('display_errors',1);
        include "app/third_party/LOG/Log.php";
        
        $mids = array();
        $aux = '';
        $this->load->model('site_model');
        $aux = $this->site_model->getSite($id);
        $token = $aux['token'];
        $i = 0;
        $site = $aux['name'];
        $offset = 0;
        if (isset($_GET['offset']) && $_GET['offset'])
            $offset = $_GET['offset'];

        $this->load->model('advertiser_model');
        $filters['id_site'] = $id;
        $this->load->model('status_model');
        $filters['id_status'] = $this->status_model->getStatusByName('approved');
        $mag = array();
        $mag = $this->advertiser_model->getAdvertisers($filters);
        foreach ($mag as $val) {
            $mids[] = $val['mid'];
        }

        $j = count($mids);

        $cate = 0;

        while ($j > 0) {
            $cats = array();
            $aux = @file_get_contents('http://lld2.linksynergy.com/services/restLinks/getCreativeCategories/' . $token . '/' . $mids[$j - 1]);
            $categories = simplexml_load_string($aux, "SimpleXMLElement", LIBXML_NOCDATA);
            //echo $categories->getName().'<br/>';die;
            if (isset($categories) && is_object($categories)) {
                $kids = $categories->children('ns1', true);
                //var_dump(count($kids));die;

                foreach ($kids as $child) {
                    $cats[$i]['id'] = $i + 1;
                    $cats[$i]['id_site'] = $id;
                    $cats[$i]['cat_id'] = (string) $child->catId;
                    $cats[$i]['name'] = (string) $child->catName;
                    $cats[$i]['mid'] = (string) $child->mid;
                    $cats[$i]['nid'] = (string) $child->nid;
                    //$cats[$i]['limit'] = 10;
                    //$cats[$i]['offset'] = $offset;
                    $i++;
                }

                $cate += count($cats);

                $this->load->model('category_creative_model');
                //delete old categories for this mid and this site id
                $this->category_creative_model->deleteCategoryByMid($id, $mids[$j - 1]);

                foreach ($cats as $cat) {
                    $cat['id'] = '';
                    $this->category_creative_model->newCategory($cat);
                }

                //print '<pre>';print_r($cats);die;
            } else {
                $message = 'http://lld2.linksynergy.com/services/restLinks/getCreativeCategories/' . $token . '/' . $mids[$j - 1] . ' xml eroare ';
                Log::error($message);
            }
            $j--;
        }

        $this->admin_navigation->module_link('See parsed creative category', site_url('admincp2/linkshare/listCreativeCategory/' . $id));

        $this->load->library('dataset');

        $columns = array(
            array(
                'name' => 'Parsed creative categories',
                'width' => '50%'),
            array(
                'name' => 'Site ID',
                'width' => '50%'),
        );

        $catz = array();
        $catz[0]['site'] = $site;
        $catz[0]['cate'] = $cate;

        $this->dataset->columns($columns);
        $this->dataset->datasource('category_creative_model', 'parseCategories', $catz);
        $this->dataset->base_url(site_url('admincp2/linkshare/parseCreativeCategories/' . $id));
        $this->dataset->rows_per_page(10);

        // total rows
        $total_rows = 1;
        $this->dataset->total_rows($total_rows);

        $this->dataset->initialize();

        $this->load->view('listCreativeCategoryParsed');
    }
    
     function updateFilters() {
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

    public function joinCreativeCategory($id = 1)
    {
        $this->admin_navigation->module_link('View Merged Categories', site_url('admincp2/linkshare/listMergedCategories/'));

        $this->load->library('dataset');
        $this->load->model('category_creative_model');

        $columns = array(
            array(
                'name' => 'ID #',
                'width' => '5%'),
            array(
                'name' => 'SELECT CATEG TO MERGE',
                'width' => '10%'),
            array(
                'name' => 'SITE',
                'width' => '15%'),
            array(
                'name' => 'CATEGORY ID #',
                'width' => '30%'),
            array(
                'name' => 'Name',
                'width' => '20%',
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
                'name' => 'Actions',
                'width' => '5%'
            )
        );

        $filters = $filters_decode = array();
        //$filters['limit'] = 50;
        $filters['id_site'] = $id;
        $filters['name'] = true; 
        
        if (isset($_GET['offset'])){
        $filters['offset'] = $_GET['offset'];}
       
        if (isset($_GET['filterz'])) {
            $filters_decode = unserialize(base64_decode($this->asciihex->HexToAscii($_GET['filterz'])));            
        } elseif (isset($_POST['filterz'])) {
            $filters_decode = unserialize(base64_decode($this->asciihex->HexToAscii($_POST['filterz'])));            
        }
        
//        if (isset($filters_decode['nume']))
//                $filters['nume'] = $filters_decode['nume'];
//        
//        $filters['offset'] = $filters_decode['offset'];
       
        if (isset($filters_decode) && !empty($filters_decode)) {
            foreach ($filters_decode as $key => $val) {
                $filters[$key] = $val;
            }
        }
       
        foreach ($_POST as $key => $val) {
            if (in_array($val, array('filter results'))) {
                unset($_POST[$key]);
            }
        }
        
        print '<pre>FILTERS DECODE';
        print_r($filters_decode);
        print '</pre>';      
 
        if ($_POST['saving'] == 'ok'){
            if (isset($_POST['merged_category']) && !empty($filters_decode['check_category'])){
                $checked_values = array();
                $checked_values = explode(',', $filters_decode['check_category']);
                if(!empty($_POST['check_category'])){
                    foreach ($_POST['check_category'] as $cat) {
                        if ($cat) {
                            //curent checkboxes
                            $checked_values[] = $cat;
                        }
                    }
                }  
                $id_merged_category = $this->category_creative_model->newMergedCategory($_POST['merged_category']);            
                $id_join_category = $this->category_creative_model->newJoinCategory($id_merged_category,$checked_values);
                
                $message = "New Merged Category (".$_POST['merged_category'].") added successfully.";
                $this->notices->SetNotice($message);
                redirect(site_url('admincp2/linkshare/joinCreativeCategory/1'));
                
            }elseif(isset($_POST['merged_category']) && !empty($_POST['check_category'])){
                $id_merged_category = $this->category_creative_model->newMergedCategory($_POST['merged_category']);            
                $this->category_creative_model->newJoinCategory($id_merged_category,$_POST['check_category']);

                $message = "New Merged Category (".$_POST['merged_category'].") added successfully.";
                $this->notices->SetNotice($message);
                redirect(site_url('admincp2/linkshare/joinCreativeCategory/1'));

            }  else {
                $this->notices->SetNotice('Check if any checkboxes are selected!');
                redirect(site_url('admincp2/linkshare/joinCreativeCategory/1'));
            }
        }

//        $filters['nume'] = $filters_decode['nume'];
//        
//        $filters['mid'] = $filters_decode['mid'];
        
        $this->dataset->columns($columns);
        $this->dataset->datasource('category_creative_model', 'getCreativeForMerge', $filters);
        $this->dataset->base_url(site_url('admincp2/linkshare/joinCreativeCategory/' . $id));


        if (isset($_GET['nume']))
            $filters['nume'] = $_GET['nume'];

        if (isset($_GET['mid']))
            $filters['mid'] = $_GET['mid'];
        
        // &limit=10&offset=20
        if (isset($_POST['offset'])) {
            $_GET['offset'] = $_POST['offset'];
        } else {
            $_GET['offset'] = $filters_decode['offset'];
        }
        
        if (isset($_POST['limit'])) {
            $_GET['limit'] = $_POST['limit'];
        }
        
        print '<pre>POST';
        print_r($_POST);        
        print '</pre>';
        
        print '<pre>GET';        
        print_r($_GET);
        print '</pre>';

        $this->load->library('asciihex');
        $this->load->model('forms/form_model');

        // total rows
        $this->db->where('id_site', $id);
        $total_rows = $this->category_creative_model->getCategoriesLines($filters);

        $this->dataset->total_rows($total_rows); 
        
        $this->dataset->initialize();

        // add actions
        
        $data = array(
          'filterz' => isset($_GET['filterz']) ? $_GET['filterz'] : $_POST['filterz'],
          'name_search' => $filters['nume'],
          'mid_search' => $filters['mid'],
          'name_merged' => $filters['merged_category']
        );

        $this->load->view('joinCreativeCategory', $data);
    }
    
    public function listMergedCategories()
    {
        $this->admin_navigation->module_link('ADD ANOTHER Merged Categories', site_url('admincp2/linkshare/joinCreativeCategory'));

        $this->load->library('dataset');
        $this->load->model('category_creative_model');

        $columns = array(
            array(
                'name' => 'Merged Category Name',
                'width' => '20%'),
            array(
                'name' => 'ID merged categories',
                'width' => '20%'),
            array(
                'name' => 'Actions',
                'width' => '15%'
            )
        );

//        echo "<pre>";
//        print_r($result);
//        echo "</pre>";
//        die;
        
        $filters = array();
        $this->dataset->columns($columns);
        $this->dataset->datasource('category_creative_model','listMergedCategory', $filters);
        $this->dataset->base_url(site_url('admincp2/linkshare/listMergedCategories'));
        $this->dataset->rows_per_page(10);

        // total rows
        //$total_rows = $this->db->get('linkshare_categories')->num_rows();
        $this->dataset->total_rows($total_rows);

        $this->dataset->initialize();

        // add actions
        $this->dataset->action('Delete', 'admincp2/linkshare/deleteMergedCatory');

        $this->load->view('listMergedCategory');
    }
    
    public function editMergedCategory($id)
    {
        $this->load->model('category_creative_model');
        $fields['name'] = $this->input->post('category_name');
        
        $category_name = $this->category_creative_model->getMergedCategoryByID($id);
                
//        echo "<pre>";
//        print_r($category_name);
//        echo "</pre>";
//        die;

        if (!empty($fields['name']) && isset($fields['name'])) {
            
            $this->category_creative_model->updateMergedCategory($fields, $id);
            $this->notices->SetNotice('Category updated successfully.');

            redirect('admincp2/linkshare/listMergedCategories/');
            return true;
        }
        
        $data = array(
               	'merged_category_name' => $category_name[0]['name'],
                'form_action' => site_url('admincp2/linkshare/editMergedCategory/'.$id),
	);
        
    	$this->load->view('editMergedCategory',$data);

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
        return TRUE;
    }
    
    function ajaxDeleteCategory($MergedCategory_id, $JoinsCategory_id){
        $this->load->model('category_creative_model');
        $this->category_creative_model->deleteJoinCategory($MergedCategory_id, $JoinsCategory_id);
        
        $message = "Joins Category id:".$JoinsCategory_id." from Merged Category id:".$MergedCategory_id."deleted successfully.";
        $this->notices->SetNotice($message);
        
        $return_url = site_url('admincp2/linkshare/listMergedCategories');
        redirect($return_url);
        return TRUE;
    }
    
    function export_csv($table) {
        // Create the real model name based on the $table variable
        $model = $table.'_model';
        
        // Since we might be dealing with very large data
        // Ensure we have time to process it
        set_time_limit(0);
        
        $temp_file = FCPATH . 'writeable/' . $table . '-' . date("Y-m-d") . '.csv';

        $this->load->helper('file');
        $this->load->library('array_to_csv');
        $this->load->model($model);

        // If the file already exists, we need
        // to get rid of it since this is a new download.
        $f = @fopen($temp_file, 'r+');
        if ($f !== false)
        {
            ftruncate($f, 0);
            fclose($f);
        }

        $csv_file = fopen($temp_file, 'a');
        $need_header = true;    // Do we need the CSV header?

        $query = $this->$model->get_all_categories();
        
        foreach($query as $row)
        {
            // ID(category id);Active (0/1);Name *;Parent category;Root category (0/1);Description;Meta title;Meta keywords;Meta description;URL rewritten;Image URL;ID / Name of shop(MID)
            $main_array[] = array(
                'id' => $row->cat_id,
                'active' => 1,
                'name' => $row->name,
                'parent' => 0,
                'root' => 1,
                'description' => $row->name,
                'meta_title' => $row->name,
                'meta_keywords' => $row->name,
                'meta_description' => $row->name,
                'url_rewritten' => '',
                'image_url' => '',
                'shop_name' => $row->mid
            );
        }
        $this->array_to_csv->input($main_array);
        $data = $this->array_to_csv->output($need_header, true);
        fwrite($csv_file, $data);
        
        // Make sure we're not output buffering anymore so we don't
        // run out of memory due to buffering.
        if (ob_get_level() > 0)
        {
            ob_end_flush();
        }
        //ob_implicit_flush(true);

        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=export-" . $table . '-' . date("Y-m-d") . ".csv");
        readfile($temp_file);
        die();
    }
  
    public function getXmlCreativeCategories()
    {            
        $CI =& get_instance();
        $this->load->library('admin_form'); 
        $form = new Admin_form;

        $this->load->model(array('site_model','advertiser_model'));
        
        $siteRow = $this->site_model->getSiteBySID($this->siteID);

        $temp = $this->advertiser_model->getTempAdvertisers($siteID['id']);
        $current = array_merge($this->advertiser_model->getAdvertisers(array('id_status' => 1,'id_site'=>$siteRow['id'])),
                               $this->advertiser_model->getAdvertisers(array('id_status' => 2,'id_site'=>$siteRow['id'])));
        
        foreach ($current as $val){
            $allMids[] = array( 'mid' => $val['mid'],'name' => $val['name']);
        }
     
//        echo "<pre>";
//        print_r($_GET['mid']);
//        die();
        $this->admin_navigation->module_link('Back', site_url('admincp/linkshare/getXML/'));

        $responseObj = array();

        if(isset($_GET['mid'])){
            
            $categoriesRequestUrl = 'https://api.rakutenmarketing.com/linklocator/1.0/getCreativeCategories/'.$_GET['mid'];
            
            $config = new LinkshareConfig();

            $accessToken = $config->setSiteCookieAndGetAccessToken($CI, $this->siteID);

            //echo "accessToken = $accessToken" . PHP_EOL;

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
//        print_r($_GET['status']);
//        die();                  
        
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
