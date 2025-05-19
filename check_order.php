<!DOCTYPE html>
<html>
<head>
  <title>Check Order Status</title>
  <style>
    body { font-family: Arial; padding: 20px; }
    input, button { padding: 10px; margin: 5px 0; width: 100%; max-width: 400px; }
    .result { margin-top: 20px; background: #f0f0f0; padding: 15px; border-radius: 8px; white-space: pre-wrap; }
  </style>
</head>
<body>
  <h2>Check Your Order Status</h2>
  <form method="post">
    <input type="text" name="order_id" placeholder="Enter Your Order ID" required>
    <button type="submit">Check Status</button>
  </form>

  <?php
  if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $orderId = strtoupper(trim($_POST["order_id"]));
      $files = [
          "likes_orders.txt",
          "views_orders.txt",
          "comments_orders.txt"
      ];

      $found = false;

      foreach ($files as $file) {
          if (file_exists($file)) {
              $content = file_get_contents($file);
              $orders = explode("-----------------------------", $content);
              foreach ($orders as $order) {
                  if (strpos($order, "Order ID: $orderId") !== false) {
                      echo "<div class='result'><strong>Order Found in:</strong> $file<br><br>" . htmlentities(trim($order)) . "</div>";
                      $found = true;
                      break 2;
                  }
              }
          }
      }

      if (!$found) {
          echo "<div class='result'>❌ Order ID not found. Please check and try again.</div>";
      }
  }
  ?>
</body>
</html>
