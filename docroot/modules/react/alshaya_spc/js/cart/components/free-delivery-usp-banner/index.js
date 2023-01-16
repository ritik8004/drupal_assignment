import React from 'react';
import parse from 'html-react-parser';

const FreeDeliveryUspBanner = (props) => {
  const { bannerText } = props;

  return (
    <div className="spc-free-delivery-container">
      <div className="free-delivery-usp">
        {parse(bannerText)}
      </div>
    </div>
  );
};

export default FreeDeliveryUspBanner;
