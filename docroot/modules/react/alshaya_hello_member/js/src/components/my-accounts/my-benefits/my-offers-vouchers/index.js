import React from 'react';
import Slider from 'react-slick';

const MyOffersAndVouchers = ({ myBenefitsList }) => {
  const settings = {
    dots: true,
    infinite: false,
    speed: 500,
    slidesToShow: 3,
    slidesToScroll: 3,
    initialSlide: 0,
    adaptiveHeight: true,
    lazyLoad: true,
    responsive: [
      {
        breakpoint: 767,
        settings: {
          slidesToShow: 1,
          slidesToScroll: 1,
          infinite: true,
          swipeToSlide: true,
        },
      },
    ],
  };

  return (
    <div className="my-benefits-wrapper">
      <Slider {...settings}>
        {myBenefitsList.map((data) => (
          <div className="my-offers-vouchers-details" key={data.id}>
            <div className="image-container">
              <img src={data.image} />
            </div>
            <div className="voucher-wrapper">
              <div className="title">
                {data.name}
              </div>
              <div className="info">
                {data.description}
              </div>
              <div className="expiry">
                {data.end_date}
              </div>
            </div>
          </div>
        ))}
      </Slider>
    </div>
  );
};

export default MyOffersAndVouchers;
