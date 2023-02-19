<?php

use FacebookAds\Api;
use Facebook\Facebook;
use FacebookAds\Object\AbstractArchivableCrudObject;
use FacebookAds\Object\AdAccount;
use FacebookAds\Logger\CurlLogger;
use FacebookAds\Object\AdSet;
use FacebookAds\Object\Fields\AdSetFields;
use FacebookAds\Object\ProductCatalog;
use FacebookAds\Object\Fields\AdFields;
use Facebook\Exceptions\FacebookSDKException;
use FacebookAds\Object\Fields\CampaignFields;
use Facebook\Exceptions\FacebookResponseException;
use FacebookAds\Object\Fields\ProductCatalogFields;
use FacebookAds\Object\Values\CampaignBidStrategyValues;
use FacebookAds\Object\Values\AdSetOptimizationGoalValues;
use FacebookAds\Object\Fields\TargetingFields;
use FacebookAds\Object\Values\AdSetBillingEventValues;
use FacebookAds\Object\Targeting;

function dd(...$args)
{
    echo "<pre>";
    array_map('print_r', $args);
    exit;
}

function scopes()
{
    return [
        'ads_management',
        'ads_read',
        'business_management',
        'catalog_management',
        'pages_show_list'
    ];
}

/**
 * This function will redirect the user to the Facebook Login Dialog and request the necessary permissions:
 *
 * @param string $app_id
 * @param string $redirect_uri
 * @return void
 */
function requestAuth($app_id, $redirect_uri)
{
    $url = 'https://www.facebook.com/v15.0/dialog/oauth?' .
        http_build_query(array(
            'client_id' => $app_id,
            'redirect_uri' => $redirect_uri,
            'scope' => implode(',', scopes()),
        ));

    return $url;
}

function getAccessToken($app_id, $app_secret, $redirect_uri, $code)
{
    // Exchange the authorization code for an access token
    $token_url = 'https://graph.facebook.com/v15.0/oauth/access_token?' .
        http_build_query(array(
            'client_id' => $app_id,
            'redirect_uri' => $redirect_uri,
            'client_secret' => $app_secret,
            'code' => $code,
        ));

    $response = file_get_contents($token_url);
    $params = json_decode($response, true);

    if (isset($params['access_token'])) {
        return $params['access_token'];
    } else {
        throw new Exception('Error while fetching access token.');
    }
}

function getLongLivedAccessToken($app_id, $app_secret, $access_token)
{
    $fb = new Facebook([
        'app_id' => $app_id,
        'app_secret' => $app_secret,
        'default_graph_version' => 'v15.0',
        'default_access_token' => $access_token,
    ]);

    try {
        $response = $fb->get("/oauth/access_token?grant_type=fb_exchange_token&client_id={$app_id}&client_secret={$app_secret}&fb_exchange_token={$access_token}");
        $result = $response->getDecodedBody();

        if (isset($result['access_token'])) {
            return $result['access_token'];
        }
    } catch (FacebookResponseException | FacebookSDKException $e) {
    }

    throw new Exception('Error while fetching long-lived access token');
}

function debugToken($app_id, $app_secret, $long_lived_token)
{
    $fb = new Facebook([
        'app_id' => $app_id,
        'app_secret' => $app_secret,
        'default_graph_version' => 'v15.0',
    ]);

    try {
        $response = $fb->get("/debug_token?input_token={$long_lived_token}&access_token={$app_id}|{$app_secret}&extend_token=true");
        $result = $response->getDecodedBody();
        if (isset($result['data']['is_valid']) && $result['data']['is_valid'] == true) {
            return $result['data'];
        }
    } catch (FacebookResponseException | FacebookSDKException $e) {
    }

    throw new Exception('Error while fetching token information');
}

function validateAccessToken($app_id, $app_secret, $access_token = null)
{
    if (!$access_token) {
        return false;
    }

    try {
        $response  = debugToken($app_id, $app_secret, $access_token);
        $expiration_time = isset($response['data_access_expires_at']) ? $response['data_access_expires_at'] : 0;

        if ($expiration_time <= time()) {
            return false;
        }

        $scopes = isset($response['scopes']) ? $response['scopes'] : [];

        foreach (scopes() as $scope) {
            if (!in_array($scope, $scopes)) {
                return false;
            }
        }

        return $expiration_time;
    } catch (\Throwable $th) {
        return false;
    }
}

function getActAccounts($app_id, $app_secret, $access_token)
{
    $fb = new Facebook([
        'app_id' => $app_id,
        'app_secret' => $app_secret,
        'default_access_token' => $access_token,
        'default_graph_version' => 'v15.0',
    ]);

    try {
        $response = $fb->get('/me/adaccounts?fields=name,id,business,account_id,account_status');
        $result = $response->getDecodedBody();
        $result = $result['data'];
        $records = [];
        foreach ($result as $act) {
            if ($act['account_status'] == 1) {
                array_push($records, $act);
            }
        }
        return $records;
    } catch (FacebookResponseException $e) {
        echo 'Graph returned an error: ' . $e->getMessage();
        exit;
    } catch (FacebookSDKException $e) {
        echo 'Facebook SDK returned an error: ' . $e->getMessage();
        exit;
    }
}

function getPixelIds($app_id, $app_secret, $access_token, $act_account_id)
{
    $api = Api::init($app_id, $app_secret, $access_token);
    $api->setLogger(new CurlLogger());
    $api->setDefaultGraphVersion('15.0');

    $account = new AdAccount($act_account_id);
    $response = $account->getAdsPixels(['name', 'can_proxy'])->getResponse()->getContent();

    return $response['data'];
}

function getAllCampaigns($app_id, $app_secret, $access_token, $id)
{

    $api = Api::init($app_id, $app_secret, $access_token);
    $api->setLogger(new CurlLogger());
    $api->setDefaultGraphVersion('15.0');

    $fields = array(
        'name', 'objective', 'created_time', 'effective_status',
        'account_id', 'buying_type', 'start_time', 'stop_time',
        'pacing_type', 'bid_strategy', 'lifetime_budget',
    );

    $params = array(
        'effective_status' => array('ACTIVE', 'PAUSED', 'IN_PROCESS', 'WITH_ISSUES'),
    );

    $account = new AdAccount($id);

    $response = $account->getCampaigns(
        $fields,
        $params
    )->getResponse()->getContent();

    return $response['data'];
}

/**
 * @throws FacebookSDKException
 */
function createCampaign(
    $app_id, $app_secret, $access_token, $id, $pixel_id, $product_catalog_id, $product_set_id, $page_id)
{
    $api = Api::init($app_id, $app_secret, $access_token);
    $api->setDefaultGraphVersion('15.0');
    $api->setLogger(new CurlLogger());

    $account = new AdAccount($id);

    # Create campaign
    $campaign = $account->createCampaign(
        [],
        array(
            CampaignFields::NAME => 'Catalog Sales Campaign',
            CampaignFields::OBJECTIVE => 'PRODUCT_CATALOG_SALES', // 'CONVERSIONS', // PRODUCT_CATALOG_SALES
            'status' => 'PAUSED',
            'pacing_type' => array('standard'),
            'lifetime_budget' => 350 * 100,
            'bid_strategy' => CampaignBidStrategyValues::LOWEST_COST_WITHOUT_CAP,
            'special_ad_categories' => array(),
            CampaignFields::PROMOTED_OBJECT => array('product_catalog_id' => $product_catalog_id)
        )
    )->exportAllData();

    $adset = $account->createAdSet(array(), array(
            'name' => 'Catalog Sales Campaign Adset',
            //            'optimization_goal' => AdSetOptimizationGoalValues::LINK_CLICKS, // 'OFFSITE_CONVERSIONS',
            'optimization_goal' => 'REACH',
            'billing_event' => 'IMPRESSIONS',
            'campaign_id' => $campaign['id'],
            // 'daily_budget' => '1000',
            // 'bid_amount' => '2',
            'status' => 'PAUSED',
            'end_time' => '2023-02-20T15:41:30+0000',
            'targeting' => array(
//                'age_min' => 20,
//                'age_max' => 24,
//                'genders' => array(1),
                'geo_locations' => array('countries' => array('SA'))
            ),
            'promoted_object' => array(
                // 'pixel_id' => $pixel_id,
                // 'custom_event_type' => 'CONTENT_VIEW',
                'product_set_id' => $product_set_id,
            ),
        ))->exportAllData();
    # Create adset
    //Campaign Id :23853505779060149

    $data = json_decode(file_get_contents("json/adcreative.json"), TRUE);
    $creative = $account->createAdCreative(array(),  $data)->exportAllData();

    //todo
    try {

//        $data = json_decode(file_get_contents("json/ad.json"), TRUE);
//        $account->setData($data);
//        $ad = $account->createAd();
    } catch (\Facebook\Exceptions\FacebookResponseException $e) {
        // When Graph returns an error
        echo 'Graph returned an error: ' . $e->getMessage();
    } catch (\Facebook\Exceptions\FacebookSDKException $e) {
        // When validation fails or other local issues
        echo 'Facebook SDK returned an error: ' . $e->getMessage();
    }
    die;
}

function getBusinesses($app_id, $app_secret, $access_token)
{
    $result = [];
    $fb = new Facebook([
        'app_id' => $app_id,
        'app_secret' => $app_secret,
        'default_access_token' => $access_token,
        'default_graph_version' => 'v15.0',
    ]);
    try {
        $response = $fb->get('/me/businesses?fields=id,name');
        $businesses = $response->getGraphEdge();
        if (!empty($businesses)) {
            foreach ($businesses as $business) {
                $result[] = $business->asArray();
            }
        }
    } catch (\Facebook\Exceptions\FacebookResponseException $e) {
        // When Graph returns an error
        echo 'Graph returned an error: ' . $e->getMessage();
    } catch (\Facebook\Exceptions\FacebookSDKException $e) {
        // When validation fails or other local issues
        echo 'Facebook SDK returned an error: ' . $e->getMessage();
    }
    return $result;
}

function getBusinessOwnedPages($app_id, $app_secret, $access_token, $business_id)
{
    $result = [];
    $fb = new Facebook([
        'app_id' => $app_id,
        'app_secret' => $app_secret,
        'default_access_token' => $access_token,
        'default_graph_version' => 'v15.0',
    ]);
    try {
        $response = $fb->get('/' . $business_id . '/owned_pages?fields=name,id');
        $pages = $response->getGraphEdge();
        if (!empty($pages)) {
            foreach ($pages as $page) {
                $result[] = $page->asArray();
            }
        }
    } catch (FacebookResponseException $e) {
        // When Graph returns an error
        echo 'Graph returned an error: ' . $e->getMessage();
        exit;
    } catch (FacebookSDKException $e) {
        // When validation fails or other local issues
        echo 'Facebook SDK returned an error: ' . $e->getMessage();
        exit;
    }
    return $result;
}

function getBusinessOwnedProductCatalogs($app_id, $app_secret, $access_token, $business_id)
{
    $result = [];
    $fb = new Facebook([
        'app_id' => $app_id,
        'app_secret' => $app_secret,
        'default_access_token' => $access_token,
        'default_graph_version' => 'v15.0',
    ]);
    try {
        $response = $fb->get('/' . $business_id . '/owned_product_catalogs?fields=id,product_count,name,catalog_store,commerce_merchant_settings,feed_count,is_catalog_segment,product_sets{id,name,product_count}');
        $catalogs = $response->getGraphEdge();
        if (!empty($catalogs)) {
            foreach ($catalogs as $catalog) {
                $result[] = $catalog->asArray();
            }
        }
    } catch (FacebookResponseException $e) {
        // When Graph returns an error
        echo 'Graph returned an error: ' . $e->getMessage();
        exit;
    } catch (FacebookSDKException $e) {
        // When validation fails or other local issues
        echo 'Facebook SDK returned an error: ' . $e->getMessage();
        exit;
    }
    return $result;
}

function getProductCatalogs($app_id, $app_secret, $access_token, $act_account_id)
{
    $api = Api::init($app_id, $app_secret, $access_token);
    $api->setLogger(new CurlLogger());
    $api->setDefaultGraphVersion('15.0');
    $catalog = new ProductCatalog($act_account_id,);
    $catalogs = $catalog->getSelf(
        array(
            ProductCatalogFields::ID,
            ProductCatalogFields::NAME,
        )
    );

    return $catalogs;
}
