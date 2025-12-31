# WP Mpesa Gateway - User Guide

Welcome to the **WP Mpesa Gateway** plugin! This guide will help you install, configure, and manage your M-Pesa payments on your WordPress site.

---

## Table of Contents
1.  [Installation](#1-installation)
2.  [Getting M-Pesa API Credentials](#2-getting-m-pesa-api-credentials)
3.  [Configuration](#3-configuration)
4.  [Adding the Payment Form](#4-adding-the-payment-form)
5.  [Managing Transactions](#5-managing-transactions)
6.  [Troubleshooting](#6-troubleshooting)

---

## 1. Installation

### Method A: Upload via WordPress Dashboard
1.  Download the **`wp-mpesa-gateway.zip`** file from the repository.
2.  Log in to your WordPress Dashboard.
3.  Go to **Plugins** > **Add New**.
4.  Click **Upload Plugin** at the top.
5.  Choose the `wp-mpesa-gateway.zip` file and click **Install Now**.
6.  After installation, click **Activate Plugin**.

### Method B: Manual Upload (FTP/Cpanel)
1.  Unzip the `wp-mpesa-gateway.zip` file.
2.  Upload the extracted `wp-mpesa-gateway` folder to `public_html/wp-content/plugins/` on your server.
3.  Go to **Plugins** in your WordPress Dashboard and activate **WP Mpesa Gateway**.

---

## 2. Getting M-Pesa API Credentials

To accept payments, you need credentials from Safaricom.

1.  **Register/Login**: Go to the [Safaricom Developer Portal](https://developer.safaricom.co.ke/).
2.  **Create an App**:
    *   Click "My Apps" > "Create a New App".
    *   Give it a name (e.g., "MyWebShop").
    *   Select **Lipa Na Mpesa Sandbox** (for testing) or **Lipa Na Mpesa Live** (for production).
    *   Click "Create App".
3.  **Get Keys**: Open the app you just created. You will see:
    *   **Consumer Key**
    *   **Consumer Secret**
4.  **Shortcode & Passkey**:
    *   **Sandbox**: Use the test credentials provided on the APIs > Simulate Page or [Test Credentials Page](https://developer.safaricom.co.ke/test_credentials).
    *   **Live**: Use your actual Business Paybill/Till Number and the Passkey sent to you by Safaricom via email after going live.

---

## 3. Configuration

1.  In your WordPress Dashboard, click **Mpesa Gateway** in the sidebar.
2.  Select **Settings**.
3.  Fill in the form:
    *   **Environment**: Start with `Sandbox` to test. Switch to `Live` when ready.
    *   **Consumer Key**: Paste the key from step 2.
    *   **Consumer Secret**: Paste the secret from step 2.
    *   **Business Shortcode**: Your Paybill/Till number.
    *   **Passkey**: Your STK Push Passkey.
    *   **Callback URL**: Leave as default unless you have a specific custom handler.
4.  Click **Save Changes**.

---

## 4. Adding the Payment Form

You can display the payment form on **any** page or post.

1.  Edit the Page/Post where you want the form.
2.  Add a **Shortcode Block**.
3.  Paste one of the following:
    *   `[mpesa_stk_push]`
    *   `[stk_push_form]`
4.  **Update/Publish** the page.
5.  Visit the page to see the beautiful, professional payment form.

---

## 5. Managing Transactions

Track all your payments directly in WordPress.

1.  Go to **Mpesa Gateway** > **Dashboard**.
2.  **Stats Cards**:
    *   **Total Revenue**: Sum of all successful payments.
    *   **Successful**: Count of paid transactions.
    *   **Failed**: Count of incomplete/cancelled transactions.
3.  **Recent Transactions Table**:
    *   Shows Date, Phone Number, Amount, M-Pesa Receipt (e.g., QGH5...), and Status (COMPLETED/FAILED/PENDING).

---

## 6. Troubleshooting

*   **"Payment initiation failed"**:
    *   Check your Consumer Key and Secret.
    *   Ensure your **Shortcode** matches the one associated with the keys.
    *   If using Sandbox, ensure you are using the Test Credentials (Shortcode `174379` usually).
*   **"Plugin file does not exist"**:
    *   This usually happens after renaming folders. Delete the plugin and reinstall the fresh `wp-mpesa-gateway.zip`.
*   **Status stuck on "PENDING"**:
    *   This means the **Callback** didn't reach your site.
    *   Ensure your site is publicly accessible (not on localhost without a tunnel).
    *   Check if a firewall or security plugin is blocking the `/wp-json/mpesa/v1/callback` route.

---

**Support**
For customized support, contact **KKDynamic ENterprise solutions**.
