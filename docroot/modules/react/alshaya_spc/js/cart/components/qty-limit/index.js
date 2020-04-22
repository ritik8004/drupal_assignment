const QtyLimit = ({ qty, maxSaleQty, errMsg }) => {
  if (errMsg === 'The maximum quantity per item has been exceeded') {
    return errMsg;
  }
  return (parseInt(qty, 10) < parseInt(maxSaleQty, 10))
    ? Drupal.t('Limited to @max_sale_qty per customer', { '@max_sale_qty': maxSaleQty })
    : Drupal.t('Purchase limit has been reached');
};

export default QtyLimit;
