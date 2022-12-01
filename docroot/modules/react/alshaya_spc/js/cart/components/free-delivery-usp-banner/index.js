import React from 'react';
import parse from 'html-react-parser';

const FreeDeliveryUspBanner = (props) => {
  const { bannerText } = props;

  return (
    <div className="free-delivery-usp">
      {parse(bannerText)}
    </div>
  );
};

export default FreeDeliveryUspBanner;
