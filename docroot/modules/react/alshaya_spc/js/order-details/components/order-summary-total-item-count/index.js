const TotalItemCount = (props) => {
  const { order } = props;

  if (order.quantity === 1) {
    return Drupal.t('Total: @count item', { '@count': order.quantity }, {});
  }

  if (order.quantity > 1) {
    return Drupal.t('Total: @count items', { '@count': order.quantity }, {});
  }

  return null;
};

export default TotalItemCount;
