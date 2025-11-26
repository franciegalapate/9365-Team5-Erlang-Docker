<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login to RXELLENCE</title>
        <link rel="stylesheet" href="../css/login.css">
    </head>
    <body>
        <div class="logo-placeholder">
            <div class="logo-text">RXELLENCE</div>
        </div>
        <div class="main-container">
            <div class="login-card">
                <h1 class="login-title">Login to your account</h1>
                
                <form id="loginForm">
                    <!-- ID Number Field -->
                    <div class="form-group">
                        <label for="idNumber">ID Number</label>
                        <input type="text" id="idNumber" name="idNumber" placeholder="Enter your ID Number" required>
                    </div>
                    <!-- Password Field -->
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                    <!-- Message Display Area (for errors/success) -->
                    <div id="message" class="message-area" style="display: none;"></div>
                    <!-- Login Button -->
                    <button type="submit" class="login-button">LOGIN</button>
                </form>
                <p class="register-link">
                    Don't have an account? <a href="#" onclick="alert('Registration link clicked. Implement registration page here.'); return false;">Register here</a>
                </p>
            </div>
        </div>
        <script src="../js/login.js"></script>
    </body>
</html>