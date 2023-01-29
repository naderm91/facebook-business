<?php

$records = getBusinessOwnedProductCatalogs(
    $_ENV['APP_ID'],
    $_ENV['APP_SECRET'],
    $_SESSION['fb_business']['access_token'],
    $_SESSION['fb_business']['business_id']
);

if (count($records) === 1 && count($records[0]['product_sets']) === 1) {
    $_SESSION['fb_business']['product_catalog_id'] = $records[0]['id'];
    $_SESSION['fb_business']['product_set_id'] = $records[0]['product_sets'][0]['id'];
}

foreach ($records as $record) {
    foreach ($record['product_sets'] as $product_set) {
        $title = "Catalog #: {$record['id']} - {$record['name']} - Product Set #: {$product_set['id']} - {$product_set['name']}";

        if (isset($_SESSION['fb_business']['product_set_id']) && $product_set['id'] === $_SESSION['fb_business']['product_set_id']) {
            echo "<li>$title</li>";
        } else {
            echo "<li><a href='" . $_ENV['APP_URL']
                . "/?route=select&key=product_catalog_id,product_set_id&value={$record['id']},{$product_set['id']}'>$title</a></li>";
        }
    }
}
