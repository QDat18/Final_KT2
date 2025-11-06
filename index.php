<?php
session_start(); 
require_once 'config/database.php';
require_once 'helpers/utils.php'; 
require_once 'router.php';
$database = new Database();
$db = $database->getConnection();

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);


require_once 'models/Product.php';
require_once 'models/ProductVariant.php';
require_once 'models/ProductImage.php';

// Khởi tạo các model
$productModel = new Product($db);
$variantModel = new ProductVariant($db);
$imageModel = new ProductImage($db);

$route = getRoute();
$controller = $route['controller'];
$action = $route['action'];

switch ($controller) {
    case 'product':
        require 'controllers/ProductController.php';
        break;
    case 'variant':
        require 'controllers/ProductVariantController.php';
        break;
    case 'image':
        require 'controllers/ProductImageController.php';
        break;
    default:
        http_response_code(404);
        echo "404 - Controller not found";
        break;
}
?>