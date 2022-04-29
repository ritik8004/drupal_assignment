import React from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import AddressHours from '../AddressHours';

const AddressHoursParent = (props) => {
  const { type } = props;

  if (type === 'addressitem') {
    const { address, classname } = props;
    return (
      <ConditionalView condition={hasValue(address)}>
        <AddressHours
          type="addresstext"
          address={address}
          classname={classname}
        />
      </ConditionalView>
    );
  }

  if (type === 'hoursitem') {
    const { storeHours } = props;
    return (
      <ConditionalView condition={hasValue(storeHours)}>
        <AddressHours
          type="hourstext"
          storeHours={storeHours}
        />
      </ConditionalView>
    );
  }

  return false;
};

export default AddressHoursParent;
