🛍️ HỆ THỐNG QUẢN LÝ SẢN PHẨM (CRUD + SKU + VARIANTS + MULTI-IMAGES)
📘 1. Giới thiệu

Hệ thống quản lý sản phẩm giúp admin có thể:

Thêm, sửa, xóa và xem danh sách sản phẩm.

Quản lý biến thể sản phẩm (màu sắc, dung lượng, SKU, giá, tồn kho).

Quản lý nhiều ảnh cho từng sản phẩm.

Thao tác nhanh bằng AJAX (không tải lại trang).

Lưu trữ dữ liệu trong MySQL.

Công nghệ sử dụng:

Frontend: HTML, CSS, JavaScript, AJAX, Bootstrap.

Backend: PHP OOP.

Database: MySQL.

📂 2. Cấu trúc thư mục
product-management/
│
├── config/
│   └── db.php                 # File kết nối CSDL MySQL
│
├── views/
│   ├── index.php              # Trang danh sách sản phẩm
│   ├── add_product.php        # Form thêm sản phẩm
│   ├── edit_product.php       # Form sửa sản phẩm
│   ├── product_detail.php     # Trang chi tiết sản phẩm
│   ├── variant_manage.php     # Quản lý biến thể (màu, dung lượng)
│   ├── upload_images.php      # Upload nhiều ảnh
│
├── ajax/
│   ├── fetch_products.php     # Lấy danh sách sản phẩm (AJAX)
│   ├── save_product.php       # Lưu sản phẩm mới
│   ├── update_product.php     # Cập nhật sản phẩm
│   ├── delete_product.php     # Xóa sản phẩm
│   ├── fetch_variants.php     # Lấy biến thể của sản phẩm
│   ├── save_variant.php       # Lưu biến thể mới
│   ├── upload_image.php       # Upload ảnh chi tiết
│   ├── delete_image.php       # Xóa ảnh chi tiết
│
├── assets/
│   ├── css/
│   │   └── style.css          # CSS tổng thể
│   ├── js/
│   │   └── main.js            # Xử lý AJAX, UI, modal
│   ├── images/
│   │   ├── products/          # Ảnh sản phẩm upload
│   │   └── thumbnails/        # Ảnh thumbnail
│
├── includes/
│   ├── header.php             # Header HTML chung
│   ├── footer.php             # Footer HTML chung
│   ├── functions.php          # Hàm tiện ích (sinh SKU, định dạng giá,…)
│
├── sql/
│   └── product_db.sql         # File tạo bảng và dữ liệu mẫu
│
└── README.md                  # File mô tả (tài liệu này)

🧱 3. Cấu trúc CSDL
Bảng products
Trường	Kiểu dữ liệu	Mô tả
id	INT (PK, AI)	Khóa chính
sku	VARCHAR(20)	Mã sản phẩm duy nhất
name	VARCHAR(100)	Tên sản phẩm
description	TEXT	Mô tả chi tiết
image	VARCHAR(255)	Ảnh thumbnail
created_at	DATETIME	Ngày tạo
updated_at	DATETIME	Ngày cập nhật
Bảng product_variants
Trường	Kiểu dữ liệu	Mô tả
id	INT (PK, AI)	Khóa chính
product_id	INT (FK)	Liên kết đến products.id
sku	VARCHAR(30)	SKU riêng cho biến thể
color	VARCHAR(50)	Màu sắc
storage	VARCHAR(20)	Dung lượng (VD: 128GB)
price	DECIMAL(10,2)	Giá
stock	INT	Tồn kho
image	VARCHAR(255)	Ảnh riêng (tuỳ chọn)
Bảng product_images
Trường	Kiểu dữ liệu	Mô tả
id	INT (PK, AI)	Khóa chính
product_id	INT (FK)	Liên kết đến products.id
image_url	VARCHAR(255)	Đường dẫn ảnh
⚙️ 4. Yêu cầu chức năng chi tiết
1️⃣ Quản lý sản phẩm

Thêm mới sản phẩm (tên, mô tả, ảnh thumbnail, SKU).

Xem danh sách sản phẩm (tên, SKU, giá, ảnh, tồn kho tổng).

Cập nhật thông tin sản phẩm.

Xóa sản phẩm (xóa kèm biến thể & ảnh liên quan).

Tìm kiếm sản phẩm theo tên hoặc SKU (AJAX).

2️⃣ Quản lý biến thể

Thêm mới biến thể (màu, dung lượng, giá, tồn kho, ảnh riêng).

Sửa biến thể (cập nhật giá, SKU, màu, dung lượng,…).

Xóa biến thể cụ thể.

Hiển thị danh sách biến thể của từng sản phẩm.

3️⃣ Quản lý ảnh sản phẩm

Upload nhiều ảnh cùng lúc (AJAX, không reload).

Hiển thị ảnh dạng gallery.

Xóa từng ảnh riêng.

Kiểm tra dung lượng & định dạng file khi upload.

4️⃣ Tìm kiếm & Lọc

Tìm kiếm theo tên sản phẩm hoặc SKU.

Lọc theo màu hoặc dung lượng (nếu có biến thể tương ứng).

5️⃣ Các yêu cầu kỹ thuật

Ứng dụng PHP + MySQL (AJAX để thao tác nhanh).

Upload ảnh vào thư mục /assets/images/products/.

Validate dữ liệu nhập (tránh SQL Injection).

Responsive UI (dùng Bootstrap hoặc Tailwind).

🚀 5. Cách cài đặt

Clone hoặc tải project:

git clone https://github.com/yourname/product-management.git


Tạo database:

CREATE DATABASE product_management;
USE product_management;
SOURCE sql/product_db.sql;


Cấu hình file config/db.php:

<?php
$conn = new mysqli("localhost", "root", "", "product_management");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>


Mở trình duyệt:

http://localhost/product-management/public/

👥 6. Phân chia công việc nhóm (5 thành viên)
Thành viên	Nhiệm vụ	Ghi chú
Dev 1	Database & Config	Tạo file SQL, kết nối DB, seed dữ liệu
Dev 2	CRUD Sản phẩm	Xây dựng form thêm/sửa/xóa sản phẩm
Dev 3	CRUD Biến thể	Quản lý màu, dung lượng, SKU, giá, tồn kho
Dev 4	Upload Ảnh	Làm chức năng upload/xóa ảnh nhiều qua AJAX
Dev 5	Giao diện & AJAX	Làm UI, gọi AJAX, hiển thị sản phẩm
💡 7. Nâng cao (tùy chọn để lấy điểm cộng)

Import / Export sản phẩm ra file Excel (.xlsx).

Thống kê tồn kho theo biến thể.

Tự động sinh SKU theo quy tắc (VD: SP001-128GB-BLACK).

Dùng SweetAlert2 khi xác nhận xóa.

Responsive UI trên điện thoại.

🏁 8. Kết luận

Hệ thống cung cấp nền tảng quản lý sản phẩm chuyên nghiệp, có thể mở rộng cho các shop thương mại điện tử, cửa hàng điện thoại, hoặc hệ thống bán hàng online.
