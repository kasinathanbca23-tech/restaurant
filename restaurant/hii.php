<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<button id="upiButton">Pay with UPI</button>
<script>
  document.getElementById('upiButton').onclick = function() {
    var options = {
      key: 'YOUR_RAZORPAY_KEY',
      amount: 30000, // Amount in paise (₹300 = 30000 paise)
      currency: 'INR',
      name: 'YourRestaurant',
      description: 'Payment for Order #123',
      handler: function(response) {
        alert('Payment successful! Payment ID: ' + response.razorpay_payment_id);
      },
      prefill: {
        email: 'customer@example.com',
        contact: '9999999999'
      },
      notes: {
        upi_id: 'kasinathanpb23@oksbi'
      },
      theme: {
        color: '#F37254'
      }
    };
    var rzp = new Razorpay(options);
    rzp.open();
  };
</script>
