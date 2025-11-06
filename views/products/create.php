<?php
// File: views/products/create.php
?>
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-body">
                <form action="index.php?controller=product&action=store" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="name" class="form-label">Tên sản phẩm</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Mô tả</label>
                        <textarea class="form-control" id="description" name="description" rows="5"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Ảnh thumbnail</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                    </div>
                    
                    <a href="index.php?controller=product&action=index" class="btn btn-secondary">Hủy</a>
                    <button type="submit" class="btn btn-primary">Lưu và tiếp tục (để thêm biến thể)</button>
                </form>
            </div>
        </div>
    </div>
</div>