import React from 'react';

const CheckoutItemImage = (props) => {
  const { img_data: ImgData } = props;

  if (ImgData === undefined || ImgData.url === undefined) {
    return null;
  }

  return <img loading="lazy" src={ImgData.url} alt={ImgData.alt} title={ImgData.title} />;
};

export default CheckoutItemImage;
