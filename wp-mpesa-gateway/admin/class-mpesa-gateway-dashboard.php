<?php

class WP_Mpesa_Gateway_Dashboard {

    public function render() {
        $stats = WP_Mpesa_Gateway_DB::get_stats();
        $transactions = WP_Mpesa_Gateway_DB::get_transactions();
        ?>
        <div class="wrap mpesa-dashboard">
            <h1>M-Pesa Gateway Dashboard</h1>

            <div class="mpesa-stats-row">
                <div class="mpesa-card stat-card revenue">
                    <h3>Total Revenue</h3>
                    <div class="number">KES <?php echo number_format($stats['revenue'], 2); ?></div>
                </div>
                <div class="mpesa-card stat-card success">
                    <h3>Successful</h3>
                    <div class="number"><?php echo $stats['success']; ?></div>
                </div>
                <div class="mpesa-card stat-card failed">
                    <h3>Failed</h3>
                    <div class="number"><?php echo $stats['failed']; ?></div>
                </div>
            </div>

            <div class="mpesa-card table-card">
                <h2>Recent Transactions</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Phone</th>
                            <th>Amount</th>
                            <th>Receipt</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($transactions)): ?>
                            <tr><td colspan="5">No transactions yet.</td></tr>
                        <?php else: ?>
                            <?php foreach($transactions as $txn): ?>
                                <tr>
                                    <td><?php echo $txn->created_at; ?></td>
                                    <td><?php echo $txn->phone_number; ?></td>
                                    <td><?php echo number_format($txn->amount, 2); ?></td>
                                    <td><?php echo $txn->mpesa_receipt_number ? $txn->mpesa_receipt_number : '-'; ?></td>
                                    <td>
                                        <span class="mpesa-badge <?php echo strtolower($txn->status); ?>">
                                            <?php echo $txn->status; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }
}
