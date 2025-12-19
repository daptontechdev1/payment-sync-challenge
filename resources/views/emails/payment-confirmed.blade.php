<!DOCTYPE html>
<html>
<head>
    <title>Payment Confirmed</title>
</head>
<body>
    <h1>Payment Confirmed!</h1>
    
    <p>Thank you for your payment.</p>
    
    <p><strong>Order Reference:</strong> {{ $order->external_reference }}</p>
    <p><strong>Amount:</strong> ${{ number_format($order->amount / 100, 2) }}</p>
    
    <p>Your order is now being processed.</p>
</body>
</html>
