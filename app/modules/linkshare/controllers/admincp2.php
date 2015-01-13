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

class Admincp2 extends Admincp_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->admin_navigation->parent_active('linkshare');

        //error_reporting(E_ALL^E_NOTICE);
        //error_reporting(E_WARNING);
    }

    public function index()
    {
        redirect('admincp2/linkshare/listCategories');
    }

    public function listCategories()
    {
        $this->admin_navigation->module_link('Adauga categorie', site_url('admincp2/linkshare/addCategory'));
        $this->admin_navigation->module_link('Parseaza categorii linkshare', site_url('admincp2/linkshare/parseCategories'));

        $this->load->library('dataset');

        $columns = array(
            array(
                'name' => 'ID #',
                'width' => '15%'),
            array(
                'name' => 'CATEGORY ID #',
                'width' => '15%'),
            array(
                'name' => 'Nume',
                'width' => '40%'),
            array(
                'name' => 'Operatii',
                'width' => '30%'
            )
        );

        $this->dataset->columns($columns);
        $this->dataset->datasource('category_model', 'get_categorii');
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

        $form->fieldset('Categorie noua');
        $form->text('Id categorie linkshare', 'id_category', '', 'Introduceti numele categoriei cum e pe linkshare', true, 'e.g. 17', true);
        $form->text('Nume', 'name', '', 'Introduceti numele categoriei', true, 'e.g. Business & Career', true);

        $data = array(
            'form' => $form->display(),
            'form_title' => 'Adauga categorie',
            'form_action' => site_url('admincp2/linkshare/addCategoryValidate'),
            'action' => 'new'
        );

        $this->load->view('addCategory', $data);
    }

    public function addCategoryValidate($action = 'new', $id = false)
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('name', 'Nume', 'required|trim');
        $this->form_validation->set_rules('id_category', 'Id categorie linkshare', 'required|trim');

        if ($this->form_validation->run() === false) {
            $this->notices->SetError('Campuri obligatorii.');
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
            $this->category_model->new_categorie($fields);

            $this->notices->SetNotice('Categorie adaugata cu succes.');

            redirect('admincp2/linkshare/listCategories/');
        } else {
            $this->category_model->update_categorie($fields, $id);
            $this->notices->SetNotice('Categorie actualizata cu succes.');

            redirect('admincp2/linkshare/listCategories/');
        }

        return true;
    }

    public function editCategory($id)
    {
        $this->load->model('category_model');
        $categorie = $this->category_model->get_categorie($id);

        if (empty($categorie)) {
            die(show_error('Nu exista nici o categorie cu acest ID.'));
        }

        $this->load->library('admin_form');
        $form = new Admin_form;

        $form->fieldset('Categorie');
        $form->text('Id category linkshare', 'id_category', $categorie['id_category'], 'Introduceti id-ul categoriei cum e pe linkshare.', true, 'e.g., 17', true);
        $form->text('Nume', 'name', $categorie['name'], 'Introduceti numele categoriei.', true, 'e.g., Business & Career', true);

        $data = array(
            'form' => $form->display(),
            'form_title' => 'Editare Categorie',
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

        $this->notices->SetNotice('Categorie stearsa cu succes.');

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
        $this->admin_navigation->module_link('Actualizeaza categorii linkshare', site_url('admincp2/linkshare/refreshCategories/'));

        $categories = $this->parseCategoryUrl();

        $this->load->library('dataset');

        $columns = array(
            array(
                'name' => 'Category ID #',
                'width' => '35%'),
            array(
                'name' => 'Nume',
                'width' => '65%'),
        );

        $filters['categories'] = $categories;
        $filters['limit'] = 50;
        if (isset($_GET['offset']))
            $filters['offset'] = $_GET['offset'];
        $this->dataset->rows_per_page(50);

        $this->dataset->columns($columns);
        $this->dataset->datasource('category_model', 'get_categorii_parse', $filters);
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
        $this->admin_navigation->module_link('Actualizeaza categorii linkshare', site_url('admincp2/linkshare/refresh_categorii_site/' . $id));

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
                'name' => 'Nume',
                'width' => '65%'),
        );

        $filters['categories'] = $categories;
        $filters['limit'] = 50;
        if (isset($_GET['offset']))
            $filters['offset'] = $_GET['offset'];
        $this->dataset->rows_per_page(50);

        $this->dataset->columns($columns);
        $this->dataset->datasource('category_model', 'get_categorii_parse', $filters);
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
        $this->admin_navigation->module_link('Adauga categorie creative', site_url('admincp2/linkshare/addCreativeCategory'));
        $this->admin_navigation->module_link('Parseaza categorii creative linkshare si adauga in db', site_url('admincp2/linkshare/parseCreativeCategories/' . $id));

        $this->load->library('dataset');
        $this->load->model('category_creative_model');

        $columns = array(
            array(
                'name' => 'ID #',
                'width' => '15%'),
            array(
                'name' => 'SITE',
                'width' => '15%'),
            array(
                'name' => 'CATEGORY ID #',
                'width' => '10%'),
            array(
                'name' => 'Nume',
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
                'name' => 'Operatii',
                'width' => '20%'
            )
        );

        $filters = array();
        $filters['limit'] = 50;
        $filters['id_site'] = $id;
        $filters['name'] = true;

        $this->dataset->columns($columns);
        $this->dataset->datasource('category_creative_model', 'get_categorii', $filters);
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
        $total_rows = $this->category_creative_model->get_categorii_linii($filters);
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

        $form->fieldset('Categorie creative noua');
        $form->text('Id site linkshare', 'id_site', '', 'Introduceti id site', true, 'e.g., 1', true);
        $form->text('Id categorie creative', 'cat_id', '', 'Introduceti id categorie cum e pe linkshare', true, 'e.g., 200229205', true);
        $form->text('Nume', 'name', '', 'Introduceti numele categoriei', true, 'e.g., MAT (Mission Against Terror)', true);
        $form->text('Mid linkshare', 'mid', '', 'Introduceti mid', true, 'e.g., 37517', true);
        $form->text('Nid linkshare', 'nid', '', 'Introduceti nid', true, 'e.g., 1', true);

        $data = array(
            'form' => $form->display(),
            'form_title' => 'Adauga categorie',
            'form_action' => site_url('admincp2/linkshare/addCreativeCategoryValidate'),
            'action' => 'new'
        );

        $this->load->view('addCreativeCategory', $data);
    }

    public function addCreativeCategoryValidate($action = 'new', $id = false)
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('name', 'Nume', 'required|trim');
        $this->form_validation->set_rules('cat_id', 'Id categorie creative linkshare', 'required|trim');

        if ($this->form_validation->run() === false) {
            $this->notices->SetError('Campuri obligatorii.');
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
            $type_id = $this->category_creative_model->new_categorie($fields);

            $this->notices->SetNotice('Categorie creative adaugata cu succes.');

            redirect('admincp2/linkshare/listCreativeCategory/');
        } else {
            $this->category_creative_model->update_categorie($fields, $id);
            $this->notices->SetNotice('Categorie creative actualizata cu succes.');

            redirect('admincp2/linkshare/listCreativeCategory/');
        }

        return true;
    }

    public function editCreativeCategory($id)
    {
        $this->load->model('category_creative_model');
        $categorie = $this->category_creative_model->get_categorie($id);

        if (empty($categorie)) {
            die(show_error('Nu exista nici o categorie cu acest ID.'));
        }

        $this->load->library('admin_form');
        $form = new Admin_form;

        $form->fieldset('Categorie creative');
        $form->text('Id site linkshare', 'id_site', $categorie['id_site'], 'Introduceti id site', true, 'e.g., 1', true);
        $form->text('Id categorie creative', 'cat_id', $categorie['cat_id'], 'Introduceti id categorie cum e pe linkshare', true, 'e.g., 200229205', true);
        $form->text('Nume', 'name', $categorie['name'], 'Introduceti numele categoriei', true, 'e.g., MAT (Mission Against Terror)', true);
        $form->text('Mid linkshare', 'mid', $categorie['mid'], 'Introduceti mid', true, 'e.g., 37517', true);
        $form->text('Nid linkshare', 'nid', $categorie['nid'], 'Introduceti nid', true, 'e.g., 1', true);

        $data = array(
            'form' => $form->display(),
            'form_title' => 'Editare Categorie Creative',
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

        $this->notices->SetNotice('Categorie creative stearsa cu succes.');

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
        $aux = $this->site_model->get_site($id);
        $token = $aux['token'];
        $i = 0;
        $site = $aux['name'];
        $offset = 0;
        if (isset($_GET['offset']) && $_GET['offset'])
            $offset = $_GET['offset'];

        $this->load->model('advertiser_model');
        $filters['id_site'] = $id;
        $this->load->model('status_model');
        $filters['id_status'] = $this->status_model->get_status_by_name('approved');
        $mag = array();
        $mag = $this->advertiser_model->get_magazine($filters);
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
                $this->category_creative_model->delete_categorie_by_mid($id, $mids[$j - 1]);

                foreach ($cats as $cat) {
                    $cat['id'] = '';
                    $this->category_creative_model->new_categorie($cat);
                }

                //print '<pre>';print_r($cats);die;
            } else {
                $message = 'http://lld2.linksynergy.com/services/restLinks/getCreativeCategories/' . $token . '/' . $mids[$j - 1] . ' xml eroare ';
                Log::error($message);
            }
            $j--;
        }

        $this->admin_navigation->module_link('Vezi categoriile creative parsate', site_url('admincp2/linkshare/listCreativeCategory/' . $id));

        $this->load->library('dataset');

        $columns = array(
            array(
                'name' => 'Creative Categories Parsed',
                'width' => '50%'),
            array(
                'name' => 'ID Site',
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
                'name' => 'Nume',
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
                'name' => 'Operatii',
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
                $id_merged_category = $this->category_creative_model->new_merged_category($_POST['merged_category']);            
                $id_join_category = $this->category_creative_model->new_join_category($id_merged_category,$checked_values);
                
                $message = "New Merged Category (".$_POST['merged_category'].") added successfully.";
                $this->notices->SetNotice($message);
                redirect(site_url('admincp2/linkshare/joinCreativeCategory/1'));
                
            }elseif(isset($_POST['merged_category']) && !empty($_POST['check_category'])){
                $id_merged_category = $this->category_creative_model->new_merged_category($_POST['merged_category']);            
                $this->category_creative_model->new_join_category($id_merged_category,$_POST['check_category']);

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
        $this->dataset->datasource('category_creative_model', 'get_creative_for_merge', $filters);
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
        $total_rows = $this->category_creative_model->get_categorii_linii($filters);

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
//            array(
//                'name' => 'Contained Categ Name',
//                'width' => '20%'),
//            array(
//                'name' => 'Contained Categ MID',
//                'width' => '20%'),
            array(
                'name' => 'Operatii',
                'width' => '15%'
            )
        );

//        echo "<pre>";
//        print_r($result);
//        echo "</pre>";
//        die;
        
        $filters = array();
        $this->dataset->columns($columns);
        $this->dataset->datasource('category_creative_model','list_merged_category', $filters);
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
        
        $category_name = $this->category_creative_model->get_merged_category_by_id($id);
                
//        echo "<pre>";
//        print_r($category_name);
//        echo "</pre>";
//        die;

        if (!empty($fields['name']) && isset($fields['name'])) {
            
            $this->category_creative_model->update_merged_category($fields, $id);
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
            $this->category_creative_model->delete_merged_category($content);
        }
        
        $this->notices->SetNotice('Merged Category deleted successfully.');
        redirect($return_url);
        return TRUE;
    }
    
    function ajaxDeleteCategory($MergedCategory_id, $JoinsCategory_id){
        $this->load->model('category_creative_model');
        $this->category_creative_model->delete_join_category($MergedCategory_id, $JoinsCategory_id);
        
        $message = "Joins Category id:".$JoinsCategory_id." from Merged Category id:".$MergedCategory_id."deleted successfully.";
        $this->notices->SetNotice($message);
        
        $return_url = site_url('admincp2/linkshare/listMergedCategories');
        redirect($return_url);
        return TRUE;
    }

}
