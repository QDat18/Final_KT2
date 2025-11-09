<?php
// File: controllers/ProductVariantController.php

$product_id = $_REQUEST['product_id'] ?? 0;
$redirect_url = "Location: index.php?controller=product&action=edit&id=" . $product_id;

// Helper function to remove Vietnamese accents
function removeVietnameseAccents($str) {
    $vietnamese = [
        'à', 'á', 'ạ', 'ả', 'ã', 'â', 'ầ', 'ấ', 'ậ', 'ẩ', 'ẫ', 'ă', 'ằ', 'ắ', 'ặ', 'ẳ', 'ẵ',
        'è', 'é', 'ẹ', 'ẻ', 'ẽ', 'ê', 'ề', 'ế', 'ệ', 'ể', 'ễ',
        'ì', 'í', 'ị', 'ỉ', 'ĩ',
        'ò', 'ó', 'ọ', 'ỏ', 'õ', 'ô', 'ồ', 'ố', 'ộ', 'ổ', 'ỗ', 'ơ', 'ờ', 'ớ', 'ợ', 'ở', 'ỡ',
        'ù', 'ú', 'ụ', 'ủ', 'ũ', 'ư', 'ừ', 'ứ', 'ự', 'ử', 'ữ',
        'ỳ', 'ý', 'ỵ', 'ỷ', 'ỹ',
        'đ',
        'À', 'Á', 'Ạ', 'Ả', 'Ã', 'Â', 'Ầ', 'Ấ', 'Ậ', 'Ẩ', 'Ẫ', 'Ă', 'Ằ', 'Ắ', 'Ặ', 'Ẳ', 'Ẵ',
        'È', 'É', 'Ẹ', 'Ẻ', 'Ẽ', 'Ê', 'Ề', 'Ế', 'Ệ', 'Ể', 'Ễ',
        'Ì', 'Í', 'Ị', 'Ỉ', 'Ĩ',
        'Ò', 'Ó', 'Ọ', 'Ỏ', 'Õ', 'Ô', 'Ồ', 'Ố', 'Ộ', 'Ổ', 'Ỗ', 'Ơ', 'Ờ', 'Ớ', 'Ợ', 'Ở', 'Ỡ',
        'Ù', 'Ú', 'Ụ', 'Ủ', 'Ũ', 'Ư', 'Ừ', 'Ứ', 'Ự', 'Ử', 'Ữ',
        'Ỳ', 'Ý', 'Ỵ', 'Ỷ', 'Ỹ',
        'Đ'
    ];
    
    $latin = [
        'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a',
        'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e',
        'i', 'i', 'i', 'i', 'i',
        'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o',
        'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u',
        'y', 'y', 'y', 'y', 'y',
        'd',
        'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A',
        'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E',
        'I', 'I', 'I', 'I', 'I',
        'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O',
        'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U',
        'Y', 'Y', 'Y', 'Y', 'Y',
        'D'
    ];
    
    return str_replace($vietnamese, $latin, $str);
}

// Color mapping for SKU generation
$color_map_sku = [
    'Đen' => 'BLACK',
    'Trắng' => 'WHITE',
    'Bạc' => 'SILVER',
    'Xám' => 'GRAY',
    'Titan Tự nhiên' => 'NATURAL',
    'Vàng' => 'GOLD',
    'Đỏ' => 'RED',
    'Xanh Dương' => 'BLUE',
    'Xanh Lá' => 'GREEN',
    'Tím' => 'PURPLE',
    'Hồng' => 'PINK',
    'Beige' => 'BEIGE',
    'Platinum' => 'PLATINUM',
];

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

            $color = trim(sanitizeString($_POST['color'] ?? ''));
            $storage = trim(sanitizeString($_POST['storage'] ?? ''));
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

            // Generate SKU
            $product_sku = $product['sku'];
            
            // Use predefined mapping or convert color name
            if (isset($color_map_sku[$color])) {
                $color_slug = $color_map_sku[$color];
            } else {
                $color_clean = removeVietnameseAccents($color);
                $color_slug = strtoupper(str_replace(' ', '', $color_clean));
            }

            // Remove spaces from storage
            $storage_slug = str_replace(' ', '', $storage);

            $generated_sku = $product_sku . '-' . $color_slug . '-' . $storage_slug;

            // Normalize input data
            $color = trim($color);
            $storage = trim($storage);

            // Check if variant already exists (case-insensitive, ignore spaces)
            $existing = $variantModel->findByProductColorStorage($product_id, $color, $storage);
            
            if ($existing) {
                // Highlight the existing variant
                echo json_encode([
                    'success' => false, 
                    'message' => "⚠️ Biến thể màu <strong>{$color}</strong> - dung lượng <strong>{$storage}</strong> đã tồn tại!<br>Vui lòng chỉnh sửa biến thể hiện có thay vì thêm mới.",
                    'type' => 'warning',
                    'existing_id' => $existing['id']
                ]);
                exit; 
            }

            // Double check SKU
            if ($variantModel->findBySKU($generated_sku)) {
                echo json_encode([
                    'success' => false, 
                    'message' => "SKU {$generated_sku} đã tồn tại trong hệ thống!",
                    'type' => 'warning'
                ]);
                exit; 
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
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $file_type = $_FILES['image']['type'];
                
                if (!in_array($file_type, $allowed_types)) {
                    throw new Exception('Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WEBP)');
                }
                
                $max_size = 5 * 1024 * 1024; // 5MB
                if ($_FILES['image']['size'] > $max_size) {
                    throw new Exception('Kích thước file không được vượt quá 5MB');
                }

                $target_dir = "uploads/variants/";
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0755, true);
                }

                $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
                $new_file_name = uniqid('variant_', true) . '.' . $file_extension;
                $target_file = $target_dir . $new_file_name;

                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $data['image'] = $target_file;
                } else {
                    throw new Exception('Upload ảnh thất bại');
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
                    'Hồng' => '#FFC0CB',
                    'Beige' => '#F5F5DC',
                    'Platinum' => '#E5E4E2'
                ];

                // Get color hex
                $bg_hex = $color_map[$new_variant['color']] ?? '#CCCCCC';
                $border = ($bg_hex == '#FFFFFF') ? 'border: 1px solid #ccc;' : '';

                // Generate HTML for the new row
                $image = !empty($new_variant['image'])
                    ? '<img src="' . htmlspecialchars($new_variant['image']) . '" class="variant-thumbnail" alt="Variant image">'
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
                                ' . number_format($new_variant['price'], 0, ',', '.') . ' đ
                            </span>
                        </td>
                        <td>
                            <span id="variant-stock-' . $new_variant_id . '">
                                ' . number_format($new_variant['stock']) . '
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
                throw new Exception('Thêm biến thể thất bại');
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

            if ($id <= 0) throw new Exception('ID không hợp lệ');
            if ($price <= 0) throw new Exception('Giá không hợp lệ');
            if ($stock < 0) throw new Exception('Tồn kho không hợp lệ');

            $existing_variant = $variantModel->getByID($id);
            if (!$existing_variant) {
                throw new Exception('Biến thể không tồn tại');
            }

            $image_url = $existing_variant['image'];

            // Handle image upload if present
            if (isset($_FILES['variant_image']) && $_FILES['variant_image']['error'] == 0) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $file_type = $_FILES['variant_image']['type'];
                
                if (!in_array($file_type, $allowed_types)) {
                    throw new Exception('Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WEBP)');
                }
                
                $max_size = 5 * 1024 * 1024; // 5MB
                if ($_FILES['variant_image']['size'] > $max_size) {
                    throw new Exception('Kích thước file không được vượt quá 5MB');
                }

                // Delete old image if exists
                if (!empty($image_url) && file_exists($image_url)) {
                    @unlink($image_url);
                }

                $target_dir = "uploads/variants/";
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0755, true);
                }

                $file_extension = strtolower(pathinfo($_FILES["variant_image"]["name"], PATHINFO_EXTENSION));
                $new_file_name = uniqid('variant_', true) . '.' . $file_extension;
                $target_file = $target_dir . $new_file_name;

                if (move_uploaded_file($_FILES["variant_image"]["tmp_name"], $target_file)) {
                    $image_url = $target_file;
                } else {
                    throw new Exception('Upload ảnh thất bại');
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
                    'price' => number_format($price, 0, ',', '.') . ' đ',
                    'stock' => number_format($stock),
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

            if ($id <= 0) {
                throw new Exception('ID không hợp lệ');
            }

            $variant = $variantModel->getByID($id);
            if (!$variant) {
                throw new Exception('Biến thể không tồn tại');
            }

            // Delete image file if exists
            if (!empty($variant['image']) && file_exists($variant['image'])) {
                @unlink($variant['image']);
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

            $color = trim(sanitizeString($_POST['color'] ?? ''));
            $storage = trim(sanitizeString($_POST['storage'] ?? ''));
            $price = filter_var($_POST['price'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $stock = filter_var($_POST['stock'] ?? 0, FILTER_SANITIZE_NUMBER_INT);

            if (empty($color)) $errors[] = "Màu sắc không được để trống";
            if (empty($storage)) $errors[] = "Dung lượng không được để trống";
            if ($price <= 0) $errors[] = "Giá không hợp lệ";
            if ($stock < 0) $errors[] = "Tồn kho không hợp lệ";

            if (empty($errors)) {
                $product_sku = $product['sku'];
                
                if (isset($color_map_sku[$color])) {
                    $color_slug = $color_map_sku[$color];
                } else {
                    $color_clean = removeVietnameseAccents($color);
                    $color_slug = strtoupper(str_replace(' ', '', $color_clean));
                }
                
                $storage_slug = str_replace(' ', '', $storage);
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
                        if (!is_dir($target_dir)) {
                            mkdir($target_dir, 0755, true);
                        }
                        
                        $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
                        $new_file_name = uniqid('variant_', true) . '.' . $file_extension;
                        $target_file = $target_dir . $new_file_name;
                        
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

            if ($id <= 0) $errors[] = "ID không hợp lệ";
            if ($price <= 0) $errors[] = "Giá không hợp lệ";
            if ($stock < 0) $errors[] = "Tồn kho không hợp lệ";

            if (empty($errors)) {
                $existing_variant = $variantModel->getByID($id);
                if (!$existing_variant) {
                    $_SESSION['error_message'] = "Biến thể không tồn tại.";
                } else {
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
                }
            } else {
                $_SESSION['error_message'] = implode('<br>', $errors);
            }
        }
        header($redirect_url);
        break;

    case 'delete':
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            $_SESSION['error_message'] = "ID không hợp lệ.";
        } else {
            $variant = $variantModel->getByID($id);
            if ($variant && !empty($variant['image']) && file_exists($variant['image'])) {
                @unlink($variant['image']);
            }
            
            if ($variantModel->delete($id)) {
                $_SESSION['success_message'] = "Xóa biến thể thành công!";
            } else {
                $_SESSION['error_message'] = "Xóa biến thể thất bại.";
            }
        }
        header($redirect_url);
        break;

    default:
        header($redirect_url);
        break;
}