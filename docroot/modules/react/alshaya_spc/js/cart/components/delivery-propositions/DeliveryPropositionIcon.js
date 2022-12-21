import React from 'react';

const DeliveryPropositionIcon = (props) => {
  const { iconPath } = props;

  return (
    <img src={iconPath} height="30px" width="50px" loading="lazy" />
  );
};

export default DeliveryPropositionIcon;
