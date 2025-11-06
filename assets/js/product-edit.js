$(document).ready(function () {

    const productId = $('#product-id').val();
    const variantTableBody = $('#variant-table-body');
    const imageGallery = $('#image-gallery');

    // Khởi tạo đối tượng Modal
    const editVariantModal = new bootstrap.Modal(document.getElementById('editVariantModal'));

    // Color map
    const colorMap = {
        'Đen': '#000000',
        'Trắng': '#FFFFFF',
        'Bạc': '#C0C0C0',
        'Xám': '#808080',
        'Titan Tự nhiên': '#A6A199',
        'Vàng': '#FFD700',
        'Đỏ': '#E74C3C',
        'Xanh Dương': '#3498DB',
        'Xanh Lá': '#2ECC71',
        'Tím': '#9B59B6',
        'Hồng': '#FADADD'
    };

    // ========== TOAST NOTIFICATION ==========
    const showToast = (message, isSuccess = true) => {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: isSuccess ? 'success' : 'error',
            title: message,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    }

    // ========== VARIANT MANAGEMENT ==========

    // === THÊM BIẾN THỂ MỚI (AJAX) ===
    $('#variant-form').on('submit', function (e) {
        e.preventDefault();

        const $form = $(this);
        const $submitButton = $form.find('button[type="submit"]');
        const originalButtonHtml = $submitButton.html();

        $submitButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang thêm...');

        const formData = new FormData(this);

        $.ajax({
            url: SITE_URL,
            type: 'POST',
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            success: function (response) {
                if (response.success) {
                    showToast(response.message, true);
                    $('#no-variants-row').remove();
                    variantTableBody.append(response.variant_html);
                    $form[0].reset();
                } else {
                    showToast(response.message, false);
                }
            },
            error: function (jqXHR) {
                const errorMsg = jqXHR.responseJSON?.message || 'Lỗi không xác định. Vui lòng thử lại.';
                showToast(errorMsg, false);
                console.error('Error:', jqXHR.responseText);
            },
            complete: function () {
                $submitButton.prop('disabled', false).html(originalButtonHtml);
            }
        });
    });

    // === MỞ MODAL SỬA BIẾN THỂ ===
    $(document).on('click', '.btn-edit-variant', function () {
        const id = $(this).data('id');
        const color = $(this).data('color');
        const storage = $(this).data('storage');
        const price = $(this).data('price');
        const stock = $(this).data('stock');
        const imageUrl = $(this).data('image_url') || '';

        // Điền dữ liệu vào form
        $('#edit-variant-id').val(id);
        $('#edit-variant-color').text(color);
        $('#edit-variant-storage').text(storage);
        $('#edit-variant-price').val(price);
        $('#edit-variant-stock').val(stock);

        // Set color circle
        const colorHex = colorMap[color] || '#FFFFFF';
        const border = (colorHex === '#FFFFFF') ? 'border: 1px solid #ccc;' : '';
        $('#edit-variant-color-circle').css({
            'background-color': colorHex,
            'border': border ? '1px solid #ccc' : 'none'
        });

        // Display current image
        if (imageUrl) {
            $('#current-variant-image').html(`
                <img src="${imageUrl}" alt="Variant Image" style="max-width: 200px; max-height: 200px; border-radius: 8px;">
            `);
        } else {
            $('#current-variant-image').html(`
                <i class="fas fa-image fa-3x text-muted"></i>
            `);
        }

        // Reset file input and preview
        $('#edit-variant-image').val('');
        $('#new-variant-image-preview').hide();

        // Mở modal
        editVariantModal.show();
    });

    // === SUBMIT FORM SỬA BIẾN THỂ ===
    $('#edit-variant-form').on('submit', function (e) {
        e.preventDefault();
        const $form = $(this);
        const $submitButton = $form.find('button[type="submit"]');
        const originalButtonHtml = $submitButton.html();

        $submitButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang lưu...');

        const formData = new FormData(this);
        formData.append('controller', 'variant');
        formData.append('action', 'ajax_update');
        formData.append('product_id', productId);

        const variantId = $('#edit-variant-id').val();
        const newPriceVal = $('#edit-variant-price').val();
        const newStockVal = $('#edit-variant-stock').val();

        $.ajax({
            url: SITE_URL,
            type: 'POST',
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            success: function (response) {
                if (response.success) {
                    showToast(response.message, true);
                    editVariantModal.hide();

                    // Cập nhật giá và tồn kho
                    $('#variant-price-' + variantId).text(response.price);
                    $('#variant-stock-' + variantId).text(response.stock);

                    // Cập nhật ảnh nếu có
                    if (response.image_url) {
                        const newImageHtml = `<img src="${response.image_url}" class="variant-thumbnail" alt="Variant Image">`;
                        $('#variant-image-wrapper-' + variantId).html(newImageHtml);
                    }

                    // Cập nhật data attributes
                    const $editButton = $('.btn-edit-variant[data-id="' + variantId + '"]');
                    $editButton.data('price', newPriceVal);
                    $editButton.data('stock', newStockVal);
                    if (response.image_url) {
                        $editButton.data('image_url', response.image_url);
                    }

                } else {
                    showToast(response.message, false);
                }
            },
            error: function (jqXHR) {
                const errorMsg = jqXHR.responseJSON?.message || 'Lỗi không xác định.';
                showToast(errorMsg, false);
                console.error('Error:', jqXHR.responseText);
            },
            complete: function () {
                $submitButton.prop('disabled', false).html(originalButtonHtml);
            }
        });
    });

    // === XÓA BIẾN THỂ ===
    $(document).on('click', '.btn-delete-variant', function () {
        const $thisButton = $(this);
        const variantId = $thisButton.data('id');
        const variantName = $thisButton.data('name');

        Swal.fire({
            title: 'Bạn có chắc không?',
            text: `Bạn sắp xóa biến thể "${variantName}". Hành động này không thể hoàn tác!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Đồng ý, xóa nó!',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: SITE_URL,
                    type: 'POST',
                    data: {
                        controller: 'variant',
                        action: 'ajax_delete',
                        id: variantId,
                        product_id: productId
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            showToast(response.message, true);
                            $('#variant-' + variantId).fadeOut(500, function () {
                                $(this).remove();
                                if ($('#variant-table-body tr').length === 0) {
                                    $('#variant-table-body').html(`
                                        <tr id="no-variants-row">
                                            <td colspan="7" class="text-center py-5">
                                                <div class="empty-state">
                                                    <i class="fas fa-box"></i>
                                                    <h5>Chưa có biến thể nào</h5>
                                                    <p>Hãy thêm biến thể mới ở form bên trên</p>
                                                </div>
                                            </td>
                                        </tr>
                                    `);
                                }
                            });
                        } else {
                            showToast(response.message, false);
                        }
                    },
                    error: function (jqXHR) {
                        const errorMsg = jqXHR.responseJSON?.message || 'Lỗi không thể xóa.';
                        showToast(errorMsg, false);
                    }
                });
            }
        });
    });

    // ========== IMAGE MANAGEMENT ==========

    // === UPLOAD ẢNH ===
    $('#image-form').on('submit', function (e) {
        e.preventDefault();

        const $form = $(this);
        const $submitButton = $form.find('button[type="submit"]');
        const originalButtonHtml = $submitButton.html();

        const fileInput = $('#image_url')[0];
        if (!fileInput.files || fileInput.files.length === 0) {
            showToast('Vui lòng chọn ảnh!', false);
            return;
        }

        if (fileInput.files[0].size > 5 * 1024 * 1024) {
            showToast('File ảnh không được vượt quá 5MB!', false);
            return;
        }

        $submitButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang upload...');

        const formData = new FormData(this);

        $.ajax({
            url: SITE_URL,
            type: 'POST',
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            success: function (response) {
                if (response.success) {
                    showToast(response.message, true);
                    $('#no-images-row').remove();
                    imageGallery.append(response.image_html);
                    $form[0].reset();
                    $form.find('.form-text').html('Chọn file ảnh (JPG, PNG, GIF, WEBP - Max 5MB)');
                } else {
                    showToast(response.message, false);
                }
            },
            error: function (jqXHR) {
                const errorMsg = jqXHR.responseJSON?.message || 'Upload thất bại!';
                showToast(errorMsg, false);
            },
            complete: function () {
                $submitButton.prop('disabled', false).html(originalButtonHtml);
            }
        });
    });

    // === XÓA ẢNH ===
    $(document).on('click', '.btn-delete-image', function () {
        const $thisButton = $(this);
        const imageId = $thisButton.data('id');
        const $imageItem = $thisButton.closest('.image-item');

        Swal.fire({
            title: 'Xác nhận xóa?',
            text: 'Bạn có chắc chắn muốn xóa ảnh này?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Xóa',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: SITE_URL,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        controller: 'image',
                        action: 'ajax_delete',
                        product_id: productId,
                        id: imageId
                    },
                    success: function (response) {
                        if (response.success) {
                            showToast(response.message, true);
                            $imageItem.fadeOut(400, function () {
                                $(this).remove();
                                if ($('#image-gallery .image-item').length === 0) {
                                    $('#image-gallery').html(`
                                        <div class="col-12 text-center py-5" id="no-images-row">
                                            <div class="empty-state">
                                                <i class="fas fa-images"></i>
                                                <h5>Chưa có ảnh nào</h5>
                                                <p>Hãy upload ảnh mới ở form bên trên</p>
                                            </div>
                                        </div>
                                    `);
                                }
                            });
                        } else {
                            showToast(response.message, false);
                        }
                    },
                    error: function (xhr) {
                        const response = xhr.responseJSON;
                        showToast(response?.message || 'Xóa thất bại!', false);
                    }
                });
            }
        });
    });

    // ========== FILE INPUT PREVIEW ==========
    $('#image_url').on('change', function () {
        const file = this.files[0];
        if (file) {
            const fileName = file.name;
            const fileSize = (file.size / 1024 / 1024).toFixed(2);
            $(this).next('.form-text').html(`
                <i class="fas fa-file-image text-success"></i> 
                ${fileName} (${fileSize} MB)
            `);
        } else {
            $(this).next('.form-text').html('Chọn file ảnh (JPG, PNG, GIF, WEBP - Max 5MB)');
        }
    });

    console.log('✅ Edit Product Page (Modal + Image Upload) Initialized');
});