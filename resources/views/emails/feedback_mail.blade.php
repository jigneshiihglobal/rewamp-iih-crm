<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Customer Feedback</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <p>Hi Sanjay,</p>

    <p>You have received customer feedback:</p>

    <p>------------------------------------------------------------------------------------</p>
    <p><strong>Name:</strong> {{ $data['name'] }}<br>
    <strong>Email:</strong> {{ $data['email'] }}</p>

    <p><strong>Submitted on:</strong> {{ $data['feedback_date'] }}</p>
    <p>------------------------------------------------------------------------------------</p>

    <p>Thank You</p>
</body>
</html>
