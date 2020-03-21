import React from 'react';

const CheckoutItemImage = (props) => {
  const { img_data: { url, alt, title }, img_data: ImgData } = props;
  if (ImgData !== undefined) {
    return <img src={url} alt={alt} title={title} />;
  }

  return (null);
};

export default CheckoutItemImage;
