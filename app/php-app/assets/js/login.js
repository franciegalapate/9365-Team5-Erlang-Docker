const API_URL = '/login_handler.php';

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('loginForm');
    const messageDiv = document.getElementById('message');

    const displayMessage = (message, type) => {
        messageDiv.textContent = message;
        messageDiv.className = `message-area message-${type}`;
        messageDiv.style.display = 'block';
    };

    const hideMessage = () => {
        messageDiv.style.display = 'none';
        messageDiv.textContent = '';
    };

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        hideMessage();

        const idNumber = document.getElementById('idNumber').value;
        const password = document.getElementById('password').value;
        const loginButton = form.querySelector('button[type="submit"]');

        // Disable button and show loading state
        loginButton.textContent = 'Logging In...';
        loginButton.disabled = true;

        try {
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ idNumber, password })
            });

            // Check if the network response was OK
            if (!response.ok) {
                const errorText = await response.text();
                console.error(`Fetch error: Server responded with status ${response.status}. Response: ${errorText}`);
                
                throw new Error(`Server error or network issue (Status: ${response.status})`);
            }

            const result = await response.json();
            
            if (result.status === 'success') {
                displayMessage('Login successful! Redirecting...', 'success');
                
                let redirectUrl = 'assets/html/unauthorized.html'; // Default fallback
                
                // Use the account_type from the PHP response
                switch (result.account_type) {
                    case 'admin':
                        redirectUrl = '../html/admin_dashboard.html';    // Update to correct admin dashboard
                        break;
                    case 'doctor':
                        redirectUrl = '../html/doctor_view_records.php';   // Update to correct doctor dashboard
                        break;
                    case 'pharmacist':
                        redirectUrl = '../html/pharmacist_search_prescription.php';   // Update to correct pharmacist dashboard
                        break;
                    case 'patient':
                        redirectUrl = '/patient_view_prescriptions.php';  // Update to correct patient dashboard
                        break;
                }
                
                // Wait briefly then redirect
                setTimeout(() => {
                    window.location.href = redirectUrl;
                }, 1000);
            } else {
                // Display error message from the PHP backend
                displayMessage(result.message || 'An unknown authentication error occurred.', 'error');
            }

        } catch (error) {
            console.error('Fetch operation failed:', error.message);
            displayMessage('A connection error occurred. Please try again.', 'error');
        } finally {
            loginButton.textContent = 'LOGIN';
            loginButton.disabled = false;
        }
    });
});