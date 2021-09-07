import Advantagecard from '../../../utilities/advantagecard';

const AdvantageCardExcludedItem = (props) => {
  const {
    totalsItems,
    id,
  } = props;
  if (Advantagecard.isAdvantagecardEnabled()
    && Advantagecard.isAdvantageCardEligibleProduct(totalsItems, id) === 'false') {
    return Drupal.t('The products in your shopping basket are not eligible for the Advantage card discount.');
  }
  return null;
};

export default AdvantageCardExcludedItem;
