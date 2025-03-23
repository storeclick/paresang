$(document).ready(function() {
    let itemIndex = 1;
    let searchTimer;

    // افزودن آیتم جدید
    $('#add-item').click(function() {
        let newItem = `<div class="invoice-item mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <div class="position-relative">
                                <input type="text" 
                                       name="items[${itemIndex}][product_search]" 
                                       class="form-control product-search" 
                                       placeholder="نام یا کد محصول را وارد کنید..." 
                                       autocomplete="off"
                                       required>
                                <input type="hidden" 
                                       name="items[${itemIndex}][product_id]" 
                                       class="product-id">
                            </div>
                            <div class="product-info mt-2 d-none">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <span class="stock-info"></span>
                                </small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <input type="number" 
                                   name="items[${itemIndex}][quantity]" 
                                   class="form-control quantity-input" 
                                   placeholder="تعداد" 
                                   min="1" 
                                   required>
                        </div>
                        <div class="col-md-3">
                            <div class="input-group">
                                <input type="number" 
                                       name="items[${itemIndex}][price]" 
                                       class="form-control price-input" 
                                       placeholder="قیمت واحد" 
                                       required>
                                <span class="input-group-text">تومان</span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-outline-danger remove-item w-100">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12">
                            <div class="text-end">
                                <small class="text-muted row-total"></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>`;
        $('#invoice-items').append(newItem);
        $('#invoice-items .product-search').last().focus();
        itemIndex++;
    });

    // حذف آیتم
    $(document).on('click', '.remove-item', function() {
        const $item = $(this).closest('.invoice-item');
        
        if ($('#invoice-items .invoice-item').length > 1) {
            $item.fadeOut(300, function() {
                $(this).remove();
                updateTotals();
            });
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'خطا',
                text: 'حداقل یک آیتم باید در فاکتور وجود داشته باشد',
                confirmButtonText: 'باشه'
            });
        }
    });

    // جستجوی محصول
    $(document).on('input', '.product-search', function() {
        let searchInput = $(this);
        let $item = searchInput.closest('.invoice-item');
        
        clearTimeout(searchTimer);
        $('.search-results').remove();
        
        const query = searchInput.val().trim();
        if (query.length < 2) return;

        searchInput.addClass('loading');
        
        searchTimer = setTimeout(() => {
            $.ajax({
                url: 'ajax/search-products.php',
                method: 'GET',
                data: { query: query },
                success: function(response) {
                    $('.search-results').remove();
                    
                    if (Array.isArray(response) && response.length > 0) {
                        const $results = $('<div class="search-results position-absolute w-100 mt-1"></div>');
                        const $list = $('<div class="list-group list-group-flush"></div>');
                        
                        response.forEach(product => {
                            $list.append(`
                                <button type="button" 
                                        class="list-group-item list-group-item-action product-item" 
                                        data-id="${product.id}"
                                        data-name="${product.display_text}"
                                        data-price="${product.price}"
                                        data-stock="${product.stock}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong>${product.display_text}</strong>
                                        <span class="text-primary">${product.formatted_price} تومان</span>
                                    </div>
                                    <small class="text-muted d-block">
                                        موجودی: ${product.stock_status}
                                    </small>
                                </button>
                            `);
                        });
                        
                        $results.append($list);
                        searchInput.after($results);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('خطا در جستجوی محصولات:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'خطا',
                        text: 'خطا در جستجوی محصولات. لطفاً دوباره تلاش کنید.',
                        confirmButtonText: 'باشه'
                    });
                },
                complete: function() {
                    searchInput.removeClass('loading');
                }
            });
        }, 300);
    });

    // انتخاب محصول از نتایج جستجو
    $(document).on('click', '.product-item', function(e) {
        e.preventDefault();
        const $button = $(this);
        const $item = $button.closest('.invoice-item');
        const $search = $item.find('.product-search');
        const $productId = $item.find('.product-id');
        const $price = $item.find('input[name$="[price]"]');
        const $quantity = $item.find('input[name$="[quantity]"]');
        const $productInfo = $item.find('.product-info');
        
        // پر کردن اطلاعات محصول
        $search.val($button.data('name'));
        $productId.val($button.data('id'));
        $price.val($button.data('price'));
        
        // بررسی موجودی
        if ($button.data('stock') > 0) {
            $quantity.attr('max', $button.data('stock'));
            $quantity.prop('disabled', false);
        } else {
            $quantity.prop('disabled', true);
        }
        
        // نمایش اطلاعات محصول
        if ($productInfo.length) {
            $productInfo.removeClass('d-none');
            $productInfo.find('.stock-info').html(`موجودی: ${$button.data('stock')} عدد`);
        }
        
        $('.search-results').remove();
        updateRowTotal($item);
        
        // فوکوس روی فیلد تعداد
        $quantity.focus();
    });

    // به‌روزرسانی جمع ردیف
    function updateRowTotal($item) {
        const quantity = parseFloat($item.find('.quantity-input').val()) || 0;
        const price = parseFloat($item.find('.price-input').val()) || 0;
        const total = quantity * price;
        
        $item.find('.row-total').html(`
            <i class="fas fa-calculator me-1"></i>
            جمع: ${numberFormat(total)} تومان
        `);
        
        updateTotals();
    }

    // به‌روزرسانی مبالغ کل
    function updateTotals() {
        let subtotal = 0;
        
        $('.invoice-item').each(function() {
            const quantity = parseFloat($(this).find('.quantity-input').val()) || 0;
            const price = parseFloat($(this).find('.price-input').val()) || 0;
            subtotal += quantity * price;
        });
        
        const taxRate = parseFloat($('#tax_rate').val()) || 0;
        const discountAmount = parseFloat($('#discount_amount').val()) || 0;
        
        const taxAmount = subtotal * (taxRate / 100);
        const finalAmount = subtotal + taxAmount - discountAmount;
        
        $('#subtotal').text(numberFormat(subtotal) + ' تومان');
        $('#tax-amount').text(numberFormat(taxAmount) + ' تومان');
        $('#discount').text(numberFormat(discountAmount) + ' تومان');
        $('#final-amount').text(numberFormat(finalAmount) + ' تومان');
    }

    // به‌روزرسانی محاسبات در تغییر مقادیر
    $(document).on('input', '.quantity-input, .price-input', function() {
        updateRowTotal($(this).closest('.invoice-item'));
    });

    $('#tax_rate, #discount_amount').on('input', updateTotals);

    // تبدیل اعداد به فرمت فارسی با جداکننده هزارگان
    function numberFormat(num) {
        return new Intl.NumberFormat('fa-IR').format(num);
    }

    // بستن نتایج جستجو با کلیک خارج از آن
    $(document).click(function(e) {
        if (!$(e.target).closest('.invoice-item').length) {
            $('.search-results').remove();
        }
    });

    // حفظ کدهای نمودارها از فایل اصلی
    // نمودار فروش
    const ctx = document.getElementById('salesChart');
    if (ctx) {
        const ctx2d = ctx.getContext('2d');
        let salesChart = new Chart(ctx2d, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'فروش',
                    data: [],
                    borderColor: '#4e73df',
                    tension: 0.3,
                    fill: true,
                    backgroundColor: 'rgba(78, 115, 223, 0.05)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // دریافت داده‌های نمودار
        function updateChart(period = 'week') {
            $.ajax({
                url: 'ajax/get-sales-data.php',
                data: { period: period },
                success: function(response) {
                    salesChart.data.labels = response.labels;
                    salesChart.data.datasets[0].data = response.data;
                    salesChart.update();
                }
            });
        }

        // تغییر دوره زمانی نمودار
        $('.btn-group .btn').click(function() {
            $('.btn-group .btn').removeClass('active');
            $(this).addClass('active');
            updateChart($(this).data('period'));
        });

        // بارگذاری اولیه نمودار
        updateChart();
    }

    // نمودار نقدینگی
    const ctxCashFlow = document.getElementById('cashFlowChart');
    if (ctxCashFlow) {
        const ctxCashFlow2d = ctxCashFlow.getContext('2d');
        let cashFlowChart = new Chart(ctxCashFlow2d, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'نقدینگی',
                    data: [],
                    borderColor: '#1cc88a',
                    tension: 0.3,
                    fill: true,
                    backgroundColor: 'rgba(28, 200, 138, 0.05)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // دریافت داده‌های نمودار نقدینگی
        function updateCashFlowChart() {
            $.ajax({
                url: 'ajax/get-cash-flow-data.php',
                success: function(response) {
                    cashFlowChart.data.labels = response.labels;
                    cashFlowChart.data.datasets[0].data = response.data;
                    cashFlowChart.update();
                }
            });
        }

        // بارگذاری اولیه نمودار نقدینگی
        updateCashFlowChart();
    }

    // نمودار درآمد و هزینه‌ها
    const ctxIncomeExpense = document.getElementById('incomeExpenseChart');
    if (ctxIncomeExpense) {
        const ctxIncomeExpense2d = ctxIncomeExpense.getContext('2d');
        let incomeExpenseChart = new Chart(ctxIncomeExpense2d, {
            type: 'pie',
            data: {
                labels: ['درآمد', 'هزینه‌ها'],
                datasets: [{
                    label: 'مقدار',
                    data: [],
                    backgroundColor: ['rgba(54, 162, 235, 0.2)', 'rgba(255, 99, 132, 0.2)'],
                    borderColor: ['rgba(54, 162, 235, 1)', 'rgba(255, 99, 132, 1)'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.label + ': ' + tooltipItem.raw + ' تومان';
                            }
                        }
                    }
                }
            }
        });

        // دریافت داده‌های نمودار درآمد و هزینه‌ها
        function updateIncomeExpenseChart() {
            $.ajax({
                url: 'ajax/get-income-expense-data.php',
                success: function(response) {
                    incomeExpenseChart.data.labels = response.labels;
                    incomeExpenseChart.data.datasets[0].data = response.data;
                    incomeExpenseChart.update();
                }
            });
        }

        // بارگذاری اولیه نمودار درآمد و هزینه‌ها
        updateIncomeExpenseChart();
    }

    // اگر فرم خالی باشد، اولین آیتم را اضافه کن
    if ($('#invoice-items').is(':empty')) {
        $('#add-item').click();
    }
});