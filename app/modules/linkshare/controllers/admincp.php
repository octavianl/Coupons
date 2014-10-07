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

class Admincp extends Admincp_Controller
{
    public function __construct() 
    {
        parent::__construct();

        $this->admin_navigation->parent_active('annonces');

        //error_reporting(E_ALL^E_NOTICE);
        //error_reporting(E_WARNING);
    }

    public function index()
    {
        redirect('admincp/linkshare/siteAdvertisers/1');
    }

    public function listCategories()
    {
        $this->admin_navigation->module_link('Adauga categorie', site_url('admincp/linkshare/addCategory'));
        $this->admin_navigation->module_link('Parseaza categorii linkshare', site_url('admincp/linkshare/parseCategories'));

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
        $this->dataset->datasource('categorie_model', 'get_categorii');
        $this->dataset->base_url(site_url('admincp/linkshare/listCategories'));
        $this->dataset->rows_per_page(1000);

        // total rows
        $total_rows = $this->db->get('linkshare_categorie')->num_rows();
        $this->dataset->total_rows($total_rows);

        $this->dataset->initialize();

        // add actions
        $this->dataset->action('Delete', 'admincp/linkshare/deleteCategory');

        $this->load->view('listCategories');
    }

    public function addCategory()
    {
        $this->load->library('admin_form');
        $form = new Admin_form;

        $form->fieldset('Categorie noua');
        $form->text('Id categorie linkshare', 'id_category', '', 'Introduceti numele categoriei cum e pe linkshare', true, 'e.g., 17', true);
        $form->text('Nume', 'name', '', 'Introduceti numele categoriei', true, 'e.g., Business & Career', true);

        $data = array(
            'form' => $form->display(),
            'form_title' => 'Adauga categorie',
            'form_action' => site_url('admincp/linkshare/addCategoryValidate'),
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
                redirect('admincp/linkshare/addCategory');
                return false;
            } else {
                redirect('admincp/linkshare/editCategory/' . $id);
                return false;
            }
        }

        $this->load->model('categorie_model');

        $fields['name'] = $this->input->post('name');

        if ($action == 'new') {
            $type_id = $this->categorie_model->new_categorie($fields);

            $this->notices->SetNotice('Categorie adaugata cu succes.');

            redirect('admincp/linkshare/listCategories/');
        } else {
            $this->categorie_model->update_categorie($fields, $id);
            $this->notices->SetNotice('Categorie actualizata cu succes.');

            redirect('admincp/linkshare/listCategories/');
        }

        return true;
    }

    public function editCategory($id)
    {
        $this->load->model('categorie_model');
        $categorie = $this->categorie_model->get_categorie($id);

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
            'form_action' => site_url('admincp/linkshare/addCategoryValidate/edit/' . $categorie['id']),
            'action' => 'edit',
        );

        $this->load->view('addCategory', $data);
    }

    public function deleteCategory($contents, $return_url)
    {

        $this->load->library('asciihex');
        $this->load->model('categorie_model');

        $contents = unserialize(base64_decode($this->asciihex->HexToAscii($contents)));
        $return_url = base64_decode($this->asciihex->HexToAscii($return_url));

        foreach ($contents as $content) {
            $this->categorie_model->deleteCategory($content);
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
        $this->admin_navigation->module_link('Actualizeaza categorii linkshare', site_url('admincp/linkshare/refreshCategories/'));

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
        $this->dataset->datasource('categorie_model', 'get_categorii_parse', $filters);
        $this->dataset->base_url(site_url('admincp/linkshare/parseCategories'));


        // total rows
        $total_rows = count($categories);
        $this->dataset->total_rows($total_rows);

        $data['cate'] = $total_rows;
        $this->dataset->initialize();

        $this->load->view('parseCategories', $data);
    }

    public function refreshCategories() {
        $this->db->query("TRUNCATE TABLE linkshare_categorie");
        $categories = $this->parseCategoryUrl();

        foreach ($categories as $cat) {
            $this->db->query("INSERT INTO  linkshare_categorie(id_category,name) VALUES('{$cat['id_category']}','{$cat['name']}')");
        }

        $data['cate'] = count($categories);

        $this->load->view('refreshCategories', $data);
    }

    public function listProducts($id_site, $mid)
    {
        $this->admin_navigation->module_link('Inapoi la magazine', site_url('admincp/linkshare/siteAdvertisers/' . $id_site));

        $this->load->library('dataset');

        $columns = array(
            array(
                'name' => 'ID #',
                'width' => '5%'),
            array(
                'name' => 'Site',
                'width' => '1%'),
            array(
                'name' => 'MID',
                'width' => '1%'),
            array(
                'name' => 'Magazin',
                'width' => '5%'),
            array(
                'name' => 'Creat_cat_ID',
                'width' => '2%',
                'type' => 'text',
                'filter' => 'cat_creative_id'),
            array(
                'name' => 'Cat Creativa',
                'width' => '9%',
                'type' => 'text',
                'filter' => 'cat_creative_name'),
            array(
                'name' => 'Primary',
                'width' => '4%'),
            array(
                'name' => 'Secondary',
                'width' => '4%'),
            array(
                'name' => 'Produs',
                'width' => '10%'),
            array(
                'name' => 'Image',
                'width' => '10%'),
            array(
                'name' => 'Description',
                'width' => '10%'),
            array(
                'name' => 'Price',
                'width' => '5%'),
            array(
                'name' => 'Pricelist',
                'width' => '5%'),
            array(
                'name' => 'Currency',
                'width' => '5%'),
            array(
                'name' => 'Linkid',
                'width' => '5%'),
            array(
                'name' => 'SKU',
                'width' => '5%'),
            array(
                'name' => 'Link',
                'width' => '5%'),
            array(
                'name' => 'Available',
                'width' => '5%'),
            array(
                'name' => 'Operatii',
                'width' => '5%'
            )
        );
        /*
          //click url
          http://click.linksynergy.com/fs-bin/click?id=eRClQ*mKkgw&offerid=270100.1772&type=2 BAZA
          //icon url
          http://www.glam-net.com/media/catalog/product/g/n/gn_ring.jpg BAZA
          //show url
          http://ad.linksynergy.com/fs-bin/show?id=eRClQ*mKkgw&bids=270100.1772&type=2 varza nu e bun de nimik
          //link url
          http://click.linksynergy.com/link?id=eRClQ*mKkgw&offerid=270100.1772&type=15&murl=http%3A%2F%2Fwww.glam-net.com%2Fniin-cosmo-agate-ring.html are in plus url de redirect
          //image url
          http://www.glam-net.com/media/catalog/product/g/n/gn_ring.jpg same shit
         * 
         */

        $filters = array();
        $filters['limit'] = 20;

        if (isset($_GET['offset']))
            $filters['offset'] = $_GET['offset'];
        if (isset($_GET['cat_creative_id']))
            $filters['cat_creative_id'] = $_GET['cat_creative_id'];

        if (isset($_GET['filters'])) {
            $aux = unserialize(base64_decode($this->asciihex->HexToAscii($_GET['filters'])));
            if (isset($aux['cat_creative_id']))
                $filters['cat_creative_id'] = $aux['cat_creative_id'];
            if (isset($aux['cat_creative_name']))
                $filters['cat_creative_name'] = $aux['cat_creative_name'];
        }

        $filters['id_site'] = $id_site;
        $filters['mid'] = $mid;

        $this->dataset->columns($columns);
        $this->dataset->datasource('produs_model', 'get_produse_by_mid', $filters);
        $this->dataset->base_url(site_url('admincp/linkshare/listProducts/' . $id_site . '/' . $mid));
        $this->dataset->rows_per_page(20);

        $this->load->model('magazin_model');

        // total rows
        $total_rows = $this->magazin_model->get_count_produse_by_mid($mid, $id_site, $filters);
        $this->dataset->total_rows($total_rows);

        $this->dataset->initialize();

        // add actions
        $this->dataset->action('Delete', 'admincp/linkshare/delete_produs');

        $magazin = '';

        $this->load->model('magazin_model');
        $aux = $this->magazin_model->get_magazin_by_mid($mid, $id_site);
        if ($aux)
            $magazin = $aux['name'];


        $data = array(
            'mid' => $mid,
            'magazin' => $magazin
        );

        $this->load->view('listProducts', $data);
    }

    public function change_produs_status($id_product, $ret_url)
    {
        $ret_url = base64_decode($ret_url);
        $this->load->model('produs_model');
        $this->produs_model->change_produs_status($id_product);
        echo '<META http-equiv="refresh" content="0;URL=' . $ret_url . '">';
    }

    public function listSites()
    {
        $this->admin_navigation->module_link('Adauga site', site_url('admincp/linkshare/addSite/'));

        $this->load->library('dataset');

        $columns = array(
            array(
                'name' => 'ID #',
                'width' => '10%'),
            array(
                'name' => 'Nume',
                'width' => '20%'),
            array(
                'name' => 'Token',
                'width' => '70%'),
            array(
                'name' => 'Operatii',
                'width' => '30%'
            ),
            array(
                'name' => 'Info',
                'width' => '30%'
            )
        );

        $this->dataset->columns($columns);
        $this->dataset->datasource('site_model', 'get_sites');
        $this->dataset->base_url(site_url('admincp/linkshare/listSites'));
        $this->dataset->rows_per_page(10);

        // total rows
        $total_rows = $this->db->get('linkshare_site')->num_rows();
        $this->dataset->total_rows($total_rows);

        $this->dataset->initialize();

        // add actions
        //$this->dataset->action('Delete','admincp/linkshare/deleteSite');

        $this->load->view('listSites');
    }

    public function addSite()
    {
        $this->load->library('admin_form');
        $form = new Admin_form;

        $form->fieldset('Site nou');
        $form->text('Nume site', 'name', '', 'Introduceti nume site.', true, 'e.g., couponland.com', true);
        $form->text('Token site', 'token', '', 'Introduceti token site.', true, '', true);


        $data = array(
            'form' => $form->display(),
            'form_title' => 'Adauga site',
            'form_action' => site_url('admincp/linkshare/addSiteValidate'),
            'action' => 'new'
        );

        $this->load->view('addSite', $data);
    }

    public function addSiteValidate($action = 'new', $id = false)
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('name', 'Nume site', 'required|trim');
        $this->form_validation->set_rules('token', 'Token site', 'required|trim');

        if ($this->form_validation->run() === false) {
            $this->notices->SetError('Aveti erori in formular.');
            $error = true;
        }

        if (isset($error)) {
            if ($action == 'new') {
                redirect('admincp/linkshare/addSite');
                return false;
            } else {
                redirect('admincp/linkshare/editSite/' . $id);
                return false;
            }
        }

        $fields['name'] = $this->input->post('name');
        $fields['token'] = $this->input->post('token');

        $this->load->model('site_model');

        if ($action == 'new') {
            $type_id = $this->site_model->new_site($fields);

            $this->notices->SetNotice('Site adaugat cu succes.');

            redirect('admincp/linkshare/listSites/');
        } else {
            $this->site_model->update_site($fields, $id);
            $this->notices->SetNotice('Site actualizat cu succes.');

            redirect('admincp/linkshare/listSites/');
        }

        return true;
    }

    public function editSite($id)
    {
        $this->load->model('site_model');
        $site = $this->site_model->get_site($id);

        if (empty($site)) {
            die(show_error('Nu exista site cu acest ID.'));
        }

        $this->load->library('admin_form');
        $form = new Admin_form;

        $form->fieldset('Site');
        $form->text('Nume site', 'name', $site['name'], 'Introduceti nume site.', true, 'e.g., couponland', true);
        $form->text('Token site', 'token', $site['token'], 'Introduceti token site.', true, '', true);

        $data = array(
            'form' => $form->display(),
            'form_title' => 'Editare site',
            'form_action' => site_url('admincp/linkshare/addSiteValidate/edit/' . $site['id']),
            'action' => 'edit',
        );

        $this->load->view('addSite', $data);
    }

    public function infoSite($id)
    {
        $this->admin_navigation->module_link('Vezi categorii creative linkshare', site_url('admincp/linkshare/list_categorii_creative/' . $id));
        $this->admin_navigation->module_link('Vezi magazine linkshare', site_url('admincp/linkshare/siteAdvertisers/' . $id));
        $this->admin_navigation->module_link('Vezi produse linkshare', site_url('admincp/linkshare/site_produse/' . $id));

        $this->load->model('site_model');
        $site = $this->site_model->get_site($id);

        if (empty($site)) {
            die(show_error('Nu exista site cu acest ID.'));
        }

        $data = array();
        $data['name'] = $site['name'];

        $this->load->view('infoSite', $data);
    }

    public function parse_categorii_site($id)
    {
        $this->admin_navigation->module_link('Actualizeaza categorii linkshare', site_url('admincp/linkshare/refresh_categorii_site/' . $id));

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
        $this->dataset->datasource('categorie_model', 'get_categorii_parse', $filters);
        $this->dataset->base_url(site_url('admincp/linkshare/parseCategories'));


        // total rows
        $total_rows = count($categories);
        $this->dataset->total_rows($total_rows);

        $data['cate'] = $total_rows;
        $this->dataset->initialize();

        $this->load->view('parseCategories', $data);
    }

    public function deleteSite($contents, $return_url)
    {

        $this->load->library('asciihex');
        $this->load->model('site_model');

        $contents = unserialize(base64_decode($this->asciihex->HexToAscii($contents)));
        $return_url = base64_decode($this->asciihex->HexToAscii($return_url));

        foreach ($contents as $content) {
            $this->site_model->deleteSite($content);
        }

        $this->notices->SetNotice('Site(-uri) sters(e) cu succes.');

        redirect($return_url);

        return true;
    }

    /* networks */

    public function listNetworks()
    {
        $this->admin_navigation->module_link('Adauga network', site_url('admincp/linkshare/addNetwork/'));

        $this->load->library('dataset');

        $columns = array(
            array(
                'name' => 'ID #',
                'width' => '10%'),
            array(
                'name' => 'Network ID',
                'width' => '20%'),
            array(
                'name' => 'Nume',
                'width' => '40%'),
            array(
                'name' => 'Operatii',
                'width' => '30%'
            )
        );

        $this->dataset->columns($columns);
        $this->dataset->datasource('network_model', 'get_networks');
        $this->dataset->base_url(site_url('admincp/linkshare/listNetworks'));
        $this->dataset->rows_per_page(10);

        // total rows
        $total_rows = $this->db->get('linkshare_network')->num_rows();
        $this->dataset->total_rows($total_rows);

        $this->dataset->initialize();

        // add actions
        $this->dataset->action('Delete', 'admincp/linkshare/deleteNetwork');

        $this->load->view('listNetworks');
    }

    public function addNetwork()
    {
        $this->load->library('admin_form');
        $form = new Admin_form;

        $form->fieldset('Network noua');
        $form->text('Nume', 'name', '', 'Introduceti nume network', true, 'e.g., U.S. Network', true);
        $form->text('NID', 'nid', '', 'Introduceti id network', true, 'e.g., 1', true);

        $data = array(
            'form' => $form->display(),
            'form_title' => 'Adauga network',
            'form_action' => site_url('admincp/linkshare/addNetworkValidate'),
            'action' => 'new'
        );

        $this->load->view('addNetwork', $data);
    }

    public function addNetworkValidate($action = 'new', $id = false)
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('name', 'Nume', 'required|trim');
        $this->form_validation->set_rules('nid', 'Network id', 'required|trim');

        if ($this->form_validation->run() === false) {
            $this->notices->SetError('Campuri obligatorii.');
            $error = true;
        }

        if (isset($error)) {
            if ($action == 'new') {
                redirect('admincp/linkshare/addNetwork');
                return false;
            } else {
                redirect('admincp/linkshare/editNetwork/' . $id);
                return false;
            }
        }

        $this->load->model('network_model');

        $fields['name'] = $this->input->post('name');
        $fields['nid'] = $this->input->post('nid');

        if ($action == 'new') {
            $type_id = $this->network_model->new_network($fields);

            $this->notices->SetNotice('Network adaugata cu succes.');

            redirect('admincp/linkshare/listNetworks/');
        } else {
            $this->network_model->update_network($fields, $id);
            $this->notices->SetNotice('network actualizata cu succes.');

            redirect('admincp/linkshare/listNetworks/');
        }

        return true;
    }

    public function editNetwork($id)
    {
        $this->load->model('network_model');
        $network = $this->network_model->get_network($id);

        if (empty($network)) {
            die(show_error('Nu exista nici o network cu acest ID.'));
        }

        $this->load->library('admin_form');
        $form = new Admin_form;

        $form->fieldset('network');
        $form->text('Nume', 'name', $network['name'], 'Introduceti nume network.', true, 'e.g., U.S. Network', true);
        $form->text('Nid', 'nid', $network['nid'], 'Introduceti network ID.', true, 'e.g., 1', true);

        $data = array(
            'form' => $form->display(),
            'form_title' => 'Editare network',
            'form_action' => site_url('admincp/linkshare/addNetworkValidate/edit/' . $network['id']),
            'action' => 'edit',
        );

        $this->load->view('addNetwork', $data);
    }

    public function deleteNetwork($contents, $return_url)
    {

        $this->load->library('asciihex');
        $this->load->model('network_model');

        $contents = unserialize(base64_decode($this->asciihex->HexToAscii($contents)));
        $return_url = base64_decode($this->asciihex->HexToAscii($return_url));

        foreach ($contents as $content) {
            $this->network_model->deleteNetwork($content);
        }

        $this->notices->SetNotice('network stearsa cu succes.');

        redirect($return_url);

        return true;
    }

    /* status */

    public function listStatus()
    {
        $this->admin_navigation->module_link('Adauga status', site_url('admincp/linkshare/addStatus/'));

        $this->load->library('dataset');

        $columns = array(
            array(
                'name' => 'ID #',
                'width' => '10%'),
            array(
                'name' => 'status ID',
                'width' => '20%'),
            array(
                'name' => 'Nume',
                'width' => '20%'),
            array(
                'name' => 'Descriere',
                'width' => '40%'),
            array(
                'name' => 'Operatii',
                'width' => '10%'
            )
        );

        $this->dataset->columns($columns);
        $this->dataset->datasource('status_model', 'get_statuses');
        $this->dataset->base_url(site_url('admincp/linkshare/listStatus'));
        $this->dataset->rows_per_page(10);

        // total rows
        $total_rows = $this->db->get('linkshare_status')->num_rows();
        $this->dataset->total_rows($total_rows);

        $this->dataset->initialize();

        // add actions
        $this->dataset->action('Delete', 'admincp/linkshare/deleteStatus');

        $this->load->view('listStatus');
    }

    public function addStatus()
    {
        $this->load->library('admin_form');
        $form = new Admin_form;

        $form->fieldset('status nou');
        $form->text('SID', 'id_status', '', 'Introduceti id status', true, 'e.g., approved', true);
        $form->text('Nume', 'name', '', 'Introduceti nume status', true, 'e.g., Approved', true);
        $form->textarea('Descriere', 'description', '', 'Introduceti descriere status', false, '', true);

        $data = array(
            'form' => $form->display(),
            'form_title' => 'Adauga status',
            'form_action' => site_url('admincp/linkshare/addStatusValidate'),
            'action' => 'new'
        );

        $this->load->view('addStatus', $data);
    }

    public function addStatusValidate($action = 'new', $id = false)
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('id_status', 'SID', 'required|trim');
        $this->form_validation->set_rules('name', 'Nume', 'required|trim');


        if ($this->form_validation->run() === false) {
            $this->notices->SetError('Campuri obligatorii.');
            $error = true;
        }

        if (isset($error)) {
            if ($action == 'new') {
                redirect('admincp/linkshare/addStatus');
                return false;
            } else {
                redirect('admincp/linkshare/editStatus/' . $id);
                return false;
            }
        }

        $this->load->model('status_model');
        $fields = array();

        $fields['id_status'] = $this->input->post('id_status');
        $fields['name'] = $this->input->post('name');
        $fields['description'] = $this->input->post('description');

        if ($action == 'new') {
            $type_id = $this->status_model->new_status($fields);

            $this->notices->SetNotice('status adaugat cu succes.');

            redirect('admincp/linkshare/listStatus/');
        } else {
            $this->status_model->update_status($fields, $id);
            $this->notices->SetNotice('status actualizat cu succes.');

            redirect('admincp/linkshare/listStatus/');
        }

        return true;
    }

    public function editStatus($id)
    {
        $this->load->model('status_model');
        $status = $this->status_model->get_status($id);

        if (empty($status)) {
            die(show_error('Nu exista nici un status cu acest ID.'));
        }

        $this->load->library('admin_form');
        $form = new Admin_form;

        $form->fieldset('status');
        $form->text('SID', 'id_status', $status['id_status'], 'Introduceti status ID.', true, 'e.g., approved', true);
        $form->text('Nume', 'name', $status['name'], 'Introduceti nume status.', true, 'e.g., U.S. status', true);
        $form->textarea('Descriere', 'description', $status['description'], 'Introduceti descriere status.', false, '', true);

        $data = array(
            'form' => $form->display(),
            'form_title' => 'Editare status',
            'form_action' => site_url('admincp/linkshare/addStatusValidate/edit/' . $status['id']),
            'action' => 'edit',
        );

        $this->load->view('addStatus', $data);
    }

    public function deleteStatus($contents, $return_url)
    {

        $this->load->library('asciihex');
        $this->load->model('status_model');

        $contents = unserialize(base64_decode($this->asciihex->HexToAscii($contents)));
        $return_url = base64_decode($this->asciihex->HexToAscii($return_url));

        foreach ($contents as $content) {
            $this->status_model->deleteStatus($content);
        }

        $this->notices->SetNotice('status sters cu succes.');

        redirect($return_url);

        return true;
    }

    public function siteAdvertisers($id = 1)
    {
        $this->load->model('site_model');
        $this->load->model('magazin_model');
        $site = $this->site_model->get_site($id);

        $this->admin_navigation->module_link('Adauga advertiser', site_url('admincp/linkshare/addAdvertiser/'));
        $this->admin_navigation->module_link('Parseaza advertiserii aprobati', site_url('admincp/linkshare/parseAdvertisers/' . $site['token'] . '/approved'));
        //$this->admin_navigation->module_link('Parseaza magazine temp removed',site_url('admincp/linkshare/parseAdvertisers/'.$site['token'].'/temp removed'));
        $this->admin_navigation->module_link('Parseaza TOATI advertiserii', site_url('admincp/linkshare/parseAdvertisers/' . $site['token'] . '/all'));

        $this->load->library('dataset');

        $columns = array(
            array(
                'name' => 'ID #',
                'width' => '1%'),
            array(
                'name' => 'Site',
                'width' => '10%'),
            array(
                'name' => 'Id Status',
                'width' => '4%'),
            array(
                'name' => 'Status',
                'width' => '8%',
                'type' => 'text',
                'sort_column' => 'status'),
            array(
                'name' => 'Categorii',
                'width' => '10%',
                'type' => 'text',
                'filter' => 'id_categories'),
            array(
                'name' => 'Mid',
                'width' => '10%',
                'type' => 'text',
                'filter' => 'mid',
                'sort_column' => 'status'),
            array(
                'name' => 'Nume',
                'width' => '10%',
                'type' => 'text',
                'filter' => 'name'),
            array(
                'name' => 'Comision',
                'width' => '10%'),
            array(
                'name' => 'Oferta ID',
                'width' => '5%'),
            array(
                'name' => 'Nume oferta',
                'width' => '10%'),
            array(
                'name' => 'Operatii',
                'width' => '10%'
            ),
            array(
                'name' => 'Parseaza produse',
                'width' => '5%'
            ),
            array('name' => 'Produse',
                'width' => '5%'
            ),
            array('name' => 'NR',
                'width' => '1%'
            ),
        );

        $filters = array();
        $filters['limit'] = 10;

        if (isset($_GET['offset']))
        $filters['offset'] = $_GET['offset'];
        $filters['id_site'] = $id;

        $this->dataset->columns($columns);
        $this->dataset->datasource('magazin_model', 'get_magazine', $filters);
        $this->dataset->base_url(site_url('admincp/linkshare/siteAdvertisers/' . $id));
        $this->dataset->rows_per_page(10);

        // total rows
        $this->db->where('id_site', $id);
        $total_rows = $this->db->get('linkshare_magazin')->num_rows();
        $this->dataset->total_rows($total_rows);

        $this->dataset->initialize();

        // add actions
        $this->dataset->action('Delete', 'admincp/linkshare/deleteAdvertiser');

        $this->load->view('listAdvertisers');
    }

    public function addAdvertiser()
    {
        $this->load->library('admin_form');
        $form = new Admin_form;

        $status = array('aprobat' => 'aprobat', 'in curs' => 'in curs', 'terminat' => 'terminat', 'respins' => 'respins');
        $this->load->model('categorie_model');
        $categorii = $this->categorie_model->get_categorie_status();

        $form->fieldset('Magazin nou');
        $form->text('Id site', 'id_site', '', 'Introduceti id site.', true, 'e.g., 1', true);
        $form->text('Id status', 'id_status', '', 'Introduceti id status.', true, 'e.g., 1', true);
        $form->text('Status', 'status', '', 'Introduceti status.', true, 'e.g., Temp Rejected', true);
        $form->text('Categorii', 'id_categories', '', 'Introduceti categorii', true, 'e.g., 103 155 154 125 18 132', true);
        $form->text('Id magazin', 'mid', '', 'Introduceti id magazin.', true, 'e.g., 35171', true);
        $form->text('Nume magazin', 'name', '', 'Introduceti nume magazin.', true, 'e.g., MrWatch', true);
        $form->text('Offer also', 'offer_also', '', 'Introduceti offer_also.', true, 'e.g., 19.2', true);
        $form->text('Comision', 'commission', '', 'Introduceti comision .', true, 'e.g., sale : 0-2000 8%', true);
        $form->text('Offer id', 'offer_id', '', 'Introduceti offer_id.', true, 'e.g., 228980', true);
        $form->text('Offer name', 'offer_name', '', 'Introduceti offer_name.', true, 'e.g., Baseline Offer', true);

        $data = array(
            'form' => $form->display(),
            'form_title' => 'Adauga magazin',
            'form_action' => site_url('admincp/linkshare/addAdvertiserValidate'),
            'action' => 'new'
        );

        $this->load->view('addAdvertiser', $data);
    }

    public function addAdvertiserValidate($action = 'new', $id = false)
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('name', 'Nume magazin', 'required|trim');
        $this->form_validation->set_rules('mid', 'Id magazin', 'required|trim');

        if ($this->form_validation->run() === false) {
            $this->notices->SetError('Aveti erori in formular.');
            $error = true;
        }

        if (isset($error)) {
            if ($action == 'new') {
                redirect('admincp/linkshare/addAdvertiser');
                return false;
            } else {
                redirect('admincp/linkshare/editAdvertiser/' . $id);
                return false;
            }
        }

        $fields['id_site'] = $this->input->post('id_site');
        $fields['id_status'] = $this->input->post('id_status');
        $fields['status'] = $this->input->post('status');
        $fields['id_categories'] = $this->input->post('id_categories');
        $fields['mid'] = $this->input->post('mid');
        $fields['name'] = $this->input->post('name');
        $fields['offer_also'] = $this->input->post('offer_also');
        $fields['commission'] = $this->input->post('commission');
        $fields['offer_id'] = $this->input->post('offer_id');
        $fields['offer_name'] = $this->input->post('offer_name');

        $this->load->model('magazin_model');

        if ($action == 'new') {
            $type_id = $this->magazin_model->new_magazin($fields);

            $this->notices->SetNotice('Magazin adaugat cu succes.');

            redirect('admincp/linkshare/siteAdvertisers/1');
        } else {
            $this->magazin_model->update_magazin($fields, $id);
            $this->notices->SetNotice('Magazin actualizat cu succes.');

            redirect('admincp/linkshare/siteAdvertisers/1');
        }

        return true;
    }

    public function editAdvertiser($id)
    {
        $this->load->model('magazin_model');
        $magazin = $this->magazin_model->get_magazin($id);

        if (empty($magazin)) {
            die(show_error('Nu exista magazin cu acest ID.'));
        }

        $this->load->library('admin_form');
        $form = new Admin_form;

        $form->fieldset('Magazin');
        $form->text('Id site', 'id_site', $magazin['id_site'], 'Introduceti id site.', true, 'e.g., 1', true);
        $form->text('Id status', 'id_status', $magazin['id_status'], 'Introduceti id status.', true, 'e.g., 1', true);
        $form->text('Status', 'status', $magazin['status'], 'Introduceti status.', true, 'e.g., Temp Rejected', true);
        $form->text('Categorii', 'id_categories', $magazin['id_categories'], 'Introduceti categorii', true, 'e.g., 103 155 154 125 18 132', true);
        $form->text('Id magazin', 'mid', $magazin['mid'], 'Introduceti id magazin.', true, 'e.g., 35171', true);
        $form->text('Nume magazin', 'name', $magazin['name'], 'Introduceti nume magazin.', true, 'e.g., MrWatch', true);
        $form->text('Offer also', 'offer_also', $magazin['offer_also'], 'Introduceti offer_also.', true, 'e.g., 19.2', true);
        $form->text('Comision', 'commission', $magazin['commission'], 'Introduceti comision .', true, 'e.g., sale : 0-2000 8%', true);
        $form->text('Offer id', 'offer_id', $magazin['offer_id'], 'Introduceti offer_id.', true, 'e.g., 228980', true);
        $form->text('Offer name', 'offer_name', $magazin['offer_name'], 'Introduceti offer_name.', true, 'e.g., Baseline Offer', true);

        $data = array(
            'form' => $form->display(),
            'form_title' => 'Editare magazin',
            'form_action' => site_url('admincp/linkshare/addAdvertiserValidate/edit/' . $magazin['id']),
            'action' => 'edit',
        );

        $this->load->view('addAdvertiser', $data);
    }

    public function deleteAdvertiser($contents, $return_url)
    {

        $this->load->library('asciihex');
        $this->load->model('magazin_model');

        $contents = unserialize(base64_decode($this->asciihex->HexToAscii($contents)));
        $return_url = base64_decode($this->asciihex->HexToAscii($return_url));

        foreach ($contents as $content) {
            $this->magazin_model->deleteAdvertiser($content);
        }

        $this->notices->SetNotice('Magazin(e) sters(e) cu succes.');

        redirect($return_url);

        return true;
    }

    public function parseAdvertisers($token, $status)
    {
        //error_reporting(E_ALL);
        //ini_set('display_errors',1);
        $mids = array();
        $aux = '';
        $this->load->model('site_model');
        $aux = $this->site_model->get_site_by_token($token);
        $i = 0;
        $site = $aux['name'];
        $offset = 0;
        if (isset($_GET['offset']) && $_GET['offset'])
            $offset = $_GET['offset'];

        $j = 1;
        $statuses = array();
        if ($status == 'all') {
            $this->load->model('status_model');
            $aux = $this->status_model->get_statuses();
            foreach ($aux as $val) {
                $statuses[] = str_replace(' ', '%20', $val['id_status']);
            }
            $j = count($statuses);
        } else {
            $statuses[] = $status;
        }

        /* print '<pre>';
          print_r($statuses);
          echo "j=$j<br/>";
          die; */

        while ($j > 0) {
            $aux = file_get_contents('http://lld2.linksynergy.com/services/restLinks/getMerchByAppStatus/' . $token . '/' . $statuses[$j - 1]);
            $categories = simplexml_load_string($aux, "SimpleXMLElement", LIBXML_NOCDATA);
            //echo $categories->getName().'<br/>';die;
            if (isset($categories)) {
                $kids = $categories->children('ns1', true);
                //var_dump(count($kids));die;

                foreach ($kids as $child) {
                    $mids[$i]['id'] = $i + 1;
                    $mids[$i]['site'] = $site;
                    $mids[$i]['id_status'] = $child->applicationStatus;
                    $mids[$i]['status'] = $child->applicationStatus;
                    $mids[$i]['id_categories'] = $child->categories;
                    $mids[$i]['mid'] = $child->mid;
                    $mids[$i]['name'] = $child->name;
                    $mids[$i]['offer_also'] = $child->offer->alsoName;
                    $mids[$i]['commission'] = $child->offer->commissionTerms;
                    $mids[$i]['offer_id'] = $child->offer->offerId;
                    $mids[$i]['offer_name'] = $child->offer->offerName;
                    $mids[$i]['limit'] = 10;
                    $mids[$i]['offset'] = $offset;
                    $i++;
                }
            }

            $j--;
            //print '<pre>';print_r($mids);die;
        }

        $this->admin_navigation->module_link('Adauga advertiseri parsati', site_url('admincp/linkshare/parseAdvertisersAdd/' . $token . '/' . $status));

        $this->load->library('dataset');

        $columns = array(
            array(
                'name' => 'ID #',
                'width' => '5%'),
            array(
                'name' => 'Site',
                'width' => '10%'),
            array(
                'name' => 'ID Status',
                'width' => '5%'),
            array(
                'name' => 'Status',
                'width' => '5%'),
            array(
                'name' => 'Categorii',
                'width' => '15%'),
            array(
                'name' => 'Mid',
                'width' => '10%'),
            array(
                'name' => 'Nume',
                'width' => '10%'),
            array(
                'name' => 'Oferta alias',
                'width' => '10%'),
            array(
                'name' => 'Comision',
                'width' => '10%'),
            array(
                'name' => 'Oferta ID',
                'width' => '10%'
            ),
            array('name' => 'Oferta nume',
                'width' => '10%'
            ),
        );

        $this->dataset->columns($columns);
        $this->dataset->datasource('magazin_model', 'parse_magazin', $mids);
        $this->dataset->base_url(site_url('admincp/linkshare/parseAdvertisers/' . $token) . '/' . $status);
        $this->dataset->rows_per_page(10);

        // total rows
        $total_rows = count($mids);
        $this->dataset->total_rows($total_rows);

        $this->dataset->initialize();

        $this->load->view('listAdvertisersParsed');
    }

    public function parseAdvertisersAdd($token, $status)
    {
        //error_reporting(E_ALL);
        //ini_set('display_errors',1);            
        $this->load->model('site_model');
        $aux = $this->site_model->get_site_by_token($token);
        $id_site = $aux['id'];
        $aux = '';
        $i = 0;
        $cate = 0;

        $j = 1;
        $statuses = array();
        if ($status == 'all') {
            $this->load->model('status_model');
            $aux = $this->status_model->get_statuses();
            foreach ($aux as $val) {
                $statuses[] = str_replace(' ', '%20', $val['id_status']);
            }
            $j = count($statuses);
        } else {
            $statuses[] = $status;
        }

        while ($j > 0) {
            $mids = array();
            $aux = file_get_contents('http://lld2.linksynergy.com/services/restLinks/getMerchByAppStatus/' . $token . '/' . $statuses[$j - 1]);

            $categories = simplexml_load_string($aux, "SimpleXMLElement", LIBXML_NOCDATA);
            //echo $categories->getName().'<br/>';
            if (isset($categories)) {
                $kids = $categories->children('ns1', true);
                //var_dump(count($kids));

                $this->load->model('status_model');

                foreach ($kids as $child) {
                    $mids[$i]['id_site'] = $id_site;
                    $mids[$i]['id_status'] = mysql_real_escape_string($this->status_model->get_status_by_application_status($child->applicationStatus));
                    $mids[$i]['status'] = mysql_real_escape_string($child->applicationStatus);
                    $mids[$i]['id_categories'] = mysql_real_escape_string($child->categories);
                    $mids[$i]['mid'] = mysql_real_escape_string($child->mid);
                    $mids[$i]['name'] = mysql_real_escape_string($child->name);
                    $mids[$i]['offer_also'] = mysql_real_escape_string($child->offer->alsoName);
                    $mids[$i]['commission'] = mysql_real_escape_string($child->offer->commissionTerms);
                    $mids[$i]['offer_id'] = mysql_real_escape_string($child->offer->offerId);
                    $mids[$i]['offer_name'] = mysql_real_escape_string($child->offer->offerName);
                    $i++;
                }
            }

            $statuses[$j - 1] = str_replace('%20', ' ', $statuses[$j - 1]);
            $this->load->model('status_model');
            $id_status = $this->status_model->get_status_by_name($statuses[$j - 1]);

            $this->load->model('magazin_model');
            $this->magazin_model->deleteAdvertiserByStatus($id_site, $id_status);
            $cate += $this->magazin_model->new_magazine($mids);

            $i = 0;
            unset($mids);
            $j--;
        }


        $this->notices->SetNotice($cate . ' magazine parsate adaugate cu succes.');
        redirect('admincp/linkshare/siteAdvertisers/' . $id_site);
    }

    public function list_categorii_creative($id = 1)
    {
        $this->admin_navigation->module_link('Adauga categorie creative', site_url('admincp/linkshare/add_categorie_creative'));
        $this->admin_navigation->module_link('Parseaza categorii creative linkshare si adauga in db', site_url('admincp/linkshare/parse_categorie_creative/' . $id));

        $this->load->library('dataset');
        $this->load->model('categorie_creative_model');

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
                'width' => '15%'),
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

        if (isset($_GET['offset']))
            $filters['offset'] = $_GET['offset'];
        $filters['id_site'] = $id;
        $filters['name'] = true;
        if (isset($_GET['nume']))
            $filters['nume'] = $_GET['nume'];

        $this->load->library('asciihex');
        $this->load->model('forms/form_model');

        if (isset($_GET['filters'])) {
            $aux = unserialize(base64_decode($this->asciihex->HexToAscii($_GET['filters'])));
            if (isset($aux['nume']))
                $filters['nume'] = $aux['nume'];
        }

        $this->dataset->columns($columns);
        $this->dataset->datasource('categorie_creative_model', 'get_categorii', $filters);
        $this->dataset->base_url(site_url('admincp/linkshare/list_categorii_creative/' . $id));
        $this->dataset->rows_per_page(50);

        // total rows
        $this->db->where('id_site', $id);
        $total_rows = $this->categorie_creative_model->get_categorii_linii($filters);
        $this->dataset->total_rows($total_rows);

        $this->dataset->initialize();

        // add actions
        $this->dataset->action('Delete', 'admincp/linkshare/delete_categorie_creative');

        $this->load->view('list_categorii_creative');
    }

    public function add_categorie_creative()
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
            'form_action' => site_url('admincp/linkshare/add_categorie_creative_validate'),
            'action' => 'new'
        );

        $this->load->view('add_categorie_creative', $data);
    }

    public function add_categorie_creative_validate($action = 'new', $id = false)
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
                redirect('admincp/linkshare/add_categorie_creative');
                return false;
            } else {
                redirect('admincp/linkshare/edit_categorie_creative/' . $id);
                return false;
            }
        }

        $this->load->model('categorie_creative_model');

        $fields['id_site'] = $this->input->post('id_site');
        $fields['cat_id'] = $this->input->post('cat_id');
        $fields['name'] = $this->input->post('name');
        $fields['mid'] = $this->input->post('mid');
        $fields['nid'] = $this->input->post('nid');

        if ($action == 'new') {
            $type_id = $this->categorie_creative_model->new_categorie($fields);

            $this->notices->SetNotice('Categorie creative adaugata cu succes.');

            redirect('admincp/linkshare/list_categorii_creative/');
        } else {
            $this->categorie_creative_model->update_categorie($fields, $id);
            $this->notices->SetNotice('Categorie creative actualizata cu succes.');

            redirect('admincp/linkshare/list_categorii_creative/');
        }

        return true;
    }

    public function edit_categorie_creative($id)
    {
        $this->load->model('categorie_creative_model');
        $categorie = $this->categorie_creative_model->get_categorie($id);

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
            'form_action' => site_url('admincp/linkshare/add_categorie_creative_validate/edit/' . $categorie['id']),
            'action' => 'edit',
        );

        $this->load->view('add_categorie_creative', $data);
    }

    public function delete_categorie_creative($contents, $return_url)
    {

        $this->load->library('asciihex');
        $this->load->model('categorie_creative_model');

        $contents = unserialize(base64_decode($this->asciihex->HexToAscii($contents)));
        $return_url = base64_decode($this->asciihex->HexToAscii($return_url));

        foreach ($contents as $content) {
            $this->categorie_creative_model->deleteCategory($content);
        }

        $this->notices->SetNotice('Categorie creative stearsa cu succes.');

        redirect($return_url);

        return true;
    }

    public function parse_categorie_creative($id)
    {
        //error_reporting(E_ALL);
        //ini_set('display_errors',1);
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

        $this->load->model('magazin_model');
        $filters['id_site'] = $id;
        $this->load->model('status_model');
        $filters['id_status'] = $this->status_model->get_status_by_name('approved');
        $mag = array();
        $mag = $this->magazin_model->get_magazine($filters);
        foreach ($mag as $val) {
            $mids[] = $val['mid'];
        }

        $j = count($mids);

        /* print '<pre>';
          print_r($mids);
          die; */

        $fp = fopen('erori_parsare.txt', 'a');

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

                $this->load->model('categorie_creative_model');
                //delete old categories for this mid and this site id
                $this->categorie_creative_model->delete_categorie_by_mid($id, $mids[$j - 1]);

                foreach ($cats as $cat) {
                    $cat['id'] = '';
                    $this->categorie_creative_model->new_categorie($cat);
                }

                //print '<pre>';print_r($cats);die;
            } else {
                //log_message('debug', 'http://lld2.linksynergy.com/services/restLinks/getCreativeCategories/'.$token.'/'.$mids[$j-1].' xml eroare');                    
                //error_log('http://lld2.linksynergy.com/services/restLinks/getCreativeCategories/'.$token.'/'.$mids[$j-1].' xml eroare', 3, "/public_html/hero/app/logs/my-errors.log");
                $data = date('Y-m-d H:i:s');
                fwrite($fp, 'erori_parsare.txt', 'http://lld2.linksynergy.com/services/restLinks/getCreativeCategories/' . $token . '/' . $mids[$j - 1] . ' xml eroare ' . $data);
            }
            $j--;
        }

        fclose($fp);

        $this->admin_navigation->module_link('Vezi categoriile creative parsate', site_url('admincp/linkshare/list_categorii_creative/' . $id));

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
        $this->dataset->datasource('categorie_creative_model', 'parseCategories', $catz);
        $this->dataset->base_url(site_url('admincp/linkshare/parse_categorie_creative/' . $id));
        $this->dataset->rows_per_page(10);

        // total rows
        $total_rows = 1;
        $this->dataset->total_rows($total_rows);

        $this->dataset->initialize();

        $this->load->view('list_categorii_creative_parsate');
    }

    public function parse_product_search($id, $mid, $creative_cat_id = 0)
    {
        error_reporting(E_ERROR);
        $aux = '';
        $this->load->model('site_model');
        $aux = $this->site_model->get_site($id);
        $token = $aux['token'];

        $this->load->model('produs_model');
        $this->load->model('produs_new_model');

        $creative_categories = array();
        $this->load->model('categorie_creative_model');
        $filters['id_site'] = $id;
        $filters['mid'] = $mid;
        $creative_categories = $this->categorie_creative_model->get_categorii($filters);

        $data = array();

        // print '<pre>';
        // print_r($creative_categories);
        // print "count = ".count($creative_categories);
        // die;
        //the creative category is not present it means we have finished adding/updating new products...now we must set the old ones to available='no' and return from method

        if (!array_key_exists($creative_cat_id, $creative_categories)) {
            $old = $this->produs_new_model->mark_old($mid);
            $data['message'] = "$old products marked as not available";
            $this->load->view('parse_product_search', $data);
            return;
        }

        $creative_category = $creative_categories[$creative_cat_id]['cat_id'];
        $campaignID = -1;
        $page = 1;

        do {
            $kids = 0;
            $i = $j = $k = 0;
            if (isset($product))
                unset($product);
            $product = array();
            $message = '';

            $categories = simplexml_load_file("http://lld2.linksynergy.com/services/restLinks/getProductLinks/$token/$mid/$creative_category/$campaignID/$page", "SimpleXMLElement", LIBXML_NOCDATA);

            if (is_object($categories) && isset($categories)) {
                $copii = $categories->children('ns1', true);
                $kids = count($copii);
                if ($kids) {
                    //var_dump($kids);                                                
                    foreach ($copii as $child) {
                        $product[$i]['id_site'] = $id;
                        $product[$i]['cat_creative_id'] = addslashes($child->categoryID);
                        $product[$i]['cat_creative_name'] = addslashes($child->categoryName);
                        $product[$i]['mid'] = $mid;
                        $product[$i]['linkid'] = addslashes($child->linkID);
                        $product[$i]['linkname'] = addslashes($child->linkName);
                        $product[$i]['nid'] = $child->nid;
                        $product[$i]['click_url'] = addslashes($child->clickURL);
                        $product[$i]['icon_url'] = addslashes($child->iconURL);
                        $product[$i]['show_url'] = addslashes($child->showURL);
                        $product[$i]['available'] = 'yes';
                        $product[$i]['insert_date'] = date("Y-m-d H:i:s");
                        $product[$i]['last_update_date'] = date("Y-m-d H:i:s");

                        $produs = simplexml_load_file("http://productsearch.linksynergy.com/productsearch?token=$token&keyword=\"{$child->linkName}\"&MaxResults=1&pagenumber=1&mid=$mid", "SimpleXMLElement", LIBXML_NOCDATA);

                        if (is_object($produs) && isset($produs)) {
                            $x = $produs->item[0];
                            $product[$i]['merchantname'] = addslashes($x->merchantname);
                            $product[$i]['createdon'] = addslashes($x->createdon);
                            $product[$i]['sku'] = addslashes($x->sku);
                            $product[$i]['productname'] = addslashes($x->productname);
                            $product[$i]['categ_primary'] = addslashes($x->category->primary);
                            $product[$i]['categ_secondary'] = addslashes($x->category->secondary);
                            $product[$i]['price'] = $x->price;
                            $product[$i]['currency'] = addslashes($x->price["currency"]);
                            $product[$i]['upccode'] = addslashes($x->upccode);
                            $product[$i]['description_short'] = addslashes($x->description->short);
                            $product[$i]['description_long'] = addslashes($x->description->long);
                            $product[$i]['keywords'] = addslashes($x->keywords);
                            $product[$i]['linkurl'] = addslashes($x->linkurl);
                            $product[$i]['imageurl'] = addslashes($x->imageurl);
                            $product[$i]['price_list'] = $x->saleprice;
                            $product[$i]['price_save'] = 0;
                            $product[$i]['price_final'] = 0;
                        } else {
                            $product[$i]['merchantname'] = '';
                            $product[$i]['createdon'] = '';
                            $product[$i]['sku'] = $product[$i]['linkid'];
                            $product[$i]['productname'] = $product[$i]['linkname'];
                            $product[$i]['categ_primary'] = '';
                            $product[$i]['categ_secondary'] = '';
                            $product[$i]['price'] = 0;
                            $product[$i]['currency'] = "USD";
                            $product[$i]['upccode'] = '';
                            $product[$i]['description_short'] = '';
                            $product[$i]['description_long'] = '';
                            $product[$i]['keywords'] = '';
                            $product[$i]['linkurl'] = '';
                            $product[$i]['imageurl'] = '';
                            $product[$i]['price_list'] = 0;
                            $product[$i]['price_save'] = 0;
                            $product[$i]['price_final'] = 0;
                        }

                        //print '<pre>';
                        //print_r($product);

                        $this->produs_new_model->new_produs($product[$i]);

                        $exista = $this->produs_model->exists_produs($product[$i]['linkid']);
                        if (!$exista) {
                            $this->produs_model->new_produs($product[$i]);
                            $j++;
                        } else {
                            $this->produs_model->update_produs_by_linkid($product[$i], $product[$i]['linkid']);
                            $k++;
                        }
                        $i++;
                        //echo $i.'<br/>';
                    }
                }
            }

            $fp = fopen("loguri.txt", "a");
            fwrite($fp, "http://lld2.linksynergy.com/services/restLinks/getProductLinks/$token/$mid/$creative_category/$campaignID/$page $i total products $j inserted $k updated  " . date("Y-m-d H:i:s") . PHP_EOL);
            fclose($fp);
            $message .= "cat_id $creative_cat_id creative_category $creative_category page $page " . count($product) . " products parsed<br/>";
            $page++;
        } while ($kids > 0);

        $data['message'] = $message;

        $this->load->view('parse_product_search', $data);

        $creative_cat_id++;
        echo '<META http-equiv="refresh" content="1;URL=/admincp/linkshare/parse_product_search/' . $id . '/' . $mid . '/' . $creative_cat_id . '">';
        die;
    }

    public function parse_product_xml_reader_search($id, $mid, $creative_cat_id = 0, $partial = 0, $page = 1, $id_produs_partial = 1, $j = 0, $k = 0)
    {
        error_reporting(E_ERROR);
        $aux = '';
        $this->load->model('site_model');
        $aux = $this->site_model->get_site($id);
        $token = $aux['token'];

        $this->load->model('produs_model');
        $this->load->model('produs_partial_model');
        $this->load->model('produs_new_model');

        $creative_categories = array();
        $this->load->model('categorie_creative_model');
        $filters['id_site'] = $id;
        $filters['mid'] = $mid;
        $creative_categories = $this->categorie_creative_model->get_categorii($filters);

        $data = array();

        // print '<pre>';
        // print_r($creative_categories);
        // print "count = ".count($creative_categories);
        // die;

        if (!$partial) {

            //the creative category is not present it means we have finished adding/updating new products...now we must set the old ones to available='no' and return from method
            if (!array_key_exists($creative_cat_id, $creative_categories)) {
                $old = $this->produs_new_model->mark_old($mid);
                $data['message'] = "$old products marked as not available";
                $this->load->view('parse_product_search', $data);
                return;
            }

            $creative_category = $creative_categories[$creative_cat_id]['cat_id'];
            $campaignID = -1;

            $xml = new XMLReader();

            $file = "http://lld2.linksynergy.com/services/restLinks/getProductLinks/$token/$mid/$creative_category/$campaignID/$page";

            if (!$xml->open($file)) {
                $fp = fopen("loguri.txt", "a");
                fwrite($fp, "Failed to open input file : http://lld2.linksynergy.com/services/restLinks/getProductLinks/$token/$mid/$creative_category/$campaignID/$page " . date("Y-m-d H:i:s") . PHP_EOL);
                fclose($fp);

                $data['message'] = "Failed to open input file : http://lld2.linksynergy.com/services/restLinks/getProductLinks/$token/$mid/$creative_category/$campaignID/$page " . date("Y-m-d H:i:s");

                $this->load->view('parse_product_search', $data);

                $creative_cat_id++;
                echo '<META http-equiv="refresh" content="1;URL=/admincp/linkshare/parse_product_xml_reader_search/' . $id . '/' . $mid . '/' . $creative_cat_id . '">';
                die;
            } else {
                $i = 0;
                $product = array();
                $wrapperName = 'ns1:return';
                while ($xml->read()) {
                    $subarray = array();
                    //echo 'read<br/>';
                    if ($xml->nodeType == XMLReader::ELEMENT && $xml->name == $wrapperName) {
                        while ($xml->read() && $xml->name != $wrapperName) {
                            if ($xml->nodeType == XMLReader::ELEMENT) {
                                $name = $xml->name;
                                $xml->read();
                                $value = $xml->value;
                                $subarray[$name] = $value;
                            }
                        }

                        $product[$i]['id_site'] = $id;
                        $product[$i]['cat_creative_id'] = addslashes($subarray['ns1:categoryID']);
                        $product[$i]['cat_creative_name'] = addslashes($subarray['ns1:categoryName']);
                        $product[$i]['mid'] = $mid;
                        $product[$i]['linkid'] = addslashes($subarray['ns1:linkID']);
                        $product[$i]['linkname'] = addslashes($subarray['ns1:linkName']);
                        $product[$i]['nid'] = $subarray['ns1:nid'];
                        $product[$i]['click_url'] = addslashes($subarray['ns1:clickURL']);
                        $product[$i]['icon_url'] = addslashes($subarray['ns1:iconURL']);
                        $product[$i]['show_url'] = addslashes($subarray['ns1:showURL']);
                        $product[$i]['available'] = 'yes';
                        $product[$i]['insert_date'] = date("Y-m-d H:i:s");
                        $product[$i]['last_update_date'] = date("Y-m-d H:i:s");

                        $this->produs_partial_model->new_produs($product[$i]);

                        unset($subarray);

                        $i++;
                        //echo $i.'<br/>';
                    }

                    //echo "slice = $slice total = $total<br/>";
                }
                $xml->close();

                /* print '<pre>';
                  print_r($product);
                  die; */

                //no product found the xml is empty : <ns1:getProductLinksResponse xmlns:ns1="http://endpoint.linkservice.linkshare.com/"/>
                if (!$i) {
                    $data['message'] = "Failed to open input file : http://lld2.linksynergy.com/services/restLinks/getProductLinks/$token/$mid/$creative_category/$campaignID/$page xml empty " . date("Y-m-d H:i:s");

                    $this->load->view('parse_product_search', $data);

                    $creative_cat_id++;
                    echo '<META http-equiv="refresh" content="1;URL=/admincp/linkshare/parse_product_xml_reader_search/' . $id . '/' . $mid . '/' . $creative_cat_id . '">';
                    die;
                }

                $partial = 1;
                $id_produs_partial = 1;
                $j = $k = 0;

                $data['message'] = $message;

                $this->load->view('parse_product_search', $data);

                echo '<META http-equiv="refresh" content="1;URL=/admincp/linkshare/parse_product_xml_reader_search/' . $id . '/' . $mid . '/' . $creative_cat_id . '/' . $partial . '/' . $page . '/' . $id_produs_partial . '/' . $j . '/' . $k . '/' . '">';
                die;
            }
        } else {
            $produs_partial = array();
            $produs_partial = $this->produs_partial_model->get_produs($id_produs_partial);
            if (!empty($produs_partial)) {
                $produs = simplexml_load_file("http://productsearch.linksynergy.com/productsearch?token=$token&keyword=\"{$produs_partial['linkname']}\"&MaxResults=1&pagenumber=1&mid=$mid", "SimpleXMLElement", LIBXML_NOCDATA);

                if (is_object($produs) && isset($produs)) {
                    $x = $produs->item[0];
                    $produs_partial['merchantname'] = addslashes($x->merchantname);
                    $produs_partial['createdon'] = addslashes($x->createdon);
                    $produs_partial['sku'] = addslashes($x->sku);
                    $produs_partial['productname'] = addslashes($x->productname) ? addslashes($x->productname) : $produs_partial['linkname'];
                    $produs_partial['categ_primary'] = addslashes($x->category->primary);
                    $produs_partial['categ_secondary'] = addslashes($x->category->secondary);
                    $produs_partial['price'] = $x->price[0];
                    $produs_partial['currency'] = addslashes($x->price["currency"]);
                    $produs_partial['upccode'] = addslashes($x->upccode);
                    $produs_partial['description_short'] = addslashes($x->description->short);
                    $produs_partial['description_long'] = addslashes($x->description->long);
                    $produs_partial['keywords'] = addslashes($x->keywords);
                    $produs_partial['linkurl'] = addslashes($x->linkurl);
                    $produs_partial['imageurl'] = addslashes($x->imageurl);
                    $produs_partial['price_list'] = $x->saleprice[0];
                    $produs_partial['price_save'] = 0;
                    $produs_partial['price_final'] = 0;
                } else {
                    $produs_partial['merchantname'] = '';
                    $produs_partial['createdon'] = '';
                    $produs_partial['sku'] = $produs_partial['linkid'];
                    $produs_partial['productname'] = $produs_partial['linkname'];
                    $produs_partial['categ_primary'] = '';
                    $produs_partial['categ_secondary'] = '';
                    $produs_partial['price'] = 0;
                    $produs_partial['currency'] = "USD";
                    $produs_partial['upccode'] = '';
                    $produs_partial['description_short'] = '';
                    $produs_partial['description_long'] = '';
                    $produs_partial['keywords'] = '';
                    $produs_partial['linkurl'] = '';
                    $produs_partial['imageurl'] = '';
                    $produs_partial['price_list'] = 0;
                    $produs_partial['price_save'] = 0;
                    $produs_partial['price_final'] = 0;
                }

                unset($produs_partial['id']);
                $this->produs_new_model->new_produs($produs_partial);

                $exista = $this->produs_model->exists_produs($produs_partial['linkid']);
                if (!$exista) {
                    $this->produs_model->new_produs($produs_partial);
                    $j++;
                } else {
                    $this->produs_model->update_produs_by_linkid($produs_partial, $produs_partial['linkid']);
                    $k++;
                }

                $partial = 1;
                $id_produs_partial++;

                $data['message'] = "$id_produs_partial products parsed";

                $this->load->view('parse_product_search', $data);

                echo '<META http-equiv="refresh" content="0;URL=/admincp/linkshare/parse_product_xml_reader_search/' . $id . '/' . $mid . '/' . $creative_cat_id . '/' . $partial . '/' . $page . '/' . $id_produs_partial . '/' . $j . '/' . $k . '/' . '">';
                die;
            } else {

                $i = $id_produs_partial - 1;
                $fp = fopen("loguri.txt", "a");
                fwrite($fp, "http://lld2.linksynergy.com/services/restLinks/getProductLinks/$token/$mid/$creative_category/$campaignID/$page $i total products $j inserted $k updated  " . date("Y-m-d H:i:s") . PHP_EOL);
                fclose($fp);

                //i finished all the partial products,truncate the linkshare_produs_partial,increment the page and refresh
                $this->produs_partial_model->sterge();
                $partial = 0;
                $page++;

                $data['message'] = "cat_id $creative_cat_id creative_category $creative_category page $page $i products parsed<br/>";
                $this->load->view('parse_product_search', $data);
                echo '<META http-equiv="refresh" content="1;URL=/admincp/linkshare/parse_product_xml_reader_search/' . $id . '/' . $mid . '/' . $creative_cat_id . '/' . $partial . '/' . $page . '">';
                die;
            }
        }

        $data['message'] = 'FINISHED';

        $this->load->view('parse_product_search', $data);
    }

    public function test()
    {
        phpinfo();
    }

}
