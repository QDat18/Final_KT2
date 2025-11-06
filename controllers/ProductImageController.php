<?php
// File: controllers/ProductImageController.php

$product_id = $_REQUEST['product_id'] ?? 0;
$redirect_url = "Location: index.php?controller=product&action=edit&id=" . $product_id;

switch ($action) {
    // ========== AJAX UPLOAD IMAGE ==========
    case 'ajax_store':
        header('Content-Type: application/json');
        try {
            if (!isset($_FILES['image_url'])) {
                throw new Exception('Không có file được upload.');
            }

            $uploaded_images = [];
            $target_dir = "uploads/images/";

            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            // ✅ Nếu chỉ upload 1 file (giữ tương thích cũ)
            if (!is_array($_FILES['image_url']['name'])) {
                $_FILES['image_url']['name'] = [$_FILES['image_url']['name']];
                $_FILES['image_url']['type'] = [$_FILES['image_url']['type']];
                $_FILES['image_url']['tmp_name'] = [$_FILES['image_url']['tmp_name']];
                $_FILES['image_url']['error'] = [$_FILES['image_url']['error']];
                $_FILES['image_url']['size'] = [$_FILES['image_url']['size']];
            }

            foreach ($_FILES['image_url']['name'] as $index => $name) {
                if ($_FILES['image_url']['error'][$index] !== 0) continue;

                // Validate kích thước
                if ($_FILES['image_url']['size'][$index] > 5 * 1024 * 1024) {
                    continue; // bỏ qua file quá lớn
                }

                // Validate loại file
                $file_type = mime_content_type($_FILES['image_url']['tmp_name'][$index]);
                $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!in_array($file_type, $allowed_types)) continue;

                // Upload
                $file_extension = pathinfo($name, PATHINFO_EXTENSION);
                $new_file_name = uniqid() . '.' . $file_extension;
                $target_file = $target_dir . $new_file_name;

                if (move_uploaded_file($_FILES['image_url']['tmp_name'][$index], $target_file)) {
                    if ($imageModel->addImage($product_id, $target_file)) {
                        $image_id = $db->lastInsertId();

                        $uploaded_images[] = [
                            'id' => $image_id,
                            'image_url' => $target_file,
                            'image_html' => '
                            <div class="col-md-3 image-item fade-in" id="image-' . $image_id . '">
                                <div class="card shadow-sm">
                                    <img src="' . htmlspecialchars($target_file) . '" 
                                         class="card-img-top" 
                                         alt="Product Image"
                                         style="height: 200px; object-fit: cover;">
                                    <div class="card-body p-2">
                                        <button type="button" 
                                                class="btn btn-danger-modern btn-sm w-100 btn-delete-image" 
                                                data-id="' . $image_id . '">
                                            <i class="fas fa-trash"></i> Xóa
                                        </button>
                                    </div>
                                </div>
                            </div>'
                        ];
                    }
                }
            }

            if (empty($uploaded_images)) {
                throw new Exception('Không có ảnh hợp lệ được upload.');
            }

            echo json_encode([
                'success' => true,
                'message' => 'Upload thành công ' . count($uploaded_images) . ' ảnh!',
                'images' => $uploaded_images
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;

        // ========== AJAX DELETE IMAGE ==========
    case 'ajax_delete':
        header('Content-Type: application/json');

        try {
            $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

            if ($id === 0) {
                throw new Exception('ID không hợp lệ');
            }

            // Get image info before deleting
            $stmt = $db->prepare("SELECT image_url FROM product_images WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $image = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$image) {
                throw new Exception('Ảnh không tồn tại');
            }

            // Delete from database (Model deleteByID already handles file deletion)
            if ($imageModel->deleteByID($id)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Xóa ảnh thành công!'
                ]);
            } else {
                throw new Exception('Xóa ảnh thất bại');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;

        // ========== AJAX GET ALL IMAGES ==========
    case 'ajax_list':
        header('Content-Type: application/json');

        try {
            $images = $imageModel->getByProductID($product_id);

            $images_html = '';
            if (!empty($images)) {
                foreach ($images as $img) {
                    $images_html .= '
                        <div class="col-md-3 image-item" id="image-' . $img['id'] . '">
                            <div class="card shadow-sm">
                                <img src="' . htmlspecialchars($img['image_url']) . '" 
                                     class="card-img-top" 
                                     alt="Product Image"
                                     style="height: 200px; object-fit: cover;">
                                <div class="card-body p-2">
                                    <button type="button" 
                                            class="btn btn-danger-modern btn-sm w-100 btn-delete-image" 
                                            data-id="' . $img['id'] . '">
                                        <i class="fas fa-trash"></i> Xóa
                                    </button>
                                </div>
                            </div>
                        </div>
                    ';
                }
            }

            echo json_encode([
                'success' => true,
                'images_html' => $images_html,
                'total_images' => count($images)
            ]);
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
        if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] == 0) {
            $target_dir = "uploads/images/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $file_extension = pathinfo($_FILES["image_url"]["name"], PATHINFO_EXTENSION);
            $new_file_name = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_file_name;

            if (move_uploaded_file($_FILES["image_url"]["tmp_name"], $target_file)) {
                if ($imageModel->addImage($product_id, $target_file)) {
                    $_SESSION['success_message'] = "Thêm ảnh thành công!";
                } else {
                    $_SESSION['error_message'] = "Lỗi khi lưu vào CSDL.";
                }
            } else {
                $_SESSION['error_message'] = "Lỗi khi upload file.";
            }
        } else {
            $_SESSION['error_message'] = "Không có file được chọn hoặc có lỗi xảy ra.";
        }
        header($redirect_url);
        break;

    case 'delete':
        $id = (int)($_GET['id'] ?? 0);

        if ($imageModel->deleteByID($id)) {
            $_SESSION['success_message'] = "Xóa ảnh thành công!";
        } else {
            $_SESSION['error_message'] = "Xóa ảnh thất bại.";
        }
        header($redirect_url);
        break;

    default:
        header($redirect_url);
        break;
}
