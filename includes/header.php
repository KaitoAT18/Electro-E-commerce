<?php
require_once dirname(__FILE__) . '/../includes/functions.php';

// Check if the user is logged in
$isLoggedIn = check_login();

// Check if the user is an admin
$isAdmin = false;
if ($isLoggedIn) {
    $isAdmin = isAdmin();
}
?>
<header>
    <!-- TOP HEADER -->
    <div id="top-header">
        <div class="container">
            <ul class="header-links pull-left">
                <li>
                    <a href="#"><i class="fa fa-phone"></i> +021-95-51-84</a>
                </li>
                <li>
                    <a href="#"><i class="fa fa-envelope-o"></i>
                        support@electro.com
                    </a>
                </li>
                <li>
                    <a href="#"><i class="fa fa-map-marker"></i> 1734 Stonecoal
                        Road
                    </a>
                </li>
            </ul>
            <ul class="header-links pull-right">
                <?php if ($isLoggedIn): ?>
                    <li>
                        <a href="<?php echo BASE_URL ?>/public/profile.php"><i class="fa fa-user-o"></i> My Account</a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL ?>/public/logout.php"><i class="fa fa-sign-out"></i> Logout</a>
                    </li>
                <?php else: ?>
                    <li>
                        <a href="<?php echo BASE_URL ?>/public/login.php"><i class="fa fa-sign-in"></i> Login</a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL ?>/public/register.php"><i class="fa fa-user-plus"></i> Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    <!-- /TOP HEADER -->

    <!-- MAIN HEADER -->
    <div id="header">
        <!-- container -->
        <div class="container">
            <!-- row -->
            <div class="row">
                <!-- LOGO -->
                <div class="col-md-3">
                    <div class="header-logo">
                        <a href="<?php echo BASE_URL ?>/public/index.php" class="logo">
                            <img src="<?php echo IMAGE_DIR ?>logo.png" alt="Electro Logo" />
                        </a>
                    </div>
                </div>
                <!-- /LOGO -->

                <!-- SEARCH BAR -->
                <div class="<?php echo $isLoggedIn ? 'col-md-6' : 'col-md-7'; ?>">
                    <div class="header-search">
                        <form method="GET" action="<?php echo BASE_URL ?>/public/search.php">
                            <input class="input" name="q" placeholder="Search here" />
                            <button class="search-btn">Search</button>
                        </form>
                    </div>
                </div>
                <!-- /SEARCH BAR -->

                <!-- ACCOUNT -->
                <div class="<?php echo $isLoggedIn ? 'col-md-3' : 'col-md-2'; ?> clearfix">
                    <div class="header-ctn">
                        <?php if ($isLoggedIn): ?>
                            <!-- Cart -->
                            <div class="dropdown">
                                <a class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                                    <i class="fa fa-shopping-cart"></i>
                                    <span>Your Cart</span>
                                    <div class="qty cart-count">
                                        <?php echo isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0; ?>
                                    </div>
                                </a>
                                <div class="cart-dropdown">
                                    <div class="cart-list">
                                        <?php
                                        if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                                            $cartItems = array_values($_SESSION['cart']);
                                            $total = 0;
                                            $displayLimit = min(count($cartItems), 3);

                                            for ($i = 0; $i < $displayLimit; $i++) {
                                                $item = $cartItems[$i];
                                                $price = $item['price'];
                                                $total += $price * $item['quantity'];
                                                ?>
                                                <div class="product-widget">
                                                    <div class="product-img">
                                                        <img src="<?php echo BASE_URL . htmlspecialchars($item['primary_image']); ?>"
                                                            alt="">
                                                    </div>
                                                    <div class="product-body">
                                                        <h3 class="product-name"><a
                                                                href="<?php echo BASE_URL; ?>/public/product_detail.php?id=<?php echo $item['product_id']; ?>"><?php echo htmlspecialchars($item['product_name']); ?></a>
                                                        </h3>
                                                        <h4 class="product-price"><span
                                                                class="qty"><?php echo $item['quantity']; ?>x</span>$<?php echo number_format($price, 2); ?>
                                                        </h4>
                                                    </div>
                                                </div>
                                                <?php
                                            }

                                            if (count($cartItems) > 3) {
                                                echo '<div class="text-center"><small>and ' . (count($cartItems) - 3) . ' more item(s)</small></div>';
                                            }
                                        } else {
                                            echo '<div class="text-center py-3">Your cart is empty</div>';
                                        }
                                        ?>
                                    </div>
                                    <div class="cart-summary">
                                        <small><?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>
                                            Item(s) selected</small>
                                        <h5>SUBTOTAL: $<?php
                                        $total = 0;
                                        if (isset($_SESSION['cart'])) {
                                            foreach ($_SESSION['cart'] as $item) {
                                                $total += $item['price'] * $item['quantity'];
                                            }
                                        }
                                        echo number_format($total, 2);
                                        ?></h5>
                                    </div>
                                    <div class="cart-btns">
                                        <a href="<?php echo BASE_URL ?>/public/cart.php">View Cart</a>
                                        <a href="<?php echo BASE_URL ?>/public/checkout.php">Checkout <i
                                                class="fa fa-arrow-circle-right"></i></a>
                                    </div>
                                </div>
                            </div>
                            <!-- /Cart -->
                        <?php endif; ?>

                        <!-- Menu Toogle -->
                        <div class="menu-toggle">
                            <a href="#">
                                <i class="fa fa-bars"></i>
                                <span>Menu</span>
                            </a>
                        </div>
                        <!-- /Menu Toogle -->
                    </div>
                </div>
                <!-- /ACCOUNT -->
            </div>
            <!-- row -->
        </div>
        <!-- container -->
    </div>
    <!-- /MAIN HEADER -->
</header>

<!-- NAVIGATION -->
<nav id="navigation">
    <!-- container -->
    <div class="container">
        <!-- responsive-nav -->
        <div id="responsive-nav">
            <!-- NAV -->
            <ul class="main-nav nav navbar-nav">
                <?php
                // Get the current URL
                $currentUrl = $_SERVER['REQUEST_URI'];
                $baseUrlPath = parse_url(BASE_URL, PHP_URL_PATH);

                // Kiểm tra chính xác hơn xem đang ở trang nào
                $isHomePage = ($currentUrl === $baseUrlPath ||
                    $currentUrl === $baseUrlPath . '/' ||
                    $currentUrl === $baseUrlPath . '/public/index.php' ||
                    $currentUrl === $baseUrlPath . '/public/');

                $isAdminPage = strpos($currentUrl, '/public/admin/') !== false;

                $menuItems = [
                    'index.php' => 'Home',
                    'categories.php' => 'Categories',
                    'products.php' => 'Products',
                    'about.php' => 'About Us',
                ];

                if ($isLoggedIn) {
                    $menuItems['cart.php'] = 'My Cart';
                    $menuItems['orders.php'] = 'My Orders';

                    if ($isAdmin) {
                        $menuItems['admin/index.php'] = 'Admin Dashboard';
                    }
                }

                foreach ($menuItems as $page => $label) {
                    $isActive = '';

                    // Kiểm tra trang active
                    if ($page === 'index.php' && $isHomePage && !$isAdminPage) {
                        $isActive = 'class="active"';
                    } else if ($page === 'admin/index.php' && $isAdminPage) {
                        $isActive = 'class="active"';
                    } else if ($page !== 'index.php' && $page !== 'admin/index.php' && isCurrentPage($page)) {
                        $isActive = 'class="active"';
                    }

                    echo '<li ' . $isActive . '><a href="' . BASE_URL . '/public/' . $page . '">' . $label . '</a></li>';
                }
                ?>
            </ul>
            <!-- /NAV -->
        </div>
        <!-- /responsive-nav -->
    </div>
    <!-- /container -->
</nav>
<!-- /NAVIGATION -->

<script>
    var BASE_URL = '<?php echo BASE_URL ?>';

    window.updateHeaderCart = function () {
        $.ajax({
            url: BASE_URL + '/public/api/cart.php?action=view',
            type: 'GET',
            success: function (response) {
                // Update cart content
                let cartHTML = '';
                let total = 0;
                let itemCount = 0;

                if (response.data && response.data.length > 0) {
                    // Display a maximum of 3 products
                    const displayLimit = Math.min(response.data.length, 3);

                    for (let i = 0; i < displayLimit; i++) {
                        const item = response.data[i];
                        total += parseFloat(item.subtotal);
                        itemCount += parseInt(item.quantity);

                        cartHTML += `
                            <div class="product-widget">
                                <div class="product-img">
                                    <img src="${BASE_URL}${item.primary_image}" alt="">
                                </div>
                                <div class="product-body">
                                    <h3 class="product-name"><a href="${BASE_URL}/public/product_detail.php?id=${item.product_id}">${item.product_name}</a></h3>
                                    <h4 class="product-price"><span class="qty">${item.quantity}x</span>$${parseFloat(item.price).toFixed(2)}</h4>
                                </div>
                                <button class="delete mini-cart-remove" data-product-id="${item.product_id}"><i class="fa fa-close"></i></button>
                            </div>
                        `;
                    }

                    if (response.data.length > 3) {
                        cartHTML += `<div class="text-center"><small>and ${response.data.length - 3} more item(s)</small></div>`;
                    }

                    $('.cart-count').text(itemCount);
                    $('.cart-summary small').text(response.data.length + ' Item(s) selected');
                    $('.cart-summary h5').text('SUBTOTAL: $' + total.toFixed(2));
                } else {
                    cartHTML = '<div class="text-center py-3">Your cart is empty</div>';
                    $('.cart-count').text('0');
                    $('.cart-summary small').text('0 Item(s) selected');
                    $('.cart-summary h5').text('SUBTOTAL: $0.00');
                }

                $('.cart-list').html(cartHTML);

                $('.header-ctn .dropdown').addClass('cart-updated');
                setTimeout(function () {
                    $('.header-ctn .dropdown').removeClass('cart-updated');
                }, 1000);
            }
        });
    };

    $(document).ready(function () {
        window.updateHeaderCart = function () {
            $.ajax({
                url: '<?php echo BASE_URL ?>/public/api/cart.php?action=view',
                type: 'GET',
                success: function (response) {
                    let cartHTML = '';
                    let total = 0;
                    let itemCount = 0;

                    if (response.data && response.data.length > 0) {
                        const displayLimit = Math.min(response.data.length, 3);

                        for (let i = 0; i < displayLimit; i++) {
                            const item = response.data[i];
                            total += parseFloat(item.subtotal);
                            itemCount += parseInt(item.quantity);

                            cartHTML += `
                                <div class="product-widget">
                                    <div class="product-img">
                                        <img src="<?php echo BASE_URL ?>${item.primary_image}" alt="">
                                    </div>
                                    <div class="product-body">
                                        <h3 class="product-name"><a href="<?php echo BASE_URL ?>/public/product_detail.php?id=${item.product_id}">${item.product_name}</a></h3>
                                        <h4 class="product-price"><span class="qty">${item.quantity}x</span>$${parseFloat(item.price).toFixed(2)}</h4>
                                    </div>
                                    <button class="delete mini-cart-remove" data-product-id="${item.product_id}"><i class="fa fa-close"></i></button>
                                </div>
                            `;
                        }

                        if (response.data.length > 3) {
                            cartHTML += `<div class="text-center"><small>and ${response.data.length - 3} more item(s)</small></div>`;
                        }

                        $('.cart-count').text(itemCount);
                        $('.cart-summary small').text(response.data.length + ' Item(s) selected');
                        $('.cart-summary h5').text('SUBTOTAL: $' + total.toFixed(2));
                    } else {
                        cartHTML = '<div class="text-center py-3">Your cart is empty</div>';
                        $('.cart-count').text('0');
                        $('.cart-summary small').text('0 Item(s) selected');
                        $('.cart-summary h5').text('SUBTOTAL: $0.00');
                    }

                    $('.cart-list').html(cartHTML);

                    $('.header-ctn .dropdown').addClass('cart-updated');
                    setTimeout(function () {
                        $('.header-ctn .dropdown').removeClass('cart-updated');
                    }, 1000);
                }
            });
        };

        // Handle click event for removing items from the cart
        $(document).on('click', '.mini-cart-remove', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const productId = $(this).data('product-id');
            const $widget = $(this).closest('.product-widget');

            $.ajax({
                url: '<?php echo BASE_URL ?>/public/api/cart.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    action: 'remove',
                    product_id: productId
                }),
                beforeSend: function () {
                    // Thêm hiệu ứng loading
                    $widget.css('opacity', '0.5');
                },
                success: function (response) {
                    // Xóa sản phẩm khỏi giao diện với hiệu ứng
                    $widget.fadeOut(300, function () {
                        $(this).remove();
                        // Cập nhật giỏ hàng
                        updateHeaderCart();
                    });
                },
                error: function (xhr) {
                    $widget.css('opacity', '1');
                    alert('Failed to remove item: ' + (xhr.responseJSON?.message || 'Unknown error'));
                }
            });
        });

        $("<style>")
            .prop("type", "text/css")
            .html(`
                @keyframes cartPulse {
                    0% { transform: scale(1); }
                    50% { transform: scale(1.1); }
                    100% { transform: scale(1); }
                }
                .cart-updated .qty {
                    animation: cartPulse 0.5s ease-in-out 2;
                    background-color: #28a745 !important;
                }
            `)
            .appendTo("head");

        $('.dropdown').hover(
            function () {
                $(this).find('.cart-dropdown').stop(true, true).delay(200).fadeIn(300);
            },
            function () {
                $(this).find('.cart-dropdown').stop(true, true).delay(200).fadeOut(300);
            }
        );
    });
</script>