<?php
// File: controllers/ProductController.php

switch ($action) {
    // ========== AJAX LIST ==========
    case 'ajax_list':
        header('Content-Type: application/json');

        try {
            $search = $_GET['search'] ?? '';
            $min_price = (int)($_GET['min_price'] ?? 0);
            $max_price = (int)($_GET['max_price'] ?? 0);
            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = 10;
            $offset = ($page - 1) * $limit;

            // Get total count
            $total_records = $productModel->countAll($search, $min_price, $max_price);
            $total_pages = ceil($total_records / $limit);

            // Get products
            $products = $productModel->getAll($search, $min_price, $max_price, $limit, $offset);

            // Generate table HTML
            $table_html = '';
            if ($products->rowCount() > 0) {
                while ($row = $products->fetch(PDO::FETCH_ASSOC)) {
                    $image = !empty($row['image'])
                        ? '<img src="' . htmlspecialchars($row['image']) . '" alt="' . htmlspecialchars($row['name']) . '" class="product-thumbnail">'
                        : '<div class="product-thumbnail bg-light d-flex align-items-center justify-content-center"><i class="fas fa-image text-muted"></i></div>';

                    $minPrice = number_format($row['min_price'] ?? 0);
                    $maxPrice = number_format($row['max_price'] ?? 0);
                    $totalStock = intval($row['total_stock'] ?? 0);
                    $createdDate = date('d/m/Y', strtotime($row['created_at']));

                    $table_html .= '
                        <tr class="fade-in">
                            <td>' . $image . '</td>
                            <td><span class="product-sku">' . htmlspecialchars($row['sku']) . '</span></td>
                            <td><span class="product-name">' . htmlspecialchars($row['name']) . '</span></td>
                            <td><span class="price-badge">' . $minPrice . ' - ' . $maxPrice . ' đ</span></td>
                            <td><span class="stock-badge">' . $totalStock . '</span></td>
                            <td>' . $createdDate . '</td>
                            <td>
                                <div class="action-buttons">
                                    <a href="index.php?controller=product&action=edit&id=' . $row['id'] . '" 
                                       class="btn btn-warning-modern btn-action">
                                        <i class="fas fa-edit"></i> Sửa
                                    </a>
                                    <button type="button"
                                            class="btn btn-danger-modern btn-action btn-delete-product"
                                            data-id="' . $row['id'] . '"
                                            data-name="' . htmlspecialchars($row['name']) . '">
                                        <i class="fas fa-trash"></i> Xóa
                                    </button>
                                </div>
                            </td>
                        </tr>
                    ';
                }
            } else {
                $table_html = '
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="empty-state">
                                <i class="fas fa-box-open"></i>
                                <h5>Không tìm thấy sản phẩm</h5>
                                <p>Thử thay đổi bộ lọc hoặc thêm sản phẩm mới</p>
                            </div>
                        </td>
                    </tr>
                ';
            }

            // Generate pagination HTML
            $pagination_html = '';
            if ($total_pages > 1) {
                $pagination_html = '<ul class="pagination pagination-modern">';

                // Previous button
                if ($page > 1) {
                    $pagination_html .= '
                        <li class="page-item">
                            <a class="page-link" href="?page=' . ($page - 1) . '&search=' . urlencode($search) . '&min_price=' . $min_price . '&max_price=' . $max_price . '">
                                <i class="fas fa-chevron-left"></i> Trước
                            </a>
                        </li>
                    ';
                }

                // Page numbers
                $start = max(1, $page - 2);
                $end = min($total_pages, $page + 2);

                if ($start > 1) {
                    $pagination_html .= '<li class="page-item"><a class="page-link" href="?page=1&search=' . urlencode($search) . '&min_price=' . $min_price . '&max_price=' . $max_price . '">1</a></li>';
                    if ($start > 2) {
                        $pagination_html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                }

                for ($i = $start; $i <= $end; $i++) {
                    $active = ($i == $page) ? 'active' : '';
                    $pagination_html .= '
                        <li class="page-item ' . $active . '">
                            <a class="page-link" href="?page=' . $i . '&search=' . urlencode($search) . '&min_price=' . $min_price . '&max_price=' . $max_price . '">' . $i . '</a>
                        </li>
                    ';
                }

                if ($end < $total_pages) {
                    if ($end < $total_pages - 1) {
                        $pagination_html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                    $pagination_html .= '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . '&search=' . urlencode($search) . '&min_price=' . $min_price . '&max_price=' . $max_price . '">' . $total_pages . '</a></li>';
                }

                // Next button
                if ($page < $total_pages) {
                    $pagination_html .= '
                        <li class="page-item">
                            <a class="page-link" href="?page=' . ($page + 1) . '&search=' . urlencode($search) . '&min_price=' . $min_price . '&max_price=' . $max_price . '">
                                Sau <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    ';
                }

                $pagination_html .= '</ul>';
            }

            echo json_encode([
                'success' => true,
                'table_html' => $table_html,
                'pagination_html' => $pagination_html,
                'total_products' => $total_records,
                'current_page' => $page,
                'total_pages' => $total_pages
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ]);
        }
        exit;

        // ========== AJAX DELETE ==========
    case 'ajax_delete':
        header('Content-Type: application/json');

        try {
            $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

            if ($id === 0) {
                throw new Exception('ID không hợp lệ');
            }

            $product = $productModel->getByID($id);
            if (!$product) {
                throw new Exception('Sản phẩm không tồn tại');
            }

            $db->beginTransaction();

            // 1. Xóa tất cả hình ảnh
            $images = $imageModel->getByProductID($id);
            foreach ($images as $img) {
                $imageModel->deleteByID($img['id']);
            }

            // 2. Xóa tất cả biến thể
            $variants = $variantModel->getByProductID($id);
            foreach ($variants as $var) {
                $variantModel->delete($var['id']);
            }

            // 3. Xóa ảnh đại diện
            if (!empty($product['image']) && file_exists($product['image'])) {
                unlink($product['image']);
            }

            // 4. Xóa sản phẩm
            if ($productModel->delete($id)) {
                $db->commit();
                echo json_encode([
                    'success' => true,
                    'message' => 'Đã xóa sản phẩm "' . $product['name'] . '" thành công!'
                ]);
            } else {
                throw new Exception('Không thể xóa sản phẩm');
            }
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ]);
        }
        exit;

        // ========== NORMAL ACTIONS ==========
    case 'index':
        $search = $_GET['search'] ?? '';
        $min_price = (int)($_GET['min_price'] ?? 0);
        $max_price = (int)($_GET['max_price'] ?? 0);
        $page = (int)($_GET['page'] ?? 1);
        $limit = 10;

        $total_records = $productModel->countAll($search, $min_price, $max_price);
        $total_pages = ceil($total_records / $limit);
        $offset = ($page - 1) * $limit;

        $products = $productModel->getAll($search, $min_price, $max_price, $limit, $offset);

        $page_title = "Quản lý Sản phẩm";
        require 'views/layouts/header.php';
        require 'views/products/index.php';
        require 'views/layouts/footer.php';
        break;

    case 'create':
        $page_title = "Thêm Sản phẩm mới";
        require 'views/layouts/header.php';
        require 'views/products/create.php';
        require 'views/layouts/footer.php';
        break;

    case 'store':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $error = [];
            $name = sanitizeString($_POST['name'] ?? '');
            $description = sanitizeString($_POST['description'] ?? '');

            if (empty($name)) {
                $error[] = "Tên sản phẩm không được để trống";
            }
            if (strlen($name) > 100) {
                $error[] = "Tên sản phẩm không được vượt quá 100 ký tự";
            }

            $image_url = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
                    $error[] = "Ảnh sản phẩm không được vượt quá 2MB";
                } else {
                    $target_dir = "uploads/images/";
                    $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
                    $new_file_name = uniqid() . '.' . $file_extension;
                    $target_file = $target_dir . $new_file_name;

                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                        $image_url = $target_file;
                    } else {
                        $error[] = "Upload ảnh thất bại.";
                    }
                }
            }

            if (empty($error)) {
                $base_sku = generateAcronymSKU($name);
                $sku = $base_sku;
                $counter = 1;
                while ($productModel->findBySKU($sku)) {
                    $sku = $base_sku . '-' . $counter;
                    $counter++;
                }

                $data = [
                    'sku' => $sku,
                    'name' => $name,
                    'description' => $description,
                    'image' => $image_url
                ];

                if ($productModel->create($data)) {
                    $_SESSION['success_message'] = "Tạo sản phẩm thành công!";
                    $new_id = $db->lastInsertId();
                    header("Location: index.php?controller=product&action=edit&id=" . $new_id);
                    exit;
                } else {
                    $_SESSION['error_message'] = "Tạo sản phẩm thất bại do lỗi CSDL.";
                    header("Location: index.php?controller=product&action=create");
                    exit;
                }
            }

            $_SESSION['error_message'] = implode("<br>", $error);
            $_SESSION['old_input'] = $_POST;
            header("Location: index.php?controller=product&action=create");
            exit;
        }
        break;

    case 'edit':
        $id = (int)($_GET['id'] ?? 0);
        if ($id === 0) {
            die('ID không hợp lệ.');
        }

        $product = $productModel->getByID($id);
        $variants = $variantModel->getByProductID($id);
        $images = $imageModel->getByProductID($id);

        $page_title = "Chỉnh sửa: " . htmlspecialchars($product['name']);
        require 'views/layouts/header.php';
        require 'views/products/edit.php';
        require 'views/layouts/footer.php';
        break;

    case 'update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = [];
            $id = (int)($_POST['id'] ?? 0);
            $product = $productModel->getByID($id);
            if (!$product) {
                die('Sản phẩm không tồn tại');
            }

            $name = sanitizeString($_POST['name'] ?? '');
            $description = sanitizeString($_POST['description'] ?? '');

            if (empty($name)) {
                $errors[] = "Tên sản phẩm là bắt buộc.";
            }

            $image_url = $product['image'];
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                if (!empty($image_url) && file_exists($image_url)) {
                    unlink($image_url);
                }
                $target_dir = "uploads/images/";
                $target_file = $target_dir . basename($_FILES["image"]["name"]);
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $image_url = $target_file;
                }
            }

            if (empty($errors)) {
                $data = [
                    'id' => $id,
                    'sku' => $product['sku'],
                    'name' => $name,
                    'description' => $description,
                    'image' => $image_url
                ];

                if ($productModel->update($data)) {
                    $_SESSION['success_message'] = "Cập nhật thành công";
                } else {
                    $errors[] = "Cập nhật sản phẩm thất bại";
                }
            }
            if (!empty($errors)) {
                $_SESSION['error_message'] = implode("<br>", $errors);
            }

            header("Location: index.php?controller=product&action=edit&id=" . $id);
            exit();
        }
        break;

    case 'delete':
        $id = (int)($_GET['id'] ?? 0);

        $images = $imageModel->getByProductID($id);
        foreach ($images as $img) {
            $imageModel->deleteByID($img['id']);
        }

        $variants = $variantModel->getByProductID($id);
        foreach ($variants as $var) {
            $variantModel->delete($var['id']);
        }

        $product = $productModel->getByID($id);
        if ($product && !empty($product['image']) && file_exists($product['image'])) {
            unlink($product['image']);
        }

        if ($productModel->delete($id)) {
            $_SESSION['success_message'] = "Xóa sản phẩm thành công!";
        } else {
            $_SESSION['error_message'] = "Xóa sản phẩm thất bại.";
        }
        header("Location: index.php?controller=product&action=index");
        break;

    case 'import':
        $page_title = "Import Sản phẩm từ Excel";
        require 'views/layouts/header.php';
        require 'views/products/import.php';
        require 'views/layouts/footer.php';
        break;

    case 'import_process':
        if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] != 0) {
            $_SESSION['error_message'] = "Vui lòng chọn file Excel để import.";
            header("Location: index.php?controller=product&action=import");
            exit;
        }

        require 'vendor/autoload.php';

        $file_tmp_path = $_FILES['excel_file']['tmp_name'];

        // Get options
        $update_existing = isset($_POST['update_existing']);
        $skip_errors = isset($_POST['skip_errors']);
        $create_log = isset($_POST['create_log']);

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_tmp_path);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Statistics
            $stats = [
                'total_rows' => count($rows) - 1, // Trừ header
                'products_created' => 0,
                'products_updated' => 0,
                'variants_created' => 0,
                'variants_updated' => 0,
                'errors' => [],
                'skipped_rows' => 0
            ];

            // Remove header row
            array_shift($rows);

            // Start transaction
            $db->beginTransaction();

            foreach ($rows as $index => $row) {
                $row_number = $index + 2; // +2 because: +1 for array index, +1 for header row

                try {
                    // Validate required fields
                    $product_sku = trim($row[0] ?? '');
                    $product_name = trim($row[1] ?? '');
                    $product_desc = trim($row[2] ?? '');
                    $variant_color = trim($row[3] ?? '');
                    $variant_storage = trim($row[4] ?? '');
                    $variant_price = floatval($row[5] ?? 0);
                    $variant_stock = intval($row[6] ?? 0);

                    // Skip empty rows
                    if (empty($product_sku) && empty($product_name)) {
                        continue;
                    }

                    // Validate required fields
                    if (empty($product_sku)) {
                        throw new Exception("Product SKU không được để trống");
                    }
                    if (empty($product_name)) {
                        throw new Exception("Product Name không được để trống");
                    }
                    if (empty($variant_color)) {
                        throw new Exception("Variant Color không được để trống");
                    }
                    if (empty($variant_storage)) {
                        throw new Exception("Variant Storage không được để trống");
                    }
                    if ($variant_price <= 0) {
                        throw new Exception("Variant Price phải lớn hơn 0");
                    }
                    if ($variant_stock < 0) {
                        throw new Exception("Variant Stock phải >= 0");
                    }

                    // 1. Find or Create Product
                    $product_id = null;
                    $product = $productModel->findBySKU($product_sku);

                    if ($product) {
                        $product_id = $product['id'];

                        // Update product if needed and option enabled
                        if (
                            $update_existing &&
                            ($product['name'] != $product_name || $product['description'] != $product_desc)
                        ) {
                            $productModel->update([
                                'id' => $product_id,
                                'sku' => $product_sku,
                                'name' => $product_name,
                                'description' => $product_desc,
                                'image' => $product['image']
                            ]);
                            $stats['products_updated']++;
                        }
                    } else {
                        // Create new product
                        $productData = [
                            'sku' => $product_sku,
                            'name' => $product_name,
                            'description' => $product_desc,
                            'image' => ''
                        ];

                        if ($productModel->create($productData)) {
                            $product_id = $db->lastInsertId();
                            $stats['products_created']++;
                        } else {
                            throw new Exception("Không thể tạo sản phẩm");
                        }
                    }

                    // 2. Generate variant SKU
                    $color_slug = ucfirst(strtolower($variant_color));
                    preg_match('/(\d+)/', $variant_storage, $matches);
                    $storage_slug = $matches[1] ?? str_replace(' ', '', $variant_storage);
                    $generated_sku = $product_sku . '-' . $color_slug . '-' . $storage_slug;

                    // 3. Find or Create Variant
                    $variant = $variantModel->findBySKU($generated_sku);

                    $variantData = [
                        'product_id' => $product_id,
                        'sku' => $generated_sku,
                        'color' => $variant_color,
                        'storage' => $variant_storage,
                        'price' => $variant_price,
                        'stock' => $variant_stock,
                        'image' => ''
                    ];

                    if ($variant) {
                        if ($update_existing) {
                            $variantData['id'] = $variant['id'];
                            $variantData['image'] = $variant['image']; // Keep existing image

                            if ($variantModel->update($variantData)) {
                                $stats['variants_updated']++;
                            } else {
                                throw new Exception("Không thể cập nhật biến thể");
                            }
                        } else {
                            // Skip if exists and update not enabled
                            $stats['errors'][] = [
                                'row' => $row_number,
                                'message' => "Biến thể {$generated_sku} đã tồn tại (bỏ qua)"
                            ];
                            $stats['skipped_rows']++;
                        }
                    } else {
                        if ($variantModel->create($variantData)) {
                            $stats['variants_created']++;
                        } else {
                            throw new Exception("Không thể tạo biến thể");
                        }
                    }
                } catch (Exception $e) {
                    $stats['errors'][] = [
                        'row' => $row_number,
                        'message' => $e->getMessage(),
                        'data' => [
                            'sku' => $product_sku ?? 'N/A',
                            'name' => $product_name ?? 'N/A'
                        ]
                    ];

                    $stats['skipped_rows']++;

                    // If skip_errors is not enabled, rollback and stop
                    if (!$skip_errors) {
                        $db->rollBack();
                        $_SESSION['error_message'] = "Import thất bại tại dòng {$row_number}: " . $e->getMessage();
                        header("Location: index.php?controller=product&action=import");
                        exit;
                    }
                }
            }

            // Commit transaction
            $db->commit();

            // Create log file if requested
            if ($create_log && !empty($stats['errors'])) {
                $log_dir = 'logs/imports/';
                if (!is_dir($log_dir)) {
                    mkdir($log_dir, 0777, true);
                }

                $log_filename = $log_dir . 'import_' . date('Y-m-d_His') . '.log';
                $log_content = "IMPORT LOG - " . date('Y-m-d H:i:s') . "\n";
                $log_content .= str_repeat("=", 60) . "\n\n";
                $log_content .= "THỐNG KÊ:\n";
                $log_content .= "- Tổng số dòng: {$stats['total_rows']}\n";
                $log_content .= "- Sản phẩm mới: {$stats['products_created']}\n";
                $log_content .= "- Sản phẩm cập nhật: {$stats['products_updated']}\n";
                $log_content .= "- Biến thể mới: {$stats['variants_created']}\n";
                $log_content .= "- Biến thể cập nhật: {$stats['variants_updated']}\n";
                $log_content .= "- Dòng bỏ qua: {$stats['skipped_rows']}\n";
                $log_content .= "- Lỗi: " . count($stats['errors']) . "\n\n";

                if (!empty($stats['errors'])) {
                    $log_content .= "CHI TIẾT LỖI:\n";
                    $log_content .= str_repeat("-", 60) . "\n";
                    foreach ($stats['errors'] as $error) {
                        $log_content .= "Dòng {$error['row']}: {$error['message']}\n";
                        if (isset($error['data'])) {
                            $log_content .= "  SKU: {$error['data']['sku']}, Tên: {$error['data']['name']}\n";
                        }
                        $log_content .= "\n";
                    }
                }

                file_put_contents($log_filename, $log_content);
            }

            // Build success message
            $success_msg = "Import hoàn tất! ";
            $success_msg .= "Tạo mới: {$stats['products_created']} sản phẩm, {$stats['variants_created']} biến thể. ";

            if ($stats['products_updated'] > 0 || $stats['variants_updated'] > 0) {
                $success_msg .= "Cập nhật: {$stats['products_updated']} sản phẩm, {$stats['variants_updated']} biến thể. ";
            }

            if ($stats['skipped_rows'] > 0) {
                $success_msg .= "Bỏ qua: {$stats['skipped_rows']} dòng. ";
            }

            if ($create_log && !empty($stats['errors'])) {
                $success_msg .= "File log: {$log_filename}";
            }

            $_SESSION['success_message'] = $success_msg;

            // Store detailed stats in session for display
            $_SESSION['import_stats'] = $stats;
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $_SESSION['error_message'] = "Import thất bại: " . $e->getMessage();
        }

        header("Location: index.php?controller=product&action=import_result");
        break;

    case 'import_result':
        // Display import results
        $stats = $_SESSION['import_stats'] ?? null;
        $page_title = "Kết quả Import";

        require 'views/layouts/header.php';
        require 'views/products/import_result.php';
        require 'views/layouts/footer.php';

        // Clear stats from session
        unset($_SESSION['import_stats']);
        break;

    case 'export_template':
        require 'vendor/autoload.php';

        $format = $_GET['format'] ?? 'xlsx';

        // Create new Spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers with styling
        $headers = [
            'Product SKU',
            'Product Name',
            'Product Description',
            'Variant Color',
            'Variant Storage',
            'Variant Price',
            'Variant Stock'
        ];

        $sheet->fromArray($headers, NULL, 'A1');

        // Style header row
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2563eb']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ]
        ];
        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);

        // Add sample data
        $sampleData = [
            ['IP16PRM', 'iPhone 16 Pro Max', 'Flagship model with advanced features', 'Black', '256GB', 29990000, 50],
            ['IP16PRM', 'iPhone 16 Pro Max', 'Flagship model with advanced features', 'White', '256GB', 29990000, 45],
            ['IP16PRM', 'iPhone 16 Pro Max', 'Flagship model with advanced features', 'Black', '512GB', 34990000, 30],
            ['IP16', 'iPhone 16', 'Standard model with great performance', 'Blue', '128GB', 22990000, 100],
            ['IP16', 'iPhone 16', 'Standard model with great performance', 'Pink', '128GB', 22990000, 80],
            ['IP16', 'iPhone 16', 'Standard model with great performance', 'Blue', '256GB', 25990000, 60],
            ['SSGLS24U', 'Samsung Galaxy S24 Ultra', 'Premium Android flagship', 'Titanium Gray', '256GB', 31990000, 40],
            ['SSGLS24U', 'Samsung Galaxy S24 Ultra', 'Premium Android flagship', 'Titanium Black', '512GB', 36990000, 35],
            ['SSGLS24', 'Samsung Galaxy S24', 'Powerful mid-range option', 'Onyx Black', '128GB', 20990000, 75],
            ['SSGLS24', 'Samsung Galaxy S24', 'Powerful mid-range option', 'Marble Gray', '256GB', 23990000, 55]
        ];

        $sheet->fromArray($sampleData, NULL, 'A2');

        // Style data rows
        $dataStyle = [
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ]
        ];
        $sheet->getStyle('A2:G' . (count($sampleData) + 1))->applyFromArray($dataStyle);

        // Add borders
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ]
        ];
        $sheet->getStyle('A1:G' . (count($sampleData) + 1))->applyFromArray($borderStyle);

        // Auto-size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set row height
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Add data validation for Price (must be number > 0)
        $validation = $sheet->getCell('F2')->getDataValidation();
        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_DECIMAL);
        $validation->setOperator(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::OPERATOR_GREATERTHAN);
        $validation->setFormula1('0');
        $validation->setShowErrorMessage(true);
        $validation->setErrorTitle('Giá không hợp lệ');
        $validation->setError('Giá phải là số lớn hơn 0');

        // Add data validation for Stock (must be integer >= 0)
        $validation = $sheet->getCell('G2')->getDataValidation();
        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_WHOLE);
        $validation->setOperator(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::OPERATOR_GREATERTHANOREQUAL);
        $validation->setFormula1('0');
        $validation->setShowErrorMessage(true);
        $validation->setErrorTitle('Tồn kho không hợp lệ');
        $validation->setError('Tồn kho phải là số nguyên >= 0');

        // Add instructions in a separate sheet
        $instructionsSheet = $spreadsheet->createSheet(1);
        $instructionsSheet->setTitle('Hướng dẫn');

        $instructions = [
            ['HƯỚNG DẪN IMPORT SẢN PHẨM'],
            [''],
            ['1. ĐỊNH DẠNG CỘT'],
            ['Cột A: Product SKU - Mã SKU của sản phẩm cha (bắt buộc)'],
            ['Cột B: Product Name - Tên sản phẩm (bắt buộc)'],
            ['Cột C: Product Description - Mô tả sản phẩm (không bắt buộc)'],
            ['Cột D: Variant Color - Màu sắc biến thể (bắt buộc)'],
            ['Cột E: Variant Storage - Dung lượng biến thể (bắt buộc)'],
            ['Cột F: Variant Price - Giá bán (bắt buộc, phải là số > 0)'],
            ['Cột G: Variant Stock - Số lượng tồn kho (bắt buộc, phải là số >= 0)'],
            [''],
            ['2. QUY TẮC'],
            ['- Dòng đầu tiên là tiêu đề (sẽ bị bỏ qua khi import)'],
            ['- SKU sản phẩm không được trùng với sản phẩm đã có (trừ khi muốn cập nhật)'],
            ['- SKU biến thể sẽ tự động tạo theo format: {Product_SKU}-{Color}-{Storage_Number}'],
            ['  Ví dụ: IP16PRM-Black-256'],
            ['- Nếu sản phẩm đã tồn tại, hệ thống sẽ cập nhật thông tin'],
            ['- Nếu biến thể đã tồn tại, hệ thống sẽ cập nhật giá và tồn kho'],
            [''],
            ['3. LƯU Ý'],
            ['- File không được vượt quá 10MB'],
            ['- Chỉ chấp nhận định dạng .xlsx hoặc .xls'],
            ['- Giá và tồn kho phải là số'],
            ['- Các dòng có lỗi sẽ bị bỏ qua (nếu chọn tùy chọn skip errors)'],
            [''],
            ['4. VÍ DỤ'],
            ['Xem sheet "Template" để tham khảo dữ liệu mẫu']
        ];

        $instructionsSheet->fromArray($instructions, NULL, 'A1');

        // Style instructions
        $instructionsSheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => '2563eb']
            ]
        ]);

        $instructionsSheet->getStyle('A3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14]
        ]);

        $instructionsSheet->getStyle('A12')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14]
        ]);

        $instructionsSheet->getStyle('A20')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14]
        ]);

        $instructionsSheet->getStyle('A26')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14]
        ]);

        $instructionsSheet->getColumnDimension('A')->setWidth(100);

        // Set active sheet back to template
        $spreadsheet->setActiveSheetIndex(0);
        $spreadsheet->getActiveSheet()->setTitle('Template');

        // Generate filename
        $filename = 'product_import_template_' . date('Y-m-d_His');

        // Output file
        if ($format === 'csv') {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment;filename="' . $filename . '.csv"');
            header('Cache-Control: max-age=0');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
            $writer->setDelimiter(',');
            $writer->setEnclosure('"');
            $writer->setSheetIndex(0);
        } else {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        }

        $writer->save('php://output');
        exit;
        break;

    case 'export_products':
        // Export current products to Excel
        require 'vendor/autoload.php';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $headers = [
            'Product SKU',
            'Product Name',
            'Product Description',
            'Variant Color',
            'Variant Storage',
            'Variant Price',
            'Variant Stock'
        ];

        $sheet->fromArray($headers, NULL, 'A1');

        // Style header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2563eb']
            ]
        ];
        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);

        // Get all products with variants
        $sql = "
        SELECT 
            p.sku as product_sku,
            p.name as product_name,
            p.description,
            pv.color,
            pv.storage,
            pv.price,
            pv.stock
        FROM products p
        LEFT JOIN product_variants pv ON p.id = pv.product_id
        ORDER BY p.sku, pv.color, pv.storage
    ";

        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_NUM);

        if (!empty($data)) {
            $sheet->fromArray($data, NULL, 'A2');
        }

        // Auto-size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Generate filename
        $filename = 'products_export_' . date('Y-m-d_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        break;
    
    default:
        echo "404 - Action not found";
        break;
}
