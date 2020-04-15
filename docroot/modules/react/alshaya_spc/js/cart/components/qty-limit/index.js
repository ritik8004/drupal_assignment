import React from 'react';

const QtyLimit = ({ qty, maxSaleQty }) => (
  (parseInt(qty, 10) >= parseInt(maxSaleQty, 10))
    ? Drupal.t('Purchase limit has been reached')
    : Drupal.t('Limited to @max_sale_qty per customer', { '@max_sale_qty': maxSaleQty })
);

export default React.memo(QtyLimit);
