document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.promotion-items').forEach(function (container) {
        const products = JSON.parse(container.dataset.products || '[]');
        const list = container.querySelector('.promotion-item-list');

        function addRow() {
            const row = document.createElement('div');
            row.className = 'promotion-item-row';
            const options = products.map(product => `<option value="${product.id}">${product.product_code} — ${product.product_name}</option>`).join('');
            row.innerHTML = `<select name="product_ids[]" required>${options}</select><input name="quantities[]" type="number" min="1" value="1" required><label class="pm-check"><input name="gift_product_ids[]" type="checkbox" value="${products[0]?.id || ''}"> Quà tặng</label><button type="button" class="remove-promotion-item" aria-label="Xóa">×</button>`;
            list.appendChild(row);
        }

        container.querySelector('.add-promotion-item')?.addEventListener('click', addRow);
        container.addEventListener('change', function (event) {
            if (event.target.matches('select[name="product_ids[]"]')) {
                event.target.closest('.promotion-item-row').querySelector('input[name="gift_product_ids[]"]').value = event.target.value;
            }
        });
        container.addEventListener('click', function (event) {
            const remove = event.target.closest('.remove-promotion-item');
            if (remove) remove.closest('.promotion-item-row').remove();
        });
        if (!list.children.length && products.length) addRow();
    });
});
