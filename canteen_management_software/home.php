<?php
include 'config/connection.php';

// Handle user registration
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['register'])) {
    $firstname = mysqli_real_escape_string($conn, $_POST['firstname']);
    $lastname = mysqli_real_escape_string($conn, $_POST['lastname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    
    // Check if email already exists
    $check_sql = "SELECT id FROM users WHERE email = '$email'";
    $check_query = mysqli_query($conn, $check_sql);
    
    if(mysqli_num_rows($check_query) > 0) {
        echo "<script>alert('Email already exists!');</script>";
    } else {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user
        $insert_sql = "INSERT INTO users (firstname, lastname, email, password, type) 
                      VALUES ('$firstname', '$lastname', '$email', '$hashed_password', '$type')";
        
        if(mysqli_query($conn, $insert_sql)) {
            $user_id = mysqli_insert_id($conn);
            
            // Log the user in
            $_SESSION['user'] = [
                'id' => $user_id,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'email' => $email,
                'type' => $type
            ];
            
            // Transfer guest cart to user if exists
            if(isset($_SESSION['guest_cart']) && !empty($_SESSION['guest_cart'])) {
                transferGuestCartToUser($user_id, $conn);
            }
            
            echo "<script>alert('Registration successful!');</script>";
        } else {
            echo "<script>alert('Error during registration: " . mysqli_error($conn) . "');</script>";
        }
    }
}

// Handle user login
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $query = mysqli_query($conn, $sql);
    
    if(mysqli_num_rows($query) > 0) {
        $user = mysqli_fetch_assoc($query);
        
        if(password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'firstname' => $user['firstname'],
                'lastname' => $user['lastname'],
                'email' => $user['email'],
                'type' => $user['type']
            ];
            
            // Transfer guest cart to user if exists
            if(isset($_SESSION['guest_cart']) && !empty($_SESSION['guest_cart'])) {
                transferGuestCartToUser($user['id'], $conn);
            }
            
            echo "<script>alert('Login successful!');</script>";
        } else {
            echo "<script>alert('Invalid password!');</script>";
        }
    } else {
        echo "<script>alert('User not found!');</script>";
    }
}

// Handle user logout
if (isset($_GET['logout'])) {
    unset($_SESSION['user']);
    session_destroy();
    echo "<script>alert('Logged out successfully!'); window.location.href = 'home.php';</script>";
}

// Function to transfer guest cart to user
function transferGuestCartToUser($user_id, $conn) {
    // Get or create user cart
    $cart_sql = "SELECT id FROM cart WHERE user_id = '$user_id'";
    $cart_query = mysqli_query($conn, $cart_sql);
    
    if(mysqli_num_rows($cart_query) > 0) {
        $cart = mysqli_fetch_assoc($cart_query);
        $cart_id = $cart['id'];
    } else {
        $cart_sql = "INSERT INTO cart (user_id) VALUES ('$user_id')";
        if(mysqli_query($conn, $cart_sql)) {
            $cart_id = mysqli_insert_id($conn);
        } else {
            return false;
        }
    }
    
    // Transfer guest cart items to user cart
    foreach($_SESSION['guest_cart'] as $pid => $item) {
        // Check if item already exists in cart
        $item_sql = "SELECT id, qty FROM cart_items WHERE cart_id = '$cart_id' AND pid = '$pid'";
        $item_query = mysqli_query($conn, $item_sql);
        
        if(mysqli_num_rows($item_query) > 0) {
            // Update quantity
            $existing_item = mysqli_fetch_assoc($item_query);
            $new_qty = $existing_item['qty'] + $item['qty'];
            $update_sql = "UPDATE cart_items SET qty = '$new_qty' WHERE id = '{$existing_item['id']}'";
            mysqli_query($conn, $update_sql);
        } else {
            // Add new item to cart
            $insert_sql = "INSERT INTO cart_items (cart_id, pid, qty) VALUES ('$cart_id', '$pid', '{$item['qty']}')";
            mysqli_query($conn, $insert_sql);
        }
    }
    
    // Clear guest cart
    unset($_SESSION['guest_cart']);
    return true;
}

// Handle AJAX requests for cart operations
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['action'])) {
    $response = ['success' => false, 'message' => ''];
    
    switch($_POST['action']) {
        case 'add_to_cart':
            $pid = $_POST['pid'];
            
            // Check if user is logged in
            if(isset($_SESSION['user'])) {
                $uid = $_SESSION['user']['id'];
                
                // Check if user has an active cart
                $cart_sql = "SELECT id FROM cart WHERE user_id = '$uid'";
                $cart_query = mysqli_query($conn, $cart_sql);
                
                if(mysqli_num_rows($cart_query) > 0) {
                    $cart = mysqli_fetch_assoc($cart_query);
                    $cart_id = $cart['id'];
                } else {
                    // Create new cart
                    $cart_sql = "INSERT INTO cart (user_id) VALUES ('$uid')";
                    if(mysqli_query($conn, $cart_sql)) {
                        $cart_id = mysqli_insert_id($conn);
                    } else {
                        $response['message'] = 'Error creating cart: ' . mysqli_error($conn);
                        echo json_encode($response);
                        exit;
                    }
                }
                
                // Check if item already exists in cart
                $item_sql = "SELECT id, qty FROM cart_items WHERE cart_id = '$cart_id' AND pid = '$pid'";
                $item_query = mysqli_query($conn, $item_sql);
                
                if(mysqli_num_rows($item_query) > 0) {
                    // Update quantity
                    $item = mysqli_fetch_assoc($item_query);
                    $new_qty = $item['qty'] + 1;
                    $update_sql = "UPDATE cart_items SET qty = '$new_qty' WHERE id = '{$item['id']}'";
                    
                    if(mysqli_query($conn, $update_sql)) {
                        $response['success'] = true;
                        $response['message'] = 'Item quantity updated in cart';
                    } else {
                        $response['message'] = 'Error updating cart: ' . mysqli_error($conn);
                    }
                } else {
                    // Add new item to cart
                    $insert_sql = "INSERT INTO cart_items (cart_id, pid, qty) VALUES ('$cart_id', '$pid', 1)";
                    
                    if(mysqli_query($conn, $insert_sql)) {
                        $response['success'] = true;
                        $response['message'] = 'Item added to cart';
                    } else {
                        $response['message'] = 'Error adding to cart: ' . mysqli_error($conn);
                    }
                }
            } else {
                // Guest user - store in session
                if(!isset($_SESSION['guest_cart'])) {
                    $_SESSION['guest_cart'] = [];
                }
                
                if(isset($_SESSION['guest_cart'][$pid])) {
                    $_SESSION['guest_cart'][$pid]['qty'] += 1;
                } else {
                    $_SESSION['guest_cart'][$pid] = [
                        'qty' => 1,
                        'name' => $_POST['name'],
                        'price' => $_POST['price'],
                        'image' => $_POST['image']
                    ];
                }
                
                $response['success'] = true;
                $response['message'] = 'Item added to cart (Guest)';
                $response['guest'] = true;
            }
            break;
            
        case 'update_cart_item':
            if(isset($_SESSION['user'])) {
                $cart_item_id = $_POST['cart_item_id'];
                $qty = $_POST['qty'];
                
                if($qty <= 0) {
                    // Remove item if quantity is 0 or less
                    $delete_sql = "DELETE FROM cart_items WHERE id = '$cart_item_id'";
                    if(mysqli_query($conn, $delete_sql)) {
                        $response['success'] = true;
                        $response['message'] = 'Item removed from cart';
                    } else {
                        $response['message'] = 'Error removing item: ' . mysqli_error($conn);
                    }
                } else {
                    // Update quantity
                    $update_sql = "UPDATE cart_items SET qty = '$qty' WHERE id = '$cart_item_id'";
                    if(mysqli_query($conn, $update_sql)) {
                        $response['success'] = true;
                        $response['message'] = 'Cart updated';
                    } else {
                        $response['message'] = 'Error updating cart: ' . mysqli_error($conn);
                    }
                }
            } else {
                // Guest user
                $pid = $_POST['pid'];
                $qty = $_POST['qty'];
                
                if($qty <= 0) {
                    unset($_SESSION['guest_cart'][$pid]);
                    $response['success'] = true;
                    $response['message'] = 'Item removed from cart (Guest)';
                } else {
                    $_SESSION['guest_cart'][$pid]['qty'] = $qty;
                    $response['success'] = true;
                    $response['message'] = 'Cart updated (Guest)';
                }
                $response['guest'] = true;
            }
            break;
            
        case 'remove_from_cart':
            if(isset($_SESSION['user'])) {
                $cart_item_id = $_POST['cart_item_id'];
                $delete_sql = "DELETE FROM cart_items WHERE id = '$cart_item_id'";
                
                if(mysqli_query($conn, $delete_sql)) {
                    $response['success'] = true;
                    $response['message'] = 'Item removed from cart';
                } else {
                    $response['message'] = 'Error removing item: ' . mysqli_error($conn);
                }
            } else {
                // Guest user
                $pid = $_POST['pid'];
                unset($_SESSION['guest_cart'][$pid]);
                $response['success'] = true;
                $response['message'] = 'Item removed from cart (Guest)';
                $response['guest'] = true;
            }
            break;
            
        case 'get_cart_count':
            if(isset($_SESSION['user'])) {
                $uid = $_SESSION['user']['id'];
                $count_sql = "SELECT SUM(ci.qty) as total_items 
                             FROM cart_items ci 
                             JOIN cart c ON ci.cart_id = c.id 
                             WHERE c.user_id = '$uid'";
                $count_query = mysqli_query($conn, $count_sql);
                $count_result = mysqli_fetch_assoc($count_query);
                
                $response['success'] = true;
                $response['count'] = $count_result['total_items'] ? $count_result['total_items'] : 0;
            } else {
                // Guest user
                $count = 0;
                if(isset($_SESSION['guest_cart'])) {
                    foreach($_SESSION['guest_cart'] as $item) {
                        $count += $item['qty'];
                    }
                }
                $response['success'] = true;
                $response['count'] = $count;
                $response['guest'] = true;
            }
            break;

        case 'add_order':
            if(!isset($_SESSION['user'])) {
                $response['message'] = 'Please login to place an order!';
                echo json_encode($response);
                exit;
            }
            
            $uid = $_SESSION['user']['id'];
            
            // Get active cart
            $cart_sql = "SELECT id FROM cart WHERE user_id = '$uid'";
            $cart_query = mysqli_query($conn, $cart_sql);
            
            if(mysqli_num_rows($cart_query) === 0) {
                $response['message'] = 'No active cart found!';
                echo json_encode($response);
                exit;
            }
            
            $cart = mysqli_fetch_assoc($cart_query);
            $cart_id = $cart['id'];
            
            // Get cart items
            $items_sql = "SELECT ci.pid, ci.qty, i.price, i.name
                        FROM cart_items ci 
                        JOIN items i ON ci.pid = i.id 
                        WHERE ci.cart_id = '$cart_id'";
            $items_query = mysqli_query($conn, $items_sql);
            
            if(mysqli_num_rows($items_query) === 0) {
                $response['message'] = 'No items in cart!';
                echo json_encode($response);
                exit;
            }
            
            // Start transaction
            mysqli_begin_transaction($conn);
            
            try {
                // Calculate total amount
                $total_amount = 0;
                $order_items = [];
                
                while($item = mysqli_fetch_assoc($items_query)) {
                    $item_total = $item['price'] * $item['qty'];
                    $total_amount += $item_total;
                    $order_items[] = $item;
                }
                
                // Insert into order table
                $order_sql = "INSERT INTO `order` (uid, amt, status) 
                            VALUES ('$uid', '$total_amount', 'Received')";
                
                if(!mysqli_query($conn, $order_sql)) {
                    throw new Exception('Error creating order: ' . mysqli_error($conn));
                }
                
                $order_id = mysqli_insert_id($conn);
                
                // Insert into order_items table
                foreach($order_items as $item) {
                    $pid = $item['pid'];
                    $qty = $item['qty'];
                    $price = $item['price'];
                    $amt = $price * $qty;
                    
                    $order_item_sql = "INSERT INTO `order_items` (order_id, product_id, quantity, price, total) 
                                      VALUES ('$order_id', '$pid', '$qty', '$price', '$amt')";
                    
                    if(!mysqli_query($conn, $order_item_sql)) {
                        throw new Exception('Error creating order item: ' . mysqli_error($conn));
                    }
                }
                
                // Insert into payments table
                $payment_sql = "INSERT INTO `payments` (order_id, amount, payment_method, status) 
                               VALUES ('$order_id', '$total_amount', 'Cash', 'Completed')";
                
                if(!mysqli_query($conn, $payment_sql)) {
                    throw new Exception('Error creating payment record: ' . mysqli_error($conn));
                }
                
                // Clear cart
                $deleteCartItems = "DELETE FROM cart_items WHERE cart_id = '$cart_id'";
                if(!mysqli_query($conn, $deleteCartItems)) {
                    throw new Exception('Error clearing cart items: ' . mysqli_error($conn));
                }
                
                $deleteCart = "DELETE FROM cart WHERE id = '$cart_id'";
                if(!mysqli_query($conn, $deleteCart)) {
                    throw new Exception('Error clearing cart: ' . mysqli_error($conn));
                }
                
                // Commit transaction
                mysqli_commit($conn);
                
                $response['success'] = true;
                $response['message'] = 'Order placed successfully!';
                $response['order_id'] = $order_id;
                
            } catch (Exception $e) {
                // Rollback transaction on error
                mysqli_rollback($conn);
                $response['message'] = $e->getMessage();
            }
            break;
    }
    
    echo json_encode($response);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Hunger Bar Café - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #8B4513;
            --secondary: #D2691E;
            --accent: #F4A460;
            --light: #FFF8DC;
            --dark: #654321;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.8rem;
        }
        
        .nav-link {
            color: white !important;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .nav-link:hover {
            color: var(--light) !important;
            transform: translateY(-2px);
        }
        
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1414235077428-338989a2e8c0?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            text-align: center;
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .hero-subtitle {
            font-size: 1.5rem;
            margin-bottom: 30px;
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            padding: 10px 25px;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary);
            border-color: var(--secondary);
        }
        
        .section-title {
            color: var(--dark);
            font-weight: 700;
            margin-bottom: 40px;
            position: relative;
            padding-bottom: 15px;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 80px;
            height: 3px;
            background-color: var(--accent);
        }
        
        .menu-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 30px;
        }
        
        .menu-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .menu-card img {
            height: 200px;
            object-fit: cover;
        }
        
        .card-title {
            color: var(--dark);
            font-weight: 700;
        }
        
        .card-price {
            color: var(--primary);
            font-weight: 700;
            font-size: 1.2rem;
        }
        
        .add-to-cart-btn {
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 25px;
            padding: 8px 20px;
            transition: all 0.3s;
        }
        
        .add-to-cart-btn:hover {
            background-color: var(--secondary);
            transform: scale(1.05);
        }
        
        .cart-btn {
            background-color: var(--accent);
            color: var(--dark);
            border: none;
            border-radius: 25px;
            padding: 8px 20px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .cart-btn:hover {
            background-color: var(--secondary);
            color: white;
        }
        
        .cart-count {
            background-color: var(--primary);
            color: white;
            border-radius: 50%;
            padding: 2px 8px;
            font-size: 0.8rem;
            margin-left: 5px;
        }
        
        .user-info-bar {
            background-color: white;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .user-details {
            display: flex;
            align-items: center;
        }
        
        .user-avatar {
            background-color: var(--accent);
            color: var(--dark);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.5rem;
        }
        
        .cart-modal .modal-content {
            border-radius: 15px;
            border: none;
        }
        
        .cart-modal .modal-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: 15px 15px 0 0;
        }
        
        .empty-cart {
            padding: 40px 0;
            color: var(--dark);
        }
        
        .cart-item {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }
        
        .cart-item:last-child {
            border-bottom: none;
        }
        
        .cart-item-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
        }
        
        .cart-qty-controls {
            display: flex;
            align-items: center;
        }
        
        .cart-qty-btn {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        
        .cart-qty-input {
            width: 50px;
            text-align: center;
            border: 1px solid #ddd;
            height: 30px;
            margin: 0 5px;
        }
        
        .remove-from-cart {
            color: #dc3545;
            background: none;
            border: none;
            font-size: 1.2rem;
        }
        
        footer {
            background-color: var(--dark);
            color: white;
            padding: 40px 0 20px;
            margin-top: 50px;
        }
        
        .footer-links a {
            color: var(--light);
            text-decoration: none;
            margin-right: 20px;
            transition: color 0.3s;
        }
        
        .footer-links a:hover {
            color: var(--accent);
        }
        
        .social-icons a {
            color: white;
            font-size: 1.5rem;
            margin-right: 15px;
            transition: color 0.3s;
        }
        
        .social-icons a:hover {
            color: var(--accent);
        }
        
        .auth-modal .modal-content {
            border-radius: 15px;
            border: none;
        }
        
        .auth-modal .modal-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: 15px 15px 0 0;
        }
        
        .auth-tabs .nav-link {
            color: var(--dark) !important;
            font-weight: 600;
        }
        
        .auth-tabs .nav-link.active {
            color: var(--primary) !important;
            border-bottom: 3px solid var(--primary);
        }
        
        .form-control {
            border-radius: 10px;
            padding: 10px 15px;
            border: 1px solid #ddd;
        }
        
        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 0.2rem rgba(139, 69, 19, 0.25);
        }
        
        .category-filter {
            margin-bottom: 30px;
        }
        
        .category-btn {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 25px;
            padding: 8px 20px;
            margin-right: 10px;
            margin-bottom: 10px;
            transition: all 0.3s;
        }
        
        .category-btn.active, .category-btn:hover {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="home.php">
                <i class="bi bi-cup-hot-fill me-2"></i>The Hunger Bar Café
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="home.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#menu">Menu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <?php if(isset($_SESSION['user'])): ?>
                        <div class="dropdown me-3">
                            <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-1"></i>
                                <?php echo $_SESSION['user']['firstname']; ?>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="orders.php">My Orders</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="?logout=1">Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <button class="btn btn-outline-light me-2" data-bs-toggle="modal" data-bs-target="#authModal">
                            <i class="bi bi-person-circle me-1"></i>Login
                        </button>
                    <?php endif; ?>
                    <button class="btn cart-btn" data-bs-toggle="modal" data-bs-target="#cartModal">
                        <i class="bi bi-cart4"></i>
                        Cart
                        <span id="cartCount" class="cart-count">0</span>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1 class="hero-title">Welcome to The Hunger Bar Café</h1>
            <p class="hero-subtitle">Serving delicious food and beverages with love and care</p>
            <a href="#menu" class="btn btn-primary btn-lg">Explore Our Menu</a>
        </div>
    </section>

    <!-- Menu Section -->
    <section id="menu" class="py-5">
        <div class="container">
            <h2 class="section-title text-center">Our Delicious Menu</h2>
            
            <!-- Category Filters -->
            <div class="category-filter text-center">
                <button class="category-btn active" data-category="all">All Items</button>
                <?php
                $category_sql = "SELECT DISTINCT category FROM items WHERE status = 'Active'";
                $category_query = mysqli_query($conn, $category_sql);
                while($category = mysqli_fetch_assoc($category_query)): 
                ?>
                    <button class="category-btn" data-category="<?php echo $category['category']; ?>">
                        <?php echo $category['category']; ?>
                    </button>
                <?php endwhile; ?>
            </div>
            
            <!-- Menu Items -->
            <div class="row g-4" id="menuItems">
                <?php 
                $sql = "SELECT * FROM items WHERE status = 'Active'";
                $query = mysqli_query($conn, $sql);
                while($row = mysqli_fetch_assoc($query)):
                ?>
                    <div class="col-lg-4 col-md-6 menu-item" data-category="<?php echo $row['category']; ?>">
                        <div class="card menu-card shadow-sm h-100">
                            <img src="<?php echo $row['image']; ?>" class="card-img-top" alt="<?php echo $row['name']; ?>">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo $row['name']; ?></h5>
                                <p class="card-category"><strong>Category:</strong> <?php echo $row['category']; ?></p>
                                <p class="card-price">₹ <?php echo $row['price']; ?></p>
                                <p class="card-remarks"><?php echo $row['remarks']; ?></p>
                                <button class="btn add-to-cart-btn mt-auto addToCart"
                                        data-pid="<?php echo $row['id']; ?>"
                                        data-name="<?php echo $row['name']; ?>"
                                        data-price="<?php echo $row['price']; ?>"
                                        data-image="<?php echo $row['image']; ?>">
                                    <i class="bi bi-cart-plus"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-5 bg-light">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="section-title">About The Hunger Bar Café</h2>
                    <p class="lead">We are passionate about serving high-quality food and beverages that satisfy your cravings and fuel your day.</p>
                    <p>Our chefs use only the freshest ingredients to create mouth-watering dishes that will keep you coming back for more. Whether you're grabbing a quick bite between classes or enjoying a leisurely meal with friends, The Hunger Bar Café is your perfect destination.</p>
                    <ul class="list-unstyled">
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i> Fresh ingredients daily</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i> Friendly and efficient service</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i> Comfortable and welcoming atmosphere</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i> Affordable prices for students</li>
                    </ul>
                </div>
                <div class="col-lg-6">
                    <img src="https://images.unsplash.com/photo-1554118811-1e0d58224f24?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2047&q=80" 
                         class="img-fluid rounded shadow" alt="Café Interior">
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-5">
        <div class="container">
            <h2 class="section-title text-center">Contact Us</h2>
            <div class="row">
                <div class="col-md-4 text-center mb-4">
                    <div class="p-4 bg-white rounded shadow-sm">
                        <i class="bi bi-geo-alt-fill text-primary fs-1"></i>
                        <h4 class="mt-3">Address</h4>
                        <p>College Campus, Main Building<br>New Delhi, 110001</p>
                    </div>
                </div>
                <div class="col-md-4 text-center mb-4">
                    <div class="p-4 bg-white rounded shadow-sm">
                        <i class="bi bi-telephone-fill text-primary fs-1"></i>
                        <h4 class="mt-3">Phone</h4>
                        <p>+91 9876543210<br>+91 9876543211</p>
                    </div>
                </div>
                <div class="col-md-4 text-center mb-4">
                    <div class="p-4 bg-white rounded shadow-sm">
                        <i class="bi bi-clock-fill text-primary fs-1"></i>
                        <h4 class="mt-3">Opening Hours</h4>
                        <p>Monday - Friday: 8am - 8pm<br>Saturday - Sunday: 9am - 6pm</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h4><i class="bi bi-cup-hot-fill me-2"></i>The Hunger Bar Café</h4>
                    <p>Your favorite campus destination for delicious food and refreshing beverages.</p>
                    <div class="social-icons">
                        <a href="#"><i class="bi bi-facebook"></i></a>
                        <a href="#"><i class="bi bi-instagram"></i></a>
                        <a href="#"><i class="bi bi-twitter"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <h5>Quick Links</h5>
                    <div class="footer-links">
                        <a href="home.php">Home</a><br>
                        <a href="#menu">Menu</a><br>
                        <a href="#about">About Us</a><br>
                        <a href="#contact">Contact</a>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <h5>Contact Info</h5>
                    <p><i class="bi bi-geo-alt me-2"></i> College Campus, New Delhi</p>
                    <p><i class="bi bi-telephone me-2"></i> +91 9876543210</p>
                    <p><i class="bi bi-envelope me-2"></i> info@hungerbarcafe.com</p>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center">
                <p>&copy; <?php echo date('Y'); ?> The Hunger Bar Café. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Authentication Modal -->
    <div class="modal fade auth-modal" id="authModal" tabindex="-1" aria-labelledby="authModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="authModalLabel">Welcome to The Hunger Bar Café</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs auth-tabs mb-3" id="authTabs">
                        <li class="nav-item">
                            <a class="nav-link active" id="login-tab" data-bs-toggle="tab" href="#login">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="register-tab" data-bs-toggle="tab" href="#register">Register</a>
                        </li>
                    </ul>
                    <div class="tab-content" id="authTabsContent">
                        <!-- Login Form -->
                        <div class="tab-pane fade show active" id="login">
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="loginEmail" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="loginEmail" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="loginPassword" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="loginPassword" name="password" required>
                                </div>
                                <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
                            </form>
                        </div>
                        
                        <!-- Register Form -->
                        <div class="tab-pane fade" id="register">
                            <form method="POST" action="">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="firstName" class="form-label">First Name</label>
                                        <input type="text" class="form-control" id="firstName" name="firstname" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="lastName" class="form-label">Last Name</label>
                                        <input type="text" class="form-control" id="lastName" name="lastname" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="registerEmail" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="registerEmail" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="registerPassword" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="registerPassword" name="password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="userType" class="form-label">User Type</label>
                                    <select class="form-control" id="userType" name="type" required>
                                        <option value="student">Regular Student</option>
                                        <option value="customer">Customer</option>
                                    </select>
                                </div>
                                <button type="submit" name="register" class="btn btn-primary w-100">Register</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cart Modal -->
    <div class="modal fade cart-modal" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cartModalLabel"><i class="bi bi-cart4 me-2"></i>Your Cart</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="cartContent">
                    <!-- Cart content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Continue Shopping</button>
                    <?php if(isset($_SESSION['user'])): ?>
                        <button class="btn btn-success" id="proceedToCheckout">Proceed to Checkout</button>
                    <?php else: ?>
                        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#authModal" data-bs-dismiss="modal">Login to Checkout</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Checkout Modal -->
    <div class="modal fade checkout-modal" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-3 shadow">
                <div class="modal-header">
                    <h5 class="modal-title" id="checkoutModalLabel">Complete Your Payment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="paymentForm">
                    <div class="modal-body">
                        <div id="checkoutItems">
                            <!-- Checkout items will be loaded here -->
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Total Amount (₹)</label>
                            <input type="number" class="form-control" id="totalAmount" name="totalAmount" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <select class="form-control" id="paymentMethod" name="paymentMethod">
                                <option value="Cash">Cash</option>
                                <option value="Card">Card</option>
                                <option value="UPI">UPI</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Pay Now</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Load cart count on page load
            updateCartCount();
            
            // Category filter functionality
            $('.category-btn').click(function() {
                $('.category-btn').removeClass('active');
                $(this).addClass('active');
                
                const category = $(this).data('category');
                
                if(category === 'all') {
                    $('.menu-item').show();
                } else {
                    $('.menu-item').hide();
                    $(`.menu-item[data-category="${category}"]`).show();
                }
            });
            
            // Add to cart functionality
            $('.addToCart').click(function() {
                const pid = $(this).data('pid');
                const name = $(this).data('name');
                const price = $(this).data('price');
                const image = $(this).data('image');
                
                $.ajax({
                    url: '',
                    type: 'POST',
                    data: {
                        action: 'add_to_cart',
                        pid: pid,
                        name: name,
                        price: price,
                        image: image
                    },
                    dataType: 'json',
                    success: function(response) {
                        if(response.success) {
                            alert('Item added to cart!');
                            updateCartCount();
                            
                            // If guest user and trying to checkout, show login modal
                            if(response.guest && $('#proceedToCheckout').is(':visible')) {
                                $('#cartModal').modal('hide');
                                $('#authModal').modal('show');
                            }
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error adding item to cart');
                    }
                });
            });

            // Payment form submission
            $('#paymentForm').submit(function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: '',
                    type: 'POST',
                    data: {
                        action: 'add_order'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if(response.success) {
                            alert('Order Placed Successfully! Order ID: ' + response.order_id);
                            
                            // Clear modals
                            $('#cartModal').modal('hide');
                            $('#checkoutModal').modal('hide');
                            
                            // Clear cart and update count
                            loadCartContent();
                            updateCartCount();
                            
                            // Redirect to orders page
                            window.location.href = 'orders.php';
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Error placing order: ' + error);
                    }
                });
            });
            
            // Update cart count
            function updateCartCount() {
                $.ajax({
                    url: '',
                    type: 'POST',
                    data: {
                        action: 'get_cart_count'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if(response.success) {
                            $('#cartCount').text(response.count);
                        }
                    }
                });
            };
            
            // Load cart content when modal is shown
            $('#cartModal').on('show.bs.modal', function() {
                loadCartContent();
            });
            
            // Load cart content
            function loadCartContent() {
                $.ajax({
                    url: 'get_cart_content.php',
                    type: 'GET',
                    dataType: 'html',
                    success: function(response) {
                        $('#cartContent').html(response);
                        updateCartCount();
                    },
                    error: function() {
                        $('#cartContent').html(`
                            <div class="empty-cart text-center py-4">
                                <i class="bi bi-cart-x" style="font-size: 3rem; color: #8B7355;"></i>
                                <h5 class="mt-3" style="color: var(--primary);">Error loading cart</h5>
                                <p class="text-muted">Please try again</p>
                            </div>
                        `);
                    }
                });
            }
            
            // Cart quantity controls
            $(document).on('click', '.cart-qty-plus', function() {
                const cartItemId = $(this).data('id');
                const pid = $(this).data('pid');
                const currentQty = parseInt($(this).siblings('.cart-qty-input').val());
                const newQty = currentQty + 1;
                
                updateCartItem(cartItemId, pid, newQty);
            });
            
            $(document).on('click', '.cart-qty-minus', function() {
                const cartItemId = $(this).data('id');
                const pid = $(this).data('pid');
                const currentQty = parseInt($(this).siblings('.cart-qty-input').val());
                const newQty = currentQty - 1;
                
                if(newQty >= 0) {
                    updateCartItem(cartItemId, pid, newQty);
                }
            });
            
            // Cart quantity input change
            $(document).on('change', '.cart-qty-input', function() {
                const cartItemId = $(this).data('id');
                const pid = $(this).data('pid');
                const newQty = parseInt($(this).val());
                
                if(newQty >= 0) {
                    updateCartItem(cartItemId, pid, newQty);
                } else {
                    $(this).val(0);
                }
            });
            
            // Remove item from cart
            $(document).on('click', '.remove-from-cart', function() {
                const cartItemId = $(this).data('id');
                const pid = $(this).data('pid');
                
                $.ajax({
                    url: '',
                    type: 'POST',
                    data: {
                        action: 'remove_from_cart',
                        cart_item_id: cartItemId,
                        pid: pid
                    },
                    dataType: 'json',
                    success: function(response) {
                        if(response.success) {
                            loadCartContent();
                            updateCartCount();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error removing item from cart');
                    }
                });
            });
            
            // Update cart item quantity
            function updateCartItem(cartItemId, pid, qty) {
                $.ajax({
                    url: '',
                    type: 'POST',
                    data: {
                        action: 'update_cart_item',
                        cart_item_id: cartItemId,
                        pid: pid,
                        qty: qty
                    },
                    dataType: 'json',
                    success: function(response) {
                        if(response.success) {
                            loadCartContent();
                            updateCartCount();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error updating cart');
                    }
                });
            }
            
            // Proceed to checkout
            $('#proceedToCheckout').click(function() {
                // Hide cart modal
                $('#cartModal').modal('hide');
                
                // Prepare checkout items
                let checkoutItemsHtml = '';
                let totalAmount = 0;
                
                // Get cart items for checkout
                $.ajax({
                    url: 'get_cart_content.php?checkout=1',
                    type: 'GET',
                    dataType: 'html',
                    success: function(response) {
                        $('#checkoutItems').html(response);
                        
                        // Calculate total amount
                        $('.checkout-item').each(function() {
                            const itemTotal = parseFloat($(this).data('total'));
                            totalAmount += itemTotal;
                        });
                        
                        $('#totalAmount').val(totalAmount.toFixed(2));
                        
                        // Show checkout modal
                        $('#checkoutModal').modal('show');
                    },
                    error: function() {
                        alert('Error loading checkout information');
                    }
                });
            });
        });
    </script>
</body>
</html>