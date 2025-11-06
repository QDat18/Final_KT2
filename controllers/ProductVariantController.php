<?php
// File: controllers/ProductVariantController.php

$product_id = $_REQUEST['product_id'] ?? 0;
$redirect_url = "Location: index.php?controller=product&action=edit&id=" . $product_id;

switch ($action) {
    // ========== AJAX CREATE VARIANT ==========
    case 'ajax_store':
        header('Content-Type: application/json');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $product = $productModel->getByID($product_id);
            if (!$product) {
                throw new Exception('Sản phẩm không tồn tại');
            }

            $color = sanitizeString($_POST['color'] ?? '');
            $storage = sanitizeString($_POST['storage'] ?? '');
            $price = filter_var($_POST['price'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $stock = filter_var($_POST['stock'] ?? 0, FILTER_SANITIZE_NUMBER_INT);

            // Validation
            $errors = [];
            if (empty($color)) $errors[] = "Màu sắc không được để trống";
            if (empty($storage)) $errors[] = "Dung lượng không được để trống";
            if ($price <= 0) $errors[] = "Giá không hợp lệ";
            if ($stock < 0) $errors[] = "Tồn kho không hợp lệ";

            if (!empty($errors)) {
                throw new Exception(implode(', ', $errors));
            }

            // Generate SKU - FIX: Remove Vietnamese characters properly
            $product_sku = $product['sku'];
            
            // Clean color name: remove Vietnamese marks and convert to proper format
            $color_clean = str_replace(
                ['Đ', 'đ', 'Á', 'á', 'À', 'à', 'Ả', 'ả', 'Ã', 'ã', 'Ạ', 'ạ', 
                 'Ă', 'ă', 'Ắ', 'ắ', 'Ằ', 'ằ', 'Ẳ', 'ẳ', 'Ẵ', 'ẵ', 'Ặ', 'ặ',
                 'Â', 'â', 'Ấ', 'ấ', 'Ầ', 'ầ', 'Ẩ', 'ẩ', 'Ẫ', 'ẫ', 'Ậ', 'ậ',
                 'É', 'é', 'È', 'è', 'Ẻ', 'ẻ', 'Ẽ', 'ẽ', 'Ẹ', 'ẹ',
                 'Ê', 'ê', 'Ế', 'ế', 'Ề', 'ề', 'Ể', 'ể', 'Ễ', 'ễ', 'Ệ', 'ệ',
                 'Í', 'í', 'Ì', 'ì', 'Ỉ', 'ỉ', 'Ĩ', 'ĩ', 'Ị', 'ị',
                 'Ó', 'ó', 'Ò', 'ò', 'Ỏ', 'ỏ', 'Õ', 'õ', 'Ọ', 'ọ',
                 'Ô', 'ô', 'Ố', 'ố', 'Ồ', 'ồ', 'Ổ', 'ổ', 'Ỗ', 'ỗ', 'Ộ', 'ộ',
                 'Ơ', 'ơ', 'Ớ', 'ớ', 'Ờ', 'ờ', 'Ở', 'ở', 'Ỡ', 'ỡ', 'Ợ', 'ợ',
                 'Ú', 'ú', 'Ù', 'ù', 'Ủ', 'ủ', 'Ũ', 'ũ', 'Ụ', 'ụ',
                 'Ư', 'ư', 'Ứ', 'ứ', 'Ừ', 'ừ', 'Ử', 'ử', 'Ữ', 'ữ', 'Ự', 'ự',
                 'Ý', 'ý', 'Ỳ', 'ỳ', 'Ỷ', 'ỷ', 'Ỹ', 'ỹ', 'Ỵ', 'ỵ', ' '],
                ['D', 'd', 'A', 'a', 'A', 'a', 'A', 'a', 'A', 'a', 'A', 'a',
                 'A', 'a', 'A', 'a', 'A', 'a', 'A', 'a', 'A', 'a', 'A', 'a',
                 'A', 'a', 'A', 'a', 'A', 'a', 'A', 'a', 'A', 'a', 'A', 'a',
                 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e',
                 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e',
                 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i',
                 'O', 'o', 'O', 'o', 'O', 'o', 'O', 'o', 'O', 'o',
                 'O', 'o', 'O', 'o', 'O', 'o', 'O', 'o', 'O', 'o', 'O', 'o',
                 'O', 'o', 'O', 'o', 'O', 'o', 'O', 'o', 'O', 'o', 'O', 'o',
                 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u',
                 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u',
                 'Y', 'y', 'Y', 'y', 'Y', 'y', 'Y', 'y', 'Y', 'y', ''],
                $color
            );
            $color_slug = ucfirst(strtolower($color_clean));
            
            // Extract numbers from storage
            preg_match('/(\d+)/', $storage, $matches);
            $storage_slug = $matches[1] ?? str_replace(' ', '', $storage);
            
            $generated_sku = $product_sku . '-' . $color_slug . '-' . $storage_slug;

            // Check if SKU exists
            if ($variantModel->findBySKU($generated_sku)) {
                throw new Exception("Biến thể {$generated_sku} đã tồn tại");
            }

            $data = [
                'product_id' => $product_id,
                'sku' => $generated_sku,
                'color' => $color,
                'storage' => $storage,
                'price' => $price,
                'stock' => $stock,
                'image' => ''
            ];

            // Handle image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $target_dir = "uploads/variants/";
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                
                $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
                $new_file_name = uniqid() . '.' . $file_extension;
                $target_file = $target_dir . $new_file_name;

                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $data['image'] = $target_file;
                }
            }

            if ($variantModel->create($data)) {
                $new_variant_id = $db->lastInsertId();
                $new_variant = $variantModel->getByID($new_variant_id);
                
                // Color map for display
                $color_map = [
                    'Đen' => '#000000',
                    'Trắng' => '#FFFFFF',
                    'Bạc' => '#C0C0C0',
                    'Xám' => '#808080',
                    'Titan Tự nhiên' => '#A6A199',
                    'Vàng' => '#FFD700',
                    'Đỏ' => '#E74C3C',
                    'Xanh Dương' => '#3498DB',
                    'Xanh Lá' => '#2ECC71',
                    'Tím' => '#9B59B6',
                    'Hồng' => '#FADADD'
                ];
                
                // Get color hex
                $bg_hex = $color_map[$new_variant['color']] ?? '#FFFFFF';
                $border = ($bg_hex == '#FFFFFF') ? 'border: 1px solid #ccc;' : '';
                
                // Generate HTML for the new row
                $image = !empty($new_variant['image']) 
                    ? '<img src="' . htmlspecialchars($new_variant['image']) . '" class="variant-thumbnail">'
                    : '<i class="fas fa-image text-muted"></i>';
                
                $variant_html = '
                    <tr id="variant-' . $new_variant_id . '" class="fade-in">
                        <td id="variant-image-wrapper-' . $new_variant_id . '">' . $image . '</td>
                        <td><span class="badge bg-secondary">' . htmlspecialchars($new_variant['sku']) . '</span></td>
                        <td>
                            <span class="color-circle" style="background-color: ' . $bg_hex . '; ' . $border . '"></span>
                            ' . htmlspecialchars($new_variant['color']) . '
                        </td>
                        <td>' . htmlspecialchars($new_variant['storage']) . '</td>
                        <td>
                            <span id="variant-price-' . $new_variant_id . '">
                                ' . number_format($new_variant['price']) . ' đ
                            </span>
                        </td>
                        <td>
                            <span id="variant-stock-' . $new_variant_id . '">
                                ' . $new_variant['stock'] . '
                            </span>
                        </td>
                        <td>
                            <button type="button" 
                                    class="btn btn-warning-modern btn-sm btn-edit-variant" 
                                    data-id="' . $new_variant_id . '"
                                    data-color="' . htmlspecialchars($new_variant['color']) . '"
                                    data-storage="' . htmlspecialchars($new_variant['storage']) . '"
                                    data-price="' . $new_variant['price'] . '"
                                    data-stock="' . $new_variant['stock'] . '">
                                <i class="fas fa-edit"></i> Sửa
                            </button>
                            <button type="button" 
                                    class="btn btn-danger-modern btn-sm btn-delete-variant" 
                                    data-id="' . $new_variant_id . '"
                                    data-name="' . htmlspecialchars($new_variant['color'] . ' - ' . $new_variant['storage']) . '">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                ';

                echo json_encode([
                    'success' => true,
                    'message' => 'Thêm biến thể thành công!',
                    'variant_html' => $variant_html,
                    'variant_id' => $new_variant_id
                ]);
            } else {
                throw new Exception('Không thể tạo biến thể');
            }

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;

    // ========== AJAX UPDATE VARIANT ==========
    case 'ajax_update':
        header('Content-Type: application/json');
        
        try {
            $id = (int)($_POST['id'] ?? 0);
            $price = filter_var($_POST['price'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $stock = filter_var($_POST['stock'] ?? 0, FILTER_SANITIZE_NUMBER_INT);

            if ($price <= 0) throw new Exception('Giá không hợp lệ');
            if ($stock < 0) throw new Exception('Tồn kho không hợp lệ');

            $existing_variant = $variantModel->getByID($id);
            if (!$existing_variant) {
                throw new Exception('Biến thể không tồn tại');
            }

            $image_url = $existing_variant['image'];
            
            // Handle image upload if present
            if (isset($_FILES['variant_image']) && $_FILES['variant_image']['error'] == 0) {
                // Delete old image if exists
                if (!empty($image_url) && file_exists($image_url)) {
                    unlink($image_url);
                }
                
                $target_dir = "uploads/variants/";
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                
                $file_extension = pathinfo($_FILES["variant_image"]["name"], PATHINFO_EXTENSION);
                $new_file_name = uniqid() . '.' . $file_extension;
                $target_file = $target_dir . $new_file_name;

                if (move_uploaded_file($_FILES["variant_image"]["tmp_name"], $target_file)) {
                    $image_url = $target_file;
                }
            }

            $data = [
                'id' => $id,
                'sku' => $existing_variant['sku'],
                'color' => $existing_variant['color'],
                'storage' => $existing_variant['storage'],
                'price' => $price,
                'stock' => $stock,
                'image' => $image_url
            ];

            if ($variantModel->update($data)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Cập nhật biến thể thành công!',
                    'price' => number_format($price) . ' đ',
                    'stock' => $stock,
                    'image_url' => $image_url
                ]);
            } else {
                throw new Exception('Cập nhật thất bại');
            }

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;

    // ========== AJAX DELETE VARIANT ==========
    case 'ajax_delete':
        header('Content-Type: application/json');
        
        try {
            $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
            
            $variant = $variantModel->getByID($id);
            if (!$variant) {
                throw new Exception('Biến thể không tồn tại');
            }

            // Delete image file if exists
            if (!empty($variant['image']) && file_exists($variant['image'])) {
                unlink($variant['image']);
            }

            if ($variantModel->delete($id)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Xóa biến thể thành công!'
                ]);
            } else {
                throw new Exception('Xóa biến thể thất bại');
            }

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;

    // ========== NORMAL ACTIONS ==========
    case 'store':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = [];
            $product = $productModel->getByID($product_id);
            if (!$product) {
                $_SESSION['error_message'] = "Sản phẩm không tồn tại.";
                header($redirect_url);
                exit;
            }

            $color = sanitizeString($_POST['color'] ?? '');
            $storage = sanitizeString($_POST['storage'] ?? '');
            $price = filter_var($_POST['price'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $stock = filter_var($_POST['stock'] ?? 0, FILTER_SANITIZE_NUMBER_INT);

            if (empty($color)) $errors[] = "Màu sắc không được để trống";
            if (empty($storage)) $errors[] = "Dung lượng không được để trống";
            if ($price <= 0) $errors[] = "Giá không hợp lệ";
            if ($stock < 0) $errors[] = "Tồn kho không hợp lệ";

            if (empty($errors)) {
                $product_sku = $product['sku'];
                $color_slug = ucfirst(strtolower($color));
                preg_match('/(\d+)/', $storage, $matches);
                $storage_slug = $matches[1] ?? $storage;
                $generated_sku = $product_sku . '-' . $color_slug . '-' . $storage_slug;
                
                if ($variantModel->findBySKU($generated_sku)) {
                    $errors[] = "Biến thể {$generated_sku} đã tồn tại.";
                } else {
                    $data = [
                        'product_id' => $product_id,
                        'sku' => $generated_sku,
                        'color' => $color,
                        'storage' => $storage,
                        'price' => $price,
                        'stock' => $stock,
                        'image' => ''
                    ];
                    
                    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                        $target_dir = "uploads/variants/";
                        $target_file = $target_dir . basename($_FILES["image"]["name"]);
                        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                            $data['image'] = $target_file;
                        }
                    }
                    
                    if ($variantModel->create($data)) {
                        $_SESSION['success_message'] = "Thêm biến thể thành công!";
                    } else {
                        $errors[] = "Thêm biến thể thất bại.";
                    }
                }
            }
            
            if (!empty($errors)) {
                $_SESSION['error_message'] = implode('<br>', $errors);
            }
        }
        header($redirect_url);
        break;

    case 'update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = [];
            $id = (int)($_POST['id'] ?? 0);
            $price = filter_var($_POST['price'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $stock = filter_var($_POST['stock'] ?? 0, FILTER_SANITIZE_NUMBER_INT);

            if ($price <= 0) $errors[] = "Giá không hợp lệ";
            if ($stock < 0) $errors[] = "Tồn kho không hợp lệ";

            if (empty($errors)) {
                $existing_variant = $variantModel->getByID($id);
                $data = [
                    'id' => $id,
                    'sku' => $existing_variant['sku'],
                    'color' => $existing_variant['color'],
                    'storage' => $existing_variant['storage'],
                    'price' => $price,
                    'stock' => $stock,
                    'image' => $existing_variant['image']
                ];

                if ($variantModel->update($data)) {
                    $_SESSION['success_message'] = "Cập nhật biến thể thành công!";
                } else {
                    $_SESSION['error_message'] = "Cập nhật biến thể thất bại.";
                }
            } else {
                $_SESSION['error_message'] = implode('<br>', $errors);
            }
        }
        header($redirect_url);
        break;

    case 'delete':
        $id = (int)($_GET['id'] ?? 0);
        if ($variantModel->delete($id)) {
            $_SESSION['success_message'] = "Xóa biến thể thành công!";
        } else {
            $_SESSION['error_message'] = "Xóa biến thể thất bại.";
        }
        header($redirect_url);
        break;

    default:
        header($redirect_url);
        break;
}