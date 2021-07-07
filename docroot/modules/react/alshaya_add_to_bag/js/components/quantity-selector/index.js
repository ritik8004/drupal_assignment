import React from 'react';
import ConditionalView from '../../../../js/utilities/components/conditional-view';
import Dropdown from './dropdown';
import IncreaseDecrease from './increase-decrease';

function QuantitySelector(props) {
  // type = inc_dec / dropdown.
  // "inc_dec" will provide buttons for increasing/decreasing quantity.
  // "dropdown" will only provide a select list for changing quantity.
  const { type } = props;

  return (
    <>
      <ConditionalView condition={type === 'inc_dec'}>
        <IncreaseDecrease {...props} />
      </ConditionalView>

      <ConditionalView condition={type === 'dropdown'}>
        <Dropdown {...props} />
      </ConditionalView>
    </>
  );
}

export default QuantitySelector;
