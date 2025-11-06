<?php
// File: views/products/import.php
?>

<div class="main-container fade-in">
    <!-- Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fas fa-file-import"></i> Import Sản phẩm từ Excel</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                        <li class="breadcrumb-item"><a href="index.php?controller=product&action=index">Sản phẩm</a></li>
                        <li class="breadcrumb-item active">Import</li>
                    </ol>
                </nav>
            </div>
            <a href="index.php?controller=product&action=index" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Import Form Card -->
        <div class="col-lg-8">
            <div class="modern-card">
                <div class="card-header-modern">
                    <h5><i class="fas fa-upload"></i> Upload File Excel</h5>
                    <span class="badge bg-light text-dark">Bước 1</span>
                </div>
                <div class="card-body-ajax">
                    <form id="import-form" action="index.php?controller=product&action=import_process" method="POST" enctype="multipart/form-data">
                        
                        <!-- File Upload Area -->
                        <div class="upload-area mb-4" id="upload-area">
                            <div class="text-center py-5">
                                <i class="fas fa-cloud-upload-alt fa-4x text-primary mb-3"></i>
                                <h5>Kéo thả file vào đây hoặc click để chọn</h5>
                                <p class="text-muted mb-3">
                                    Chấp nhận file: .xlsx, .xls (Max 10MB)
                                </p>
                                <input type="file" 
                                       name="excel_file" 
                                       id="excel_file" 
                                       class="d-none" 
                                       accept=".xlsx,.xls"
                                       required>
                                <button type="button" class="btn btn-primary-modern" onclick="$('#excel_file').click()">
                                    <i class="fas fa-folder-open"></i> Chọn file
                                </button>
                            </div>
                        </div>

                        <!-- Selected File Info -->
                        <div id="file-info" class="alert alert-info d-none">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-file-excel fa-2x text-success me-3"></i>
                                    <span id="file-name" class="fw-bold"></span>
                                    <small id="file-size" class="text-muted ms-2"></small>
                                </div>
                                <button type="button" class="btn btn-sm btn-danger" onclick="clearFile()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Import Options -->
                        <div class="search-form-modern mb-4">
                            <h6 class="mb-3"><i class="fas fa-cog"></i> Tùy chọn Import</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="update_existing" id="update_existing" value="1" checked>
                                <label class="form-check-label" for="update_existing">
                                    Cập nhật sản phẩm/biến thể đã tồn tại
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="skip_errors" id="skip_errors" value="1" checked>
                                <label class="form-check-label" for="skip_errors">
                                    Bỏ qua dòng có lỗi và tiếp tục import
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="create_log" id="create_log" value="1">
                                <label class="form-check-label" for="create_log">
                                    Tạo file log chi tiết sau khi import
                                </label>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success-modern">
                                <i class="fas fa-play"></i> Bắt đầu Import
                            </button>
                            <button type="button" class="btn btn-info-modern" id="btn-preview">
                                <i class="fas fa-eye"></i> Preview trước
                            </button>
                            <a href="index.php?controller=product&action=index" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Hủy
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Instructions Card -->
        <div class="col-lg-4">
            <!-- Download Template -->
            <div class="modern-card mb-4">
                <div class="card-header-modern">
                    <h5><i class="fas fa-download"></i> Tải mẫu</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        Tải file Excel mẫu để đảm bảo định dạng đúng
                    </p>
                    <a href="index.php?controller=product&action=export_template" 
                       class="btn btn-success-modern w-100 mb-2">
                        <i class="fas fa-file-excel"></i> Tải file mẫu (.xlsx)
                    </a>
                    <a href="index.php?controller=product&action=export_template&format=csv" 
                       class="btn btn-info-modern w-100">
                        <i class="fas fa-file-csv"></i> Tải file mẫu (.csv)
                    </a>
                </div>
            </div>

            <!-- Instructions -->
            <div class="modern-card">
                <div class="card-header-modern">
                    <h5><i class="fas fa-info-circle"></i> Hướng dẫn</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <strong><i class="fas fa-exclamation-triangle"></i> Lưu ý:</strong>
                        <ul class="mb-0 mt-2 small">
                            <li>Dòng đầu tiên là tiêu đề (sẽ bị bỏ qua)</li>
                            <li>SKU sản phẩm không được trùng</li>
                            <li>Giá và tồn kho phải là số</li>
                            <li>File không quá 10MB</li>
                        </ul>
                    </div>

                    <h6 class="mt-4 mb-3">📋 Định dạng cột:</h6>
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Cột</th>
                                <th>Tên trường</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            <tr>
                                <td><strong>A</strong></td>
                                <td>Product SKU<br><small class="text-muted">VD: IP16PRM</small></td>
                            </tr>
                            <tr>
                                <td><strong>B</strong></td>
                                <td>Product Name<br><small class="text-muted">VD: iPhone 16 Pro</small></td>
                            </tr>
                            <tr>
                                <td><strong>C</strong></td>
                                <td>Description<br><small class="text-muted">Mô tả sản phẩm</small></td>
                            </tr>
                            <tr>
                                <td><strong>D</strong></td>
                                <td>Variant Color<br><small class="text-muted">VD: Black</small></td>
                            </tr>
                            <tr>
                                <td><strong>E</strong></td>
                                <td>Variant Storage<br><small class="text-muted">VD: 256GB</small></td>
                            </tr>
                            <tr>
                                <td><strong>F</strong></td>
                                <td>Variant Price<br><small class="text-muted">VD: 25000000</small></td>
                            </tr>
                            <tr>
                                <td><strong>G</strong></td>
                                <td>Variant Stock<br><small class="text-muted">VD: 100</small></td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="alert alert-info mt-3">
                        <small>
                            <strong>SKU biến thể tự động:</strong><br>
                            Định dạng: <code>{SKU}-{Color}-{Storage}</code><br>
                            Ví dụ: <code>IP16PRM-Black-256</code>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.upload-area {
    border: 3px dashed #cbd5e1;
    border-radius: 12px;
    background: #f8fafc;
    cursor: pointer;
    transition: all 0.3s ease;
}

.upload-area:hover {
    border-color: var(--primary-color);
    background: #eff6ff;
}

.upload-area.dragover {
    border-color: var(--success-color);
    background: #f0fdf4;
    transform: scale(1.02);
}

.table-sm td, .table-sm th {
    padding: 8px;
    font-size: 13px;
}
</style>

<script>
$(document).ready(function() {
    const uploadArea = $('#upload-area');
    const fileInput = $('#excel_file');
    const fileInfo = $('#file-info');
    const fileName = $('#file-name');
    const fileSize = $('#file-size');

    // Click to select file
    uploadArea.on('click', function() {
        fileInput.click();
    });

    // File selected
    fileInput.on('change', function() {
        const file = this.files[0];
        if (file) {
            displayFileInfo(file);
        }
    });

    // Drag & Drop
    uploadArea.on('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('dragover');
    });

    uploadArea.on('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('dragover');
    });

    uploadArea.on('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('dragover');
        
        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            fileInput[0].files = files;
            displayFileInfo(files[0]);
        }
    });

    // Display file info
    function displayFileInfo(file) {
        const sizeMB = (file.size / 1024 / 1024).toFixed(2);
        
        // Validate file type
        const validTypes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel'
        ];
        
        if (!validTypes.includes(file.type) && !file.name.match(/\.(xlsx|xls)$/)) {
            alert('Vui lòng chọn file Excel (.xlsx hoặc .xls)');
            clearFile();
            return;
        }

        // Validate file size (max 10MB)
        if (file.size > 10 * 1024 * 1024) {
            alert('File không được vượt quá 10MB');
            clearFile();
            return;
        }

        fileName.text(file.name);
        fileSize.text(`(${sizeMB} MB)`);
        fileInfo.removeClass('d-none');
        uploadArea.hide();
    }

    // Clear file
    window.clearFile = function() {
        fileInput.val('');
        fileInfo.addClass('d-none');
        uploadArea.show();
    };

    // Preview button (optional - requires additional implementation)
    $('#btn-preview').on('click', function() {
        if (!fileInput[0].files || fileInput[0].files.length === 0) {
            alert('Vui lòng chọn file trước!');
            return;
        }
        
        // TODO: Implement preview functionality with AJAX
        alert('Chức năng preview đang được phát triển...');
    });

    // Form submit validation
    $('#import-form').on('submit', function(e) {
        if (!fileInput[0].files || fileInput[0].files.length === 0) {
            e.preventDefault();
            alert('Vui lòng chọn file Excel để import!');
            return false;
        }

        // Show loading
        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true)
                 .html('<i class="fas fa-spinner fa-spin"></i> Đang import...');
    });
});
</script>