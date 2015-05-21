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

class Admincp3 extends Admincp_Controller {

    private $specialChars = "(&=?{}\()[]-;~|$!><*%).";

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
        redirect('admincp3/linkshare/listProducts');
    }

    public function listProducts($id_site, $mid) {
        $this->admin_navigation->module_link('Back to advertiser', site_url('admincp3/linkshare/listCreativeCategory/' . $id_site));

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
        $this->dataset->datasource('product_model', 'getProductsByMid', $filters);
        $this->dataset->base_url(site_url('admincp3/linkshare/listProducts/' . $id_site . '/' . $mid));
        $this->dataset->rows_per_page(20);

        $this->load->model('advertiser_model');

        // total rows
        $total_rows = $this->advertiser_model->getCountProductsByMID($mid, $id_site, $filters);
        $this->dataset->total_rows($total_rows);

        $this->dataset->initialize();

        // add actions
        $this->dataset->action('Delete', 'admincp3/linkshare/deleteProduct');

        $magazin = '';

        $this->load->model('advertiser_model');
        $aux = $this->advertiser_model->getAdvertiserByMID($mid, $id_site);
        if ($aux)
            $magazin = $aux['name'];


        $data = array(
            'mid' => $mid,
            'magazin' => $magazin
        );

        $this->load->view('listProducts', $data);
    }

    public function listTempProducts($mid,$cat_id) {

        $this->admin_navigation->module_link('Back to Creative Categories', site_url('admincp2/linkshare/listCreativeCategory/'));
        $this->admin_navigation->module_link('Move to CURRENT', site_url('admincp3/linkshare/moveProducts/' . $mid . '/' . $cat_id));
        $this->admin_navigation->module_link('Parse FULL Products', site_url('admincp3/linkshare/parseProductSearch/' . $mid . '/' . $cat_id));
        
        $this->load->model(array('site_model', 'product_model'));
        $siteRow = $this->site_model->getSiteBySID($this->siteID);

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
        
        $filters['id_site']=$siteRow['id'];
        $filters['mid'] = $mid;

        $this->dataset->columns($columns);
        $this->dataset->datasource('product_model', 'getTempProductsByMid', $filters);
        $this->dataset->base_url(site_url('admincp3/linkshare/listTempProducts/' . $mid . '/' . $cat_id));
        $this->dataset->rows_per_page(20);

        $this->load->model('advertiser_model');

        // total rows
        $total_rows = $this->advertiser_model->getCountProductsByMID($mid, $siteRow['id'], $filters);
        $this->dataset->total_rows($total_rows);

        $this->dataset->initialize();

        // add actions
        $this->dataset->action('Delete', 'admincp3/linkshare/deleteProduct');

        $magazin = '';

        $this->load->model('advertiser_model');
        $aux = $this->advertiser_model->getAdvertiserByMID($mid, $siteRow['id']);
        if ($aux)
            $magazin = $aux['name'];


        $data = array(
            'mid' => $mid,
            'magazin' => $magazin,
            'category' => $cat_id
        );

        $this->load->view('listTempProducts', $data);
    }
    
    public function moveProducts($mid,$cat_id){
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
    
    public function changeTempProductStatus($id_product, $sid, $cat_id, $mid, $linkid, $ret_url) {
        $ret_url = base64_decode($ret_url);
        $this->load->model('product_model');
        $this->product_model->changeTempProductStatus($id_product, $sid, $cat_id, $mid, $linkid);
        echo '<META http-equiv="refresh" content="0;URL=' . $ret_url . '">';
    }

    private function cleanLink($link) {
        for ($i = 0; $i < strlen($link); $i++) {
            if (strpos($this->specialChars, $link[$i]) !== false) {
                $link = substr($link, 0, $i);
                break;
            }
        }

        return trim($link);
    }

    public function parseShortProduct($mid, $cat_id = 0) {
        error_reporting(E_ERROR);
        include "app/third_party/LOG/Log.php";
        $CI = & get_instance();

        $this->load->model(array('site_model', 'product_model','category_creative_model'));
        $siteRow = $this->site_model->getSiteBySID($this->siteID);

        $config = new LinkshareConfig();
        $accessToken = $config->setSiteCookieAndGetAccessToken($CI, $this->siteID);

        $campaignID = -1;
        $page = 1;

        $categoriesRequestUrl = "https://api.rakutenmarketing.com/linklocator/1.0/getProductLinks/$mid/$cat_id/$campaignID/$page";

        $request = new CurlApi($categoriesRequestUrl);
        $request->setHeaders($config->getMinimalHeaders($accessToken));
        $request->setGetData();
        $request->send();

        $responseObj = $request->getFormattedResponse();

        $aux = $responseObj['body'];
        $categories = simplexml_load_string($aux, "SimpleXMLElement", LIBXML_NOCDATA);

        //if (is_object($categories) && isset($categories)) {
        $kids = $categories->children('ns1', true);

        foreach ($kids as $child) {

            $value = $this->product_model->existsTempProduct($siteRow['id'], $cat_id, $mid, $child->linkID);

            if($value){          
                $temp_shortProd = array(
                    'id_site' => $siteRow['id'],
                    'cat_creative_id' => addslashes($child->categoryID),
                    'cat_creative_name' => addslashes($child->categoryName),
                    'linkid' => addslashes($child->linkID),
                    'linkname' => addslashes($child->linkName),
                    'mid' => $mid,
                    'nid' => addslashes($child->nid),
                    'click_url' => addslashes($child->clickURL),
                    'icon_url' => addslashes($child->iconURL),
                    'show_url' => addslashes($child->showURL),
                    'available' => 'yes',
                    'insert_date' => date("Y-m-d H:i:s"),
                    'last_update_date' => date("Y-m-d H:i:s"),
                    'parsed' => 0
                );
                //UPDATE Short Product
                $this->product_model->updateTempProduct($temp_shortProd,$siteRow['id'],$mid,$cat_id,$child->linkID);
                //$shortProd[] = $temp_shortProd;                
            }
            else{    
                $temp_shortProd = array(
                    'id_site' => $siteRow['id'],
                    'cat_creative_id' => addslashes($child->categoryID),
                    'cat_creative_name' => addslashes($child->categoryName),
                    'linkid' => addslashes($child->linkID),
                    'linkname' => addslashes($child->linkName),
                    'mid' => $mid,
                    'nid' => addslashes($child->nid),
                    'click_url' => addslashes($child->clickURL),
                    'icon_url' => addslashes($child->iconURL),
                    'show_url' => addslashes($child->showURL),
                    'available' => 'yes',
                    'insert_date' => date("Y-m-d H:i:s"),
                    'last_update_date' => date("Y-m-d H:i:s"),
                    'parsed' => 0
                );
                //ADD New Short Products
                $this->product_model->newTempProduct($temp_shortProd);
                //$shortProd[] = $temp_shortProd;
            }
        }

        redirect("admincp3/linkshare/listTempProducts/" . $mid . "/" . $cat_id . "");
        //echo"<pre>";print_r($shortProd);die();
    }

    public function parseProductSearch($mid, $cat_id = 0) {
        error_reporting(E_ERROR);
        include "app/third_party/LOG/Log.php";
        $CI = & get_instance();

        $this->load->model(array('site_model', 'product_model', 'product_new_model', 'category_creative_model'));
        $siteRow = $this->site_model->getSiteBySID($this->siteID);

        $config = new LinkshareConfig();
        $accessToken = $config->setSiteCookieAndGetAccessToken($CI, $this->siteID);

        $flag = 0;
        $limit = 1;
        $shortProd = $this->product_model->getTempProducts($mid, $cat_id, $limit, $flag);

        if(empty($shortProd)){redirect("admincp3/linkshare/listTempProducts/" . $mid . "/" . $cat_id . "");}
        
        $tempLink = $this->cleanLink($shortProd['linkname']);
        $keyword = urlencode($tempLink);

//      // LEGACY
//      $produs = simplexml_load_file("http://productsearch.linksynergy.com/productsearch?token=ff8c950f7a1c7f4f7b3db7e9407bbd89822f611a6c44c441bc9043c5edaa4746&keyword=\"{$keyword}\"&MaxResults=1&pagenumber=1&mid=$mid", "SimpleXMLElement", LIBXML_NOCDATA);
//      //  http://productsearch.linksynergy.com/productsearch?token=c9b4a2805e6d69846a3b7c9f0c23c26249cb86bc50fe864ff13746a8ab7dc92f&keyword=%22Heart%20Brooch%20in%2014%20Karat%20Gold%22&MaxResults=1&pagenumber=1&mid=37557       
//      echo "<pre>"; print_r($produs);die();
//      // END LEGACY

        $requestLink = "https://api.rakutenmarketing.com/productsearch/1.0?keyword={$keyword}&max=100&mid=$mid";

        $request = new CurlApi($requestLink);
        $request->setHeaders($config->getMinimalHeaders($accessToken));
        $request->setGetData();
        $request->send();

        $responseObj = $request->getFormattedResponse();

        $auxProd = $responseObj['body'];

        $prod = simplexml_load_string($auxProd, "SimpleXMLElement", LIBXML_NOCDATA);

        foreach ($prod->item as $valProd) {
            $keyProd = $valProd->linkid;
            $tempProd = array(
                'mid' => addslashes($valProd->mid),
                'merchantname' => addslashes($valProd->merchantname),
                'linkid' => addslashes($valProd->linkid),
                'createdon' => addslashes($valProd->createdon),
                'sku' => addslashes($valProd->sku),
                'productname' => addslashes($valProd->productname),
                'categ_primary' => addslashes($valProd->category->primary),
                'categ_secondary' => addslashes($valProd->category->secondary),
                'price' => addslashes($valProd->price),
                'currency' => addslashes($valProd->price["currency"]),
                'upccode' => addslashes($valProd->upccode),
                'description_short' => addslashes($valProd->description->short),
                'description_long' => addslashes($valProd->description->long),
                'saleprice' => addslashes($valProd->saleprice),
                'keywords' => addslashes($valProd->keywords),
                'linkurl' => addslashes($valProd->linkurl),
                'imageurl' => addslashes($valProd->imageurl),
                'price_list' => addslashes($valProd->saleprice),
                'price_save' => 0,
                'price_final' => 0,
                'parsed' => 1
            );

            $longProd["$keyProd"] = $tempProd;
        }

        if (is_null($prod->item[0]->linkurl)) {
            // Delete short prod
            $this->product_model->deleteTempProduct($shortProd['id']);
        }

        if (array_key_exists($shortProd['linkid'], $longProd)) {
            // Update short product to full details
            $keyProdtoUpdate = $shortProd['linkid'];
            $update_fields = $longProd["$keyProdtoUpdate"];
            
            $this->product_model->updateTempProductByLinkID($update_fields,$shortProd['linkid']);
            
        } else {
            $logMessage = "Product from Category $cat_id and Advertiser mid $mid with linkid : {$shortProd['linkid']}. | NOT found on search product!";
            Log::warn($logMessage);
        }
//      die();
        echo '<META http-equiv="refresh" content="10;URL=/admincp3/linkshare/parseProductSearch/' . $mid . '/' . $cat_id . '">';

    }

    public function getXmlProducts() {
        $CI = & get_instance();
        $this->load->library('admin_form');
        $form = new Admin_form;

        $this->load->model(array('site_model', 'advertiser_model'));
        $this->admin_navigation->module_link('Back', site_url('admincp/linkshare/getXML/'));
        $this->admin_navigation->module_link('Parse ALL Creative Categories', site_url('admincp2/linkshare/parseCreativeCategories'));

        $siteRow = $this->site_model->getSiteBySID($this->siteID);

        $temp = $this->advertiser_model->getTempAdvertisers($siteID['id']);
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
            'form_title' => 'Products XML',
            'form_scope' => 'products',
            'site_name' => $siteRow['name'],
            'allMids' => $allMids,
            'form_action' => site_url('admincp/linkshare/')
        );

        $this->load->view('xml', $data);
    }
    
    

}
