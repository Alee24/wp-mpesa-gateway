# WP Mpesa Gateway

**WP Mpesa Gateway** is a professional, robust WordPress plugin for integrating Safaricom's M-Pesa STK Push payment method. It is designed to be simple to set up yet powerful, featuring a dedicated admin dashboard for transaction analytics and a modern, responsive payment form.

## 🚀 Features

*   **Seamless STK Push Integration**: Initiate payments directly from your WordPress site.
*   **Professional UI**: Comes with a clean, modern, and responsive payment form that looks great on all devices.
*   **Admin Dashboard**:
    *   **Analytics**: View Total Revenue, Successful Transactions, and Failed Transactions at a glance.
    *   **Transaction History**: Detailed table of all transactions with live status updates.
*   **Robust Error Logging**: Comprehensive logs to help troubleshoot API and connection issues.
*   **Secure**: Uses WordPress nonces and best practices for data handling.
*   **Sandbox & Live Support**: Easily switch between Safaricom's Sandbox (Test) and Live environments.

## 🛠️ Installation

1.  Download the repository as a ZIP file or clone it using Git.
    ```bash
    git clone https://github.com/Alee24/wp-mpesa-gateway.git
    ```
2.  If you downloaded the ZIP, extract it. You should have a folder named `wp-mpesa-gateway`.
3.  Upload the `wp-mpesa-gateway` folder to your WordPress `wp-content/plugins/` directory.
    *   *Alternatively*, you can zip the `wp-mpesa-gateway` folder and upload it via the WordPress Admin > Plugins > Add New > Upload Plugin.
4.  Activate the **WP Mpesa Gateway** plugin through the 'Plugins' menu in WordPress.

## ⚙️ Configuration

1.  Navigate to **Mpesa Gateway** > **Settings** in your WordPress Admin sidebar.
2.  Enter your M-Pesa API Credentials:
    *   **Environment**: Select `Sandbox` for testing or `Live` for production.
    *   **Consumer Key**: From Safaricom Developer Portal.
    *   **Consumer Secret**: From Safaricom Developer Portal.
    *   **Business Shortcode**: Your Paybill or Till Number.
    *   **Passkey**: Your M-Pesa Passkey.
3.  Click **Save Settings**.

## 💻 Usage

### Shortcode
Add the payment form to any Page, Post, or Widget using the shortcode:

```
[mpesa_stk_push]
```
or
```
[stk_push_form]
```

### Dashboard
Visit **Mpesa Gateway** > **Dashboard** to view your transaction history and financial statistics.

## 📂 Directory Structure

```
wp-mpesa-gateway/
├── admin/                  # Admin area logic (Dashboard, Settings)
├── assets/                 # CSS and JS files
├── includes/               # Core logic (API, Database)
├── public/                 # Frontend logic (Shortcodes)
├── wp-mpesa-gateway.php    # Main plugin file
└── README.md               # Documentation
```

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📝 License

This project is licensed under the MIT License.

## 👤 Author

**KK Dynamic Enterprise Solutions Ltd**
