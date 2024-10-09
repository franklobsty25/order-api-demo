<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Order Demo</title>
    </head>

    <body>
        <h1>Product Purchased</h1>

        <div>
            <strong>Amount purchased: &nbsp;</strong> <span>{{ $order->amount }}</span>
            <strong>Byer:&nbsp;</strong> <span>{{ $customer->firstname }} {{ $customer->lastname }}</span>
        </div>
    </body>

</html>
