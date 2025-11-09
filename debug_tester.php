<?php
// File: debug_variant.php
// Chạy file này để test logic kiểm tra variant tồn tại
// URL: http://localhost/your-project/debug_variant.php

require_once 'config/database.php';

// Khởi tạo kết nối database
$database = new Database();
$db = $database->getConnection();

// ===== THAY ĐỔI CÁC GIÁ TRỊ NÀY ĐỂ TEST =====
$product_id = 1;      // ID sản phẩm bạn đang test
$color = 'Trắng';     // Màu sắc
$storage = '128GB';   // Dung lượng
// ==============================================

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Variant Check</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            padding: 20px; 
            background: #f5f5f5;
            max-width: 1200px;
            margin: 0 auto;
        }
        .container { 
            background: white; 
            padding: 30px; 
            border-radius: 10px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 { 
            color: #2563eb; 
            border-bottom: 3px solid #2563eb; 
            padding-bottom: 10px;
        }
        h3 { 
            color: #1e40af; 
            margin-top: 30px;
            background: #eff6ff;
            padding: 10px;
            border-left: 4px solid #2563eb;
        }
        .info-box { 
            background: #f0f9ff; 
            padding: 15px; 
            border-radius: 8px; 
            border-left: 4px solid #0ea5e9;
            margin: 20px 0;
        }
        .success { 
            background: #f0fdf4; 
            color: #166534; 
            padding: 15px; 
            border-radius: 8px;
            border-left: 4px solid #22c55e;
            font-weight: bold;
            margin: 20px 0;
        }
        .error { 
            background: #fef2f2; 
            color: #991b1b; 
            padding: 15px; 
            border-radius: 8px;
            border-left: 4px solid #ef4444;
            font-weight: bold;
            margin: 20px 0;
        }
        .warning { 
            background: #fefce8; 
            color: #854d0e; 
            padding: 15px; 
            border-radius: 8px;
            border-left: 4px solid #eab308;
            margin: 20px 0;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 15px 0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        th { 
            background: #2563eb; 
            color: white; 
            padding: 12px; 
            text-align: left;
            font-weight: 600;
        }
        td { 
            padding: 10px; 
            border-bottom: 1px solid #e5e7eb;
        }
        tr:hover { 
            background: #f9fafb; 
        }
        .highlight { 
            background: #fef3c7 !important; 
            font-weight: bold;
        }
        pre { 
            background: #1e293b; 
            color: #e2e8f0; 
            padding: 15px; 
            border-radius: 8px; 
            overflow-x: auto;
            font-size: 13px;
        }
        .badge { 
            display: inline-block; 
            padding: 4px 12px; 
            border-radius: 12px; 
            font-size: 12px; 
            font-weight: 600;
        }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-info { background: #dbeafe; color: #1e40af; }
        hr { border: none; border-top: 2px solid #e5e7eb; margin: 30px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h2>🔍 Debug Variant Check</h2>
        
        <div class="info-box">
            <strong>📋 Thông tin kiểm tra:</strong><br>
            <strong>Product ID:</strong> <?= $product_id ?><br>
            <strong>Màu sắc:</strong> <?= $color ?><br>
            <strong>Dung lượng:</strong> <?= $storage ?>
        </div>

        <?php
        // Test 1: Check exact match
        echo "<h3>Test 1: Kiểm tra khớp chính xác (Exact Match)</h3>";
        echo "<p><em>Query: WHERE product_id = X AND color = 'Y' AND storage = 'Z'</em></p>";
        
        $sql1 = "SELECT * FROM product_variants 
                 WHERE product_id = :product_id 
                 AND color = :color 
                 AND storage = :storage";
        
        try {
            $stmt1 = $db->prepare($sql1);
            $stmt1->execute([
                ':product_id' => $product_id,
                ':color' => $color,
                ':storage' => $storage
            ]);
            
            $result1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($result1) > 0) {
                echo "<div class='error'>❌ Tìm thấy <strong>" . count($result1) . "</strong> biến thể trùng khớp!</div>";
                echo "<pre>" . print_r($result1, true) . "</pre>";
            } else {
                echo "<div class='success'>✅ Không tìm thấy biến thể trùng (exact match)</div>";
            }
        } catch (PDOException $e) {
            echo "<div class='error'>❌ Lỗi: " . $e->getMessage() . "</div>";
        }

        // Test 2: Check case-insensitive
        echo "<h3>Test 2: Kiểm tra không phân biệt hoa/thường (Case-Insensitive)</h3>";
        echo "<p><em>Query: WHERE LOWER(TRIM(color)) = LOWER(TRIM('Y'))</em></p>";
        
        $sql2 = "SELECT * FROM product_variants 
                 WHERE product_id = :product_id 
                 AND LOWER(TRIM(color)) = LOWER(TRIM(:color))
                 AND TRIM(storage) = TRIM(:storage)";
        
        try {
            $stmt2 = $db->prepare($sql2);
            $stmt2->execute([
                ':product_id' => $product_id,
                ':color' => $color,
                ':storage' => $storage
            ]);
            
            $result2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($result2) > 0) {
                echo "<div class='error'>❌ Tìm thấy <strong>" . count($result2) . "</strong> biến thể trùng khớp!</div>";
                echo "<pre>" . print_r($result2, true) . "</pre>";
            } else {
                echo "<div class='success'>✅ Không tìm thấy biến thể trùng (case-insensitive)</div>";
            }
        } catch (PDOException $e) {
            echo "<div class='error'>❌ Lỗi: " . $e->getMessage() . "</div>";
        }

        // Test 3: Check with space normalization
        echo "<h3>Test 3: Kiểm tra loại bỏ khoảng trắng (Space Normalization)</h3>";
        echo "<p><em>Query: WHERE REPLACE(storage, ' ', '') = REPLACE('Y', ' ', '')</em></p>";
        
        $sql3 = "SELECT * FROM product_variants 
                 WHERE product_id = :product_id 
                 AND LOWER(TRIM(color)) = LOWER(TRIM(:color))
                 AND TRIM(REPLACE(storage, ' ', '')) = TRIM(REPLACE(:storage, ' ', ''))";
        
        try {
            $stmt3 = $db->prepare($sql3);
            $stmt3->execute([
                ':product_id' => $product_id,
                ':color' => $color,
                ':storage' => $storage
            ]);
            
            $result3 = $stmt3->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($result3) > 0) {
                echo "<div class='error'>❌ Tìm thấy <strong>" . count($result3) . "</strong> biến thể trùng khớp!</div>";
                echo "<pre>" . print_r($result3, true) . "</pre>";
            } else {
                echo "<div class='success'>✅ Không tìm thấy biến thể trùng (with space normalization)</div>";
            }
        } catch (PDOException $e) {
            echo "<div class='error'>❌ Lỗi: " . $e->getMessage() . "</div>";
        }

        // Test 4: Show all variants for this product
        echo "<h3>Test 4: Tất cả biến thể của sản phẩm (Product ID: {$product_id})</h3>";
        
        $sql4 = "SELECT id, sku, color, storage, price, stock FROM product_variants 
                 WHERE product_id = :product_id 
                 ORDER BY id DESC";
        
        try {
            $stmt4 = $db->prepare($sql4);
            $stmt4->execute([':product_id' => $product_id]);
            
            $all_variants = $stmt4->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($all_variants) > 0) {
                echo "<p><span class='badge badge-info'>Tổng số: " . count($all_variants) . " biến thể</span></p>";
                echo "<table>";
                echo "<tr>
                        <th>ID</th>
                        <th>SKU</th>
                        <th>Màu sắc</th>
                        <th>Dung lượng</th>
                        <th>Giá</th>
                        <th>Tồn kho</th>
                        <th>Trạng thái</th>
                      </tr>";
                
                foreach ($all_variants as $v) {
                    // Highlight nếu trùng với giá trị test
                    $highlight = (
                        strtolower(trim($v['color'])) == strtolower(trim($color)) && 
                        str_replace(' ', '', trim($v['storage'])) == str_replace(' ', '', trim($storage))
                    ) ? "highlight" : "";
                    
                    $statusBadge = $highlight ? "<span class='badge badge-danger'>⚠️ TRÙNG</span>" : "<span class='badge badge-success'>✓ OK</span>";
                    
                    echo "<tr class='{$highlight}'>";
                    echo "<td>{$v['id']}</td>";
                    echo "<td><code>{$v['sku']}</code></td>";
                    echo "<td>{$v['color']}</td>";
                    echo "<td>{$v['storage']}</td>";
                    echo "<td>" . number_format($v['price'], 0, ',', '.') . " đ</td>";
                    echo "<td>{$v['stock']}</td>";
                    echo "<td>{$statusBadge}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<div class='warning'>⚠️ Chưa có biến thể nào cho sản phẩm này</div>";
            }
        } catch (PDOException $e) {
            echo "<div class='error'>❌ Lỗi: " . $e->getMessage() . "</div>";
        }

        // Test 5: Check table structure
        echo "<h3>Test 5: Cấu trúc bảng product_variants</h3>";
        
        try {
            $sql5 = "DESCRIBE product_variants";
            $stmt5 = $db->query($sql5);
            $columns = $stmt5->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<table>";
            echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
            foreach ($columns as $col) {
                echo "<tr>";
                echo "<td><strong>{$col['Field']}</strong></td>";
                echo "<td>{$col['Type']}</td>";
                echo "<td>{$col['Null']}</td>";
                echo "<td>{$col['Key']}</td>";
                echo "<td>" . ($col['Default'] ?: '<em>NULL</em>') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } catch (PDOException $e) {
            echo "<div class='error'>❌ Lỗi: " . $e->getMessage() . "</div>";
        }

        // Final conclusion
        echo "<hr>";
        echo "<h2>🎯 KẾT LUẬN</h2>";
        
        if (count($result3) > 0) {
            echo "<div class='error'>";
            echo "<h3>❌ BIẾN THỂ ĐÃ TỒN TẠI!</h3>";
            echo "<p><strong>Không nên cho phép thêm mới biến thể này.</strong></p>";
            echo "<ul>";
            echo "<li>ID tồn tại: <strong>{$result3[0]['id']}</strong></li>";
            echo "<li>SKU: <code>{$result3[0]['sku']}</code></li>";
            echo "<li>Màu sắc: {$result3[0]['color']}</li>";
            echo "<li>Dung lượng: {$result3[0]['storage']}</li>";
            echo "</ul>";
            echo "<p><strong>👉 Action:</strong> Logic kiểm tra trong Controller PHẢI ngăn chặn việc thêm mới!</p>";
            echo "</div>";
        } else {
            echo "<div class='success'>";
            echo "<h3>✅ BIẾN THỂ CHƯA TỒN TẠI</h3>";
            echo "<p><strong>Có thể thêm mới biến thể này.</strong></p>";
            echo "<p>Màu sắc: <strong>{$color}</strong> | Dung lượng: <strong>{$storage}</strong></p>";
            echo "</div>";
        }

        // Suggestions
        echo "<div class='warning'>";
        echo "<h3>💡 GỢI Ý KHẮC PHỤC</h3>";
        echo "<p><strong>Nếu biến thể đã tồn tại nhưng vẫn thêm được vào database:</strong></p>";
        echo "<ol>";
        echo "<li>Kiểm tra file <code>controllers/ProductVariantController.php</code> trong case <code>'ajax_store'</code></li>";
        echo "<li>Đảm bảo có <code>exit;</code> sau khi return JSON error</li>";
        echo "<li>Kiểm tra có đoạn code nào bypass logic check không</li>";
        echo "<li>Xóa cache trình duyệt (Ctrl+Shift+R hoặc Ctrl+F5)</li>";
        echo "<li>Kiểm tra có file nào khác xử lý request không (routes, middleware)</li>";
        echo "</ol>";
        echo "</div>";
        ?>
        
        <hr>
        <p style="text-align: center; color: #6b7280; margin-top: 30px;">
            <small>Debug completed at <?= date('Y-m-d H:i:s') ?></small>
        </p>
    </div>
</body>
</html>