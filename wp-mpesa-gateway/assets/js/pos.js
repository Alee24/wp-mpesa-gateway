jQuery(document).ready(function ($) {

    // --- State ---
    let cart = [];

    // --- Selectors ---
    const $grid = $('.products-grid');
    const $cartItems = $('#cart-items');
    const $total = $('#cart-total');
    const $search = $('#pos-search');
    const $payBtn = $('#pos-pay-btn');
    const $phone = $('#customer-phone');

    // --- Search ---
    $search.on('input', function () {
        const term = $(this).val().toLowerCase();
        $('.pos-product-card').each(function () {
            const name = $(this).data('name').toString().toLowerCase();
            $(this).toggle(name.indexOf(term) > -1);
        });
    });

    // --- Add to Cart ---
    $('.pos-product-card').on('click', function () {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const price = parseFloat($(this).data('price'));

        const existing = cart.find(item => item.id === id);

        if (existing) {
            existing.qty++;
        } else {
            cart.push({ id, name, price, qty: 1 });
        }

        renderCart();
    });

    // --- Cart Actions ---
    $cartItems.on('click', '.plus', function () {
        const id = $(this).closest('.cart-item').data('id');
        const item = cart.find(i => i.id === id);
        if (item) { item.qty++; renderCart(); }
    });

    $cartItems.on('click', '.minus', function () {
        const id = $(this).closest('.cart-item').data('id');
        const item = cart.find(i => i.id === id);
        if (item) {
            item.qty--;
            if (item.qty <= 0) cart = cart.filter(i => i.id !== id);
            renderCart();
        }
    });

    $cartItems.on('click', '.remove-btn', function () {
        const id = $(this).closest('.cart-item').data('id');
        cart = cart.filter(i => i.id !== id);
        renderCart();
    });

    // --- Render ---
    function renderCart() {
        $cartItems.empty();
        let total = 0;

        if (cart.length === 0) {
            $cartItems.html('<p class="empty-cart">Cart is empty</p>');
            $total.text('KES 0.00');
            return;
        }

        cart.forEach(item => {
            const itemTotal = item.price * item.qty;
            total += itemTotal;

            const html = `
                <div class="cart-item" data-id="${item.id}">
                    <div class="item-name">${item.name}</div>
                    <div class="item-qty">
                        <button class="qty-btn minus">-</button>
                        <span>${item.qty}</span>
                        <button class="qty-btn plus">+</button>
                    </div>
                    <div class="item-price">${itemTotal.toFixed(2)}</div>
                    <div class="remove-btn">&times;</div>
                </div>
            `;
            $cartItems.append(html);
        });

        $total.text('KES ' + total.toFixed(2));
    }

    // --- Checkout ---
    $payBtn.on('click', function () {
        if (cart.length === 0) return alert('Cart is empty!');

        const phone = $phone.val();
        if (!phone) return alert('Please enter Customer Phone Number');

        $payBtn.prop('disabled', true).text('Initiating STK Push...');

        $.post(mpesa_pos_vars.ajax_url, {
            action: 'mpesa_pos_checkout',
            nonce: mpesa_pos_vars.nonce,
            phone: phone,
            cart: cart
        }, function (response) {
            if (response.success) {
                alert(response.data);
                cart = [];
                renderCart();
                $phone.val('');
            } else {
                alert('Error: ' + response.data);
            }
            $payBtn.prop('disabled', false).text('Pay with M-Pesa');
        }).fail(function () {
            alert('Connection Error');
            $payBtn.prop('disabled', false).text('Pay with M-Pesa');
        });
    });

});
