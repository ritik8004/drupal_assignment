import React from 'react';
import Slider from 'react-slick';
import PdpAsset from '../pdp-asset';

const sliderSettings = {
  dots: true,
  infinite: false,
  arrows: false,
};

export default class PdpGallery extends React.PureComponent {
  render() {
    const { skuCode } = this.props;
    const images = skuCode ? drupalSettings.pdpGallery[skuCode].thumbnails : [];
    const emptyRes = (
      <div>Images not available</div>
    );

    return (images) ? (

      <div className="magv2-pdp-gallery">
        <div className="magazine__gallery--container-desktop">
          {images.map((image) => (
            <PdpAsset
              key={image.zoomurl}
              type={image.type}
              imageZoomUrl={image.zoomurl}
              imageUrl={image.mediumurl}
              alt={image.label}
              title={image.label}
            />
          ))}
        </div>
        <div className="magazine__gallery--container-mobile">
          <Slider
            dots={sliderSettings.dots}
            infinite={sliderSettings.infinite}
            arrows={sliderSettings.arrows}
          >
            {images.map((image) => (
              <img key={image.zoomurl} src={image.mediumurl} />
            ))}
          </Slider>
        </div>
      </div>
    ) : emptyRes;
  }
}
