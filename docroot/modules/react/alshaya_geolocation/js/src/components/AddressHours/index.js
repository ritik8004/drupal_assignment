import React from 'react';

const AddressHours = (props) => {
  const { type } = props;

  if (type === 'addresstext') {
    const { address, classname } = props;
    return (
      <div className={classname}>
        {address.map((item) => (
          <div key={item.code}>
            {item.code === 'address_building_segment' ? <span>{item.value}</span> : null}
            {item.code === 'street' ? <span>{item.value}</span> : null}
          </div>
        ))}
      </div>
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

  return false;
};

export default AddressHours;
