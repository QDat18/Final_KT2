<footer>
    <div class="container-fluid">
        <div class="row justify-content-center g-4 mb-4">
            <div class="col-12 text-center">
                <h5 style="color: var(--dark-color); font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">
                    Banking Academy - Group 7
                </h5>
            </div>
            
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="footer-member">
                    <h6>Hoàng Quang Đạt</h6>
                    <img src="assets/images/team/hsq5.png" alt="Hoàng Quang Đạt" class="img-fluid rounded-circle mb-2" style="width: 80px; height: 80px; object-fit: cover;">
                    <p class="text-muted small">Build models/ Controllers/ Search</p>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="footer-member">
                    <h6>Nguyễn Mạnh Thắng</h6>
                    <img src="assets/images/team/hsq4.jpg" alt="Nguyễn Mạnh Thắng" class="img-fluid rounded-circle mb-2" style="width: 80px; height: 80px; object-fit: cover;">
                    <p class="text-muted small">Import / View Products</p>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="footer-member">
                    <h6>Nguyễn Quang Duy</h6>
                    <img src="assets/images/team/hsq3.jpg" alt="Nguyễn Quang Duy" class="img-fluid rounded-circle mb-2" style="width: 80px; height: 80px; object-fit: cover;">
                    <p class="text-muted small">Export/ Delete Products</p>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="footer-member">
                    <h6>Hoàng Gia Khiêm</h6>
                    <img src="assets/images/team/hsq2.jpg" alt="Hoàng Gia Khiêm" class="img-fluid rounded-circle mb-2" style="width: 80px; height: 80px; object-fit: cover;">
                    <p class="text-muted small">Create Products</p>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="footer-member">
                    <h6>Đặng Trường Duy</h6>
                    <img src="assets/images/team/hsq1.jpg" alt="Đặng Trường Duy" class="img-fluid rounded-circle mb-2" style="width: 80px; height: 80px; object-fit: cover;">
                    <p class="text-muted small">Resources / Database / Update Products</p>
                </div>
            </div>
        </div>

        <hr style="border-color: var(--border-color);">
        
        <div class="text-center mt-3" style="color: #6b7280;">
            &copy; 2025 Banking Academy Group 7 - All Rights Reserved
        </div>
    </div>
</footer>

</div> 

<?php
    $current_controller = $_GET['controller'] ?? 'product';
    $current_action = $_GET['action'] ?? 'index';
    
    // Tạo đường dẫn file JS, ví dụ: "assets/js/product-index.js"
    $page_js_file = "assets/js/{$current_controller}-{$current_action}.js";

    // Kiểm tra xem file JS đó có tồn tại không
    if (file_exists($page_js_file)) {
        echo '<script src="' . $page_js_file . '"></script>';
    }
?>
</body>
</html>