<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My Orders - The Hunger Bar Café</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/studentStyle.css">
</head>
<body class="d-flex flex-column min-vh-100">
     <!-- Sidebar -->
  <div class="sidebar">
    <div class="sidebar-brand">
      <img src="assets/logo/the_hunger_bar_logo.png" alt="The Hunger Bar Logo">
      <span>The Hunger Bar Café</span>
    </div>

    <!-- User Info Section -->
    <div class="sidebar-user-info">
      <div class="user-avatar-sidebar">
        <i class="bi bi-person-circle"></i>
      </div>
      <div class="user-info-text">
        <div class="user-name"><?php echo $_SESSION['user']['firstname'] . ' ' . $_SESSION['user']['lastname']; ?></div>
        <div class="user-role"><?php echo ucfirst($_SESSION['user']['type']); ?> Student</div>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="quick-stats">
      <div class="stat-item">
        <span>Today's Orders</span>
        <span class="stat-value" id="todayOrders">0</span>
      </div>
      <div class="stat-item">
        <span>Pending</span>
        <span class="stat-value" id="pendingOrders">0</span>
      </div>
      <div class="stat-item">
        <span>Cart Items</span>
        <span class="stat-value" id="cartItems">0</span>
      </div>
    </div>

    <!-- Current Balance (if you implement wallet system) -->
    <div class="current-balance">
      <div class="balance-label">Current Balance</div>
      <div class="balance-amount">₹<span id="userBalance">0.00</span></div>
      <div class="balance-action" onclick="showAddMoneyModal()">Add Money</div>
    </div>

    <ul class="nav flex-column sidebar-nav">
      <li class="nav-item">
        <a class="nav-link" href="index.php">
          <i class="bi bi-grid-1x2-fill"></i>
          <span>Dashboard</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="menu.php">
          <i class="bi bi-menu-button-wide-fill"></i>
          <span>Menu</span>
          <span class="nav-badge" id="cartCountBadge">0</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link active" href="orders.php">
          <i class="bi bi-cart-check"></i>
          <span>My Orders</span>
          <span class="nav-badge" id="ordersBadge">0</span>
        </a>
      </li>
      
      <div class="sidebar-divider"></div>
      
      <!-- Additional Industry Standard Features -->
      <li class="nav-item">
        <a class="nav-link" href="favorites.php">
          <i class="bi bi-heart-fill"></i>
          <span>Favorites</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="order_tracking.php">
          <i class="bi bi-geo-alt-fill"></i>
          <span>Track Order</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="student_profile.php">
          <i class="bi bi-person-fill"></i>
          <span>My Profile</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="payment_methods.php">
          <i class="bi bi-credit-card-fill"></i>
          <span>Payment Methods</span>
        </a>
      </li>
      
      <div class="sidebar-divider"></div>
      
      <li class="nav-item">
        <a class="nav-link" href="support.php">
          <i class="bi bi-headset"></i>
          <span>Support</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="notifications.php">
          <i class="bi bi-bell-fill"></i>
          <span>Notifications</span>
          <span class="nav-badge" id="notificationsBadge">3</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="settings.php">
          <i class="bi bi-gear-fill"></i>
          <span>Settings</span>
        </a>
      </li>
    </ul>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
      <div class="d-flex justify-content-between align-items-center">
        <small class="text-white-50">v1.2.0</small>
        <a href="logout.php" class="btn btn-sm btn-outline-light">
          <i class="bi bi-box-arrow-right"></i>
        </a>
      </div>
    </div>
  </div>

  <!-- Add Money Modal (Example) -->
  <div class="modal fade" id="addMoneyModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add Money to Wallet</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="d-grid gap-2">
            <button class="btn btn-outline-primary" onclick="addMoney(100)">₹100</button>
            <button class="btn btn-outline-primary" onclick="addMoney(200)">₹200</button>
            <button class="btn btn-outline-primary" onclick="addMoney(500)">₹500</button>
            <button class="btn btn-outline-primary" onclick="addMoney(1000)">₹1000</button>
          </div>
          <div class="mt-3">
            <input type="number" class="form-control" placeholder="Custom amount" id="customAmount">
            <button class="btn btn-primary w-100 mt-2" onclick="addCustomMoney()">Add Custom Amount</button>
          </div>
        </div>
      </div>
    </div>
  </div>
   <script>
    // Function to update sidebar stats
    function updateSidebarStats() {
      // These would typically come from your backend
      fetch('get_user_stats.php')
        .then(response => response.json())
        .then(data => {
          document.getElementById('todayOrders').textContent = data.today_orders || 0;
          document.getElementById('pendingOrders').textContent = data.pending_orders || 0;
          document.getElementById('cartItems').textContent = data.cart_items || 0;
          document.getElementById('userBalance').textContent = data.balance || '0.00';
          document.getElementById('cartCountBadge').textContent = data.cart_items || 0;
          document.getElementById('ordersBadge').textContent = data.pending_orders || 0;
        })
        .catch(error => {
          console.error('Error fetching stats:', error);
        });
    }

    // Show add money modal
    function showAddMoneyModal() {
      const modal = new bootstrap.Modal(document.getElementById('addMoneyModal'));
      modal.show();
    }

    // Add money functions
    function addMoney(amount) {
      // Implement add money logic
      console.log('Adding money:', amount);
      // You would typically make an AJAX call here
    }

    function addCustomMoney() {
      const amount = document.getElementById('customAmount').value;
      if (amount > 0) {
        addMoney(amount);
      }
    }

    // Initialize sidebar stats on page load
    document.addEventListener('DOMContentLoaded', function() {
      updateSidebarStats();
      
      // Update stats every 30 seconds
      setInterval(updateSidebarStats, 30000);
    });

    // Update cart count when items are added (if you have this functionality)
    document.addEventListener('cartUpdated', function() {
      updateSidebarStats();
    });
  </script>