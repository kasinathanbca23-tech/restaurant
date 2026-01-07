<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>UPI Payment Integration (Razorpay Example)</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Razorpay Checkout Script -->
  <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f5f6fa;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }
    .container {
      background: #fff;
      padding: 2rem 3rem;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.09);
      text-align: center;
    }
    button {
      background: #3399cc;
      color: #fff;
      border: none;
      padding: 1rem 2rem;
      font-size: 1.1rem;
      border-radius: 5px;
      cursor: pointer;
      margin-top: 1rem;
    }
    button:hover {
      background: #2d85b5;
    }
    .note {
      color: #666;
      font-size: 0.95em;
      margin-top: 1.5rem;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Pay with UPI (Google Pay, PhonePe, etc.)</h2>
    <p>Click the button below to pay <b>₹500</b> using any UPI app.</p>
    <button id="pay-btn">Pay Now</button>
    <div class="note">
      <b>Note:</b> This demo uses Razorpay. UPI apps like Google Pay and PhonePe will be available as payment methods.
    </div>
  </div>
  <script>
    document.getElementById('pay-btn').onclick = function(e){
      var options = {
        "key": "YOUR_KEY_ID", // TODO: Replace with your Razorpay API Key ID from dashboard
        "amount": "50000", // Amount in paise (50000 paise = ₹500)
        "currency": "INR",
        "name": "Your Company Name",
        "description": "UPI Payment",
        // "order_id": "order_DBJOWzybf0sJbb", // Use Orders API for real payments (recommended)
        "handler": function (response){
          alert("Payment successful!\nPayment ID: " + response.razorpay_payment_id);
          // TODO: You should verify the payment on your server for security.
        },
        "prefill": {
          "name": "Test User",
          "email": "test@example.com",
          "contact": "9999999999"
        },
        "theme": {
          "color": "#3399cc"
        },
        "method": {
          "upi": true,
          "card": false,
          "netbanking": false,
          "wallet": false
        },
        "modal": {
          "ondismiss": function(){
            alert("Payment popup closed.");
          }
        }
      };
      var rzp1 = new Razorpay(options);
      rzp1.open();
      e.preventDefault();
    }
  </script>
</body>
</html>