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

    public function changeProductStatus($id_product, $ret_url) {
        $ret_url = base64_decode($ret_url);
        $this->load->model('product_model');
        $this->product_model->changeProductStatus($id_product);
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

        $this->load->model(array('site_model', 'product_model'));
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
            //echo"<pre>";print_r($temp_shortProd);
            $this->product_model->newTempProduct($temp_shortProd);
            //$shortProd[] = $temp_shortProd;
        }

        redirect("admincp3/linkshare/parseProductSearch/" . $mid . "/" . $cat_id . "");
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

        if(empty($shortProd)){redirect("admincp3/linkshare/listProducts/" . $mid . "/" . $cat_id . "");}
        
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

    public function parseProductXmlReaderSearch($id, $mid, $cat_id = 0, $partial = 0, $page = 1, $id_produs_partial = 1, $j = 0, $k = 0) {
        error_reporting(E_ERROR);
        $aux = '';
        $this->load->model('site_model');
        $aux = $this->site_model->getSite($id);
        $token = $aux['token'];

        $this->load->model('product_model');
        $this->load->model('product_partial_model');
        $this->load->model('product_new_model');

        $creative_categories = array();
        $this->load->model('category_creative_model');
        $filters['id_site'] = $id;
        $filters['mid'] = $mid;
        $creative_categories = $this->category_creative_model->getCategories($filters);

        $data = array();

        // print '<pre>';
        // print_r($creative_categories);
        // print "count = ".count($creative_categories);
        // die;

        if (!$partial) {

            //the creative category is not present it means we have finished adding/updating new products...now we must set the old ones to available='no' and return from method
            if (!array_key_exists($cat_id, $creative_categories)) {
                $old = $this->product_new_model->markOld($mid);
                $data['message'] = "$old products marked as not available";
                $this->load->view('parseProductSearch', $data);
                return;
            }

            $creative_category = $creative_categories[$cat_id]['cat_id'];
            $campaignID = -1;

            $xml = new XMLReader();

            $file = "http://lld2.linksynergy.com/services/restLinks/getProductLinks/$token/$mid/$creative_category/$campaignID/$page";

            if (!$xml->open($file)) {
                $fp = fopen("loguri.txt", "a");
                fwrite($fp, "Failed to open input file : http://lld2.linksynergy.com/services/restLinks/getProductLinks/$token/$mid/$creative_category/$campaignID/$page " . date("Y-m-d H:i:s") . PHP_EOL);
                fclose($fp);

                $data['message'] = "Failed to open input file : http://lld2.linksynergy.com/services/restLinks/getProductLinks/$token/$mid/$creative_category/$campaignID/$page " . date("Y-m-d H:i:s");

                $this->load->view('parseProductSearch', $data);

                $cat_id++;
                echo '<META http-equiv="refresh" content="1;URL=/admincp3/linkshare/parseProductXmlReaderSearch/' . $id . '/' . $mid . '/' . $cat_id . '">';
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

                        $this->product_partial_model->newProduct($product[$i]);

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

                    $this->load->view('parseProductSearch', $data);

                    $cat_id++;
                    echo '<META http-equiv="refresh" content="1;URL=/admincp3/linkshare/parseProductXmlReaderSearch/' . $id . '/' . $mid . '/' . $cat_id . '">';
                    die;
                }

                $partial = 1;
                $id_produs_partial = 1;
                $j = $k = 0;

                $data['message'] = $message;

                $this->load->view('parseProductSearch', $data);

                echo '<META http-equiv="refresh" content="1;URL=/admincp3/linkshare/parseProductXmlReaderSearch/' . $id . '/' . $mid . '/' . $cat_id . '/' . $partial . '/' . $page . '/' . $id_produs_partial . '/' . $j . '/' . $k . '/' . '">';
                die;
            }
        } else {
            $produs_partial = array();
            $produs_partial = $this->product_partial_model->getProduct($id_produs_partial);
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
                $this->product_new_model->newProduct($produs_partial);

                $exista = $this->product_model->existsProduct($produs_partial['linkid']);
                if (!$exista) {
                    $this->product_model->newProduct($produs_partial);
                    $j++;
                } else {
                    $this->product_model->updateProductByLinkID($produs_partial, $produs_partial['linkid']);
                    $k++;
                }

                $partial = 1;
                $id_produs_partial++;

                $data['message'] = "$id_produs_partial products parsed";

                $this->load->view('parseProductSearch', $data);

                echo '<META http-equiv="refresh" content="0;URL=/admincp3/linkshare/parseProductXmlReaderSearch/' . $id . '/' . $mid . '/' . $cat_id . '/' . $partial . '/' . $page . '/' . $id_produs_partial . '/' . $j . '/' . $k . '/' . '">';
                die;
            } else {

                $i = $id_produs_partial - 1;
                $fp = fopen("loguri.txt", "a");
                fwrite($fp, "http://lld2.linksynergy.com/services/restLinks/getProductLinks/$token/$mid/$creative_category/$campaignID/$page $i total products $j inserted $k updated  " . date("Y-m-d H:i:s") . PHP_EOL);
                fclose($fp);

                //i finished all the partial products,truncate the linkshare_produs_partial,increment the page and refresh
                $this->product_partial_model->deletePartialProduct();
                $partial = 0;
                $page++;

                $data['message'] = "cat_id $cat_id creative_category $creative_category page $page $i products parsed<br/>";
                $this->load->view('parseProductSearch', $data);
                echo '<META http-equiv="refresh" content="1;URL=/admincp3/linkshare/parseProductXmlReaderSearch/' . $id . '/' . $mid . '/' . $cat_id . '/' . $partial . '/' . $page . '">';
                die;
            }
        }

        $data['message'] = 'FINISHED';

        $this->load->view('parseProductSearch', $data);
    }

    public function testMarkOld() {
        $this->load->model('product_model');
        $this->load->model('product_new_model');

        $id = 2;
        $mid = 37920;
        $cat_id = 200260395;
        $linkid = 78900666;

        // Delete product from linkshare_produs
        $this->product_new_model->deleteOldFromCurrent($linkid, $mid);

        //$produs = array();
        //$produs = $this->product_new_model->getProductsByLinkID($linkid,$mid);
        //$this->product_new_model->copyToProductsOld($produs);
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
