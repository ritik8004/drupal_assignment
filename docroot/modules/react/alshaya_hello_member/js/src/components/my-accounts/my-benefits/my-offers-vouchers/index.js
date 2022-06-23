import React from 'react';
import Slider from 'react-slick';
import { hasValue } from '../../../../../../../js/utilities/conditionsUtility';

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
          variableWidth: true,
          dots: false,
        },
      },
    ],
  };
  const { currentPath } = drupalSettings.path;

  return (
    <div className="my-benefits-wrapper">
      <Slider {...settings}>
        {myBenefitsList.map((data) => (
          <a className="my-offers-vouchers-details" key={data.id || data.code} href={`${Drupal.url(currentPath)}/hello-member-benefits/${hasValue(data.id) ? `coupon/${data.id}` : `offer/${data.code}`}`}>
            <div className="image-container">
              <img src={data.small_image} />
            </div>
            <div className="voucher-wrapper">
              <div className="title">
                {data.name}
              </div>
              <div className="info">
                {data.short_description}
              </div>
              <div className="expiry">
                {data.expiry_date || data.end_date}
              </div>
            </div>
          </a>
        ))}
      </Slider>
    </div>
  );
};

export default MyOffersAndVouchers;
