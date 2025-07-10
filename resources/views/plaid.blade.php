<!DOCTYPE html>
<html>
<head>
    <title>Plaid Connect</title>
    <script src="https://cdn.plaid.com/link/v2/stable/link-initialize.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <button id="link-button">Connect Bank</button>

    <script>
            async function getLinkToken() {
            const res = await fetch('/create-link-token');
            const data = await res.json();

            const handler = Plaid.create({
                token: data.link_token,
                onSuccess: function(public_token, metadata) {
                console.log('Public Token:', public_token);

                // Send this public_token to your backend
                fetch('/get-access-token', {
                    method: 'POST',
                    headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ public_token })
                })
                .then(res => res.json())
                .then(data => console.log('Access Token Response:', data));
                },
                onExit: function(err, metadata) {
                console.log('Exited', err, metadata);
                }
            });

            document.getElementById('link-button').onclick = function() {
                handler.open();
            };
            }

            getLinkToken();
    </script>
</body>
</html>
