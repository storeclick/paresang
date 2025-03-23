$(document).ready(function() {
    let itemIndex = 1;

    // افزودن آیتم جدید
    $('#add-item').click(function() {
        let newItem = `<div class="invoice-item" data-index="${itemIndex}">
            <input type="text" name="items[${itemIndex}][product_search]" class="form-control product-search" placeholder="جستجوی محصول" required>
            <input type="hidden" name="items[${itemIndex}][product_id]" class="form-control product-id">
            <input type="number" name="items[${itemIndex}][quantity]" class="form-control" placeholder="تعداد" required>
            <input type="number" name="items[${itemIndex}][price]" class="form-control" placeholder="قیمت" required>
            <span class="remove-item">&times;</span>
        </div>`;
        $('#invoice-items').append(newItem);
        itemIndex++;
    });

    // حذف آیتم
    $(document).on('click', '.remove-item', function() {
        $(this).closest('.invoice-item').remove();
    });

    // جستجوی محصول
    $(document).on('input', '.product-search', function() {
        let searchInput = $(this);
        let query = searchInput.val();
        if (query.length > 2) {
            $.ajax({
                url: 'ajax/search-products.php',
                method: 'GET',
                data: { query: query },
                success: function(response) {
                    let products = JSON.parse(response);
                    let dropdown = $('<ul class="dropdown-menu" style="display:block; position:absolute;"></ul>');
                    products.forEach(product => {
                        dropdown.append(`<li><a href="#" class="dropdown-item" data-id="${product.id}" data-name="${product.name}">${product.name}</a></li>`);
                    });
                    searchInput.after(dropdown);
                }
            });
        }
    });

    // انتخاب محصول از جستجو
    $(document).on('click', '.dropdown-item', function(e) {
        e.preventDefault();
        let selectedProduct = $(this);
        let searchInput = selectedProduct.closest('.invoice-item').find('.product-search');
        let productIdInput = selectedProduct.closest('.invoice-item').find('.product-id');
        searchInput.val(selectedProduct.data('name'));
        productIdInput.val(selectedProduct.data('id'));
        $('.dropdown-menu').remove();
    });

    // بستن منوی جستجو در کلیک خارج از آن
    $(document).click(function(e) {
        if (!$(e.target).closest('.product-search').length) {
            $('.dropdown-menu').remove();
        }
    });

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
                labels: [], // برچسب‌های زمانی
                datasets: [{
                    label: 'نقدینگی',
                    data: [], // داده‌های نقدینگی
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
                    data: [], // داده‌های درآمد و هزینه‌ها
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
});