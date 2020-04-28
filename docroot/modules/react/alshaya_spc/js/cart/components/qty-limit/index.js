import { isQtyLimitReached } from '../../../utilities/checkout_util';

const QtyLimit = ({ qty, maxSaleQty, errMsg }) => {
  if (errMsg !== undefined && isQtyLimitReached(errMsg) >= 0) {
    return Drupal.t('The maximum quantity per item has been exceeded');
  }
  return (parseInt(qty, 10) < parseInt(maxSaleQty, 10))
    ? Drupal.t('Limited to @max_sale_qty per customer', { '@max_sale_qty': maxSaleQty })
    : Drupal.t('Purchase limit has been reached');
};

export default QtyLimit;
