const AdvantageCardExcludedItem = (props) => {
  const {
    advantageCardProduct,
  } = props;
  if (advantageCardProduct === 'false') {
    return Drupal.t('The products in your shopping basket are not eligible for the Advantage card discount.');
  }
  return null;
};

export default AdvantageCardExcludedItem;
