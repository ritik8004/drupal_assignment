import React from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';

const AddressHours = (props) => {
  const { type } = props;

  if (type === 'addresstext') {
    const { address, classname } = props;
    return (
      <div className={classname}>
        {address.map((item) => (
          <>
            {item.code === 'address_building_segment' ? <span>{item.value}</span> : null}
            {item.code === 'street' ? <span>{item.value}</span> : null}
          </>
        ))}
      </div>
    );
  }

  if (type === 'addressitem') {
    const { address, classname } = props;
    return (
      <ConditionalView condition={hasValue(address)}>
        <div className={classname}>
          {address.map((item) => (
            <>
              {item.code === 'address_building_segment' ? <span>{item.value}</span> : null}
              {item.code === 'street' ? <span>{item.value}</span> : null}
            </>
          ))}
        </div>
      </ConditionalView>
    );
  }

  if (type === 'hourstext') {
    const { storeHours } = props;
    return (
      <div className="open--hours">
        {storeHours.map((item) => (
          <div key={item.code}>
            <span className="key-value-key">{item.label}</span>
            <span className="key-value-value">{item.value}</span>
          </div>
        ))}
      </div>
    );
  }

  if (type === 'hoursitem') {
    const { storeHours } = props;
    return (
      <ConditionalView condition={hasValue(storeHours)}>
        <div className="open--hours">
          {storeHours.map((item) => (
            <div key={item.code}>
              <span className="key-value-key">{item.label}</span>
              <span className="key-value-value">{item.value}</span>
            </div>
          ))}
        </div>
      </ConditionalView>
    );
  }

  return false;
};

export default AddressHours;
