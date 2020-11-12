<?php
use \thiagova\PageAdmin;
use \thiagova\Model\User;
use \thiagova\Model\Product;

$app->get("/admin/products", function(){
    User::verifylogin();
    $products = Product::ListAll();
    $page = new PageAdmin();
    $page->setTpl("products", [
        "products"=>$products
    ]);
});

$app->get("/admin/products/create", function(){
    User::verifylogin();
    $page = new PageAdmin();
    $page->setTpl("products-create");
});

$app->post("/admin/products/create", function(){
    User::verifylogin();
    $product = new Product();
    $product->setData($_POST);
    $product->save();
});

$app->get("/admin/products/:idproduct", function($idproduct){
    User::verifylogin();
    $product = new Product();
    $product->get((int)$idproduct);
    $page = new PageAdmin();
    $page->setTpl("products-update", [
        "product"=>$product->getValues()
    ]);
});

$app->post("/admin/products/:idproduct", function($idproduct){
    User::verifylogin();
    $product = new Product();
    $product->get((int)$idproduct);
    
    $product->setData($_POST);
    $product->save();
    $product->setPhoto($_FILES["name"]);
    header("Location: /admin/products");
    exit;
});