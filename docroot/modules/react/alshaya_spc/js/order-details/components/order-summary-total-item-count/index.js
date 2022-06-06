import { hasValue } from '../../../../../js/utilities/conditionsUtility';

const TotalItemCount = (props) => {
  const { order } = props;

  if (hasValue(order.quantity) && hasValue(order.total_quantity_text)) {
    return order.total_quantity_text;
  }

  return null;
};

export default TotalItemCount;
