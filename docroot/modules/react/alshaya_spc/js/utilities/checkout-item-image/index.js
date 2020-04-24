import React from 'react';

const CheckoutItemImage = (props) => {
  const { ImgData } = props;

  if (ImgData === undefined || ImgData.url === undefined) {
    return null;
  }

  return <img src={ImgData.url} alt={ImgData.alt} title={ImgData.title} />;
};

export default CheckoutItemImage;
