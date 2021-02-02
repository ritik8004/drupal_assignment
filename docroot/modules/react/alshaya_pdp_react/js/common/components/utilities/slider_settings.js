import React from 'react';
import MagicSliderDots from 'react-magic-slider-dots';

export const sliderSettings = {
  dots: true,
  infinite: false,
  arrows: false,
  appendDots: (dots) => <MagicSliderDots dots={dots} numDotsToShow={5} dotWidth={25} />,
};

export const fullScreenSliderSettings = {
  dots: true,
  infinite: false,
  arrows: true,
  centerMode: false,
  appendDots: (dots) => <MagicSliderDots dots={dots} numDotsToShow={5} dotWidth={30} />,
};

export const crossellUpsellSliderSettings = {
  dots: false,
  infinite: false,
  arrows: false,
  slidesToShow: 4,
  slidesToScroll: 4,
  variableWidth: true,
  draggable: false,
  responsive: [
    {
      breakpoint: 1025,
      settings: {
        slidesToShow: 2,
        slidesToScroll: 2,
      },
    },
    {
      breakpoint: 767,
      settings: {
        slidesToShow: 1,
        slidesToScroll: 1,
      },
    },
  ],
};
