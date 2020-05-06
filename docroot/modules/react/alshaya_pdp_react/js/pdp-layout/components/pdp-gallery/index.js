import React from 'react';
import PdpAsset from '../pdp-asset';
import Slider from 'react-slick';

export default class PdpGallery extends React.Component {
  constructor(props) {
    super(props);

  }

  render() {
    const { skuCode } = this.props;
    const images = skuCode ? drupalSettings.pdpGallery[skuCode]['#thumbnails'] : [];
    const sliderSettings = {
      dots: true,
      infinite: false,
      arrows: false,
    };

    return (

      <div className="magv2-pdp-gallery">
        <div className="magazine__gallery--container-desktop">
          {images.map((image, key) => {
            return (
              <PdpAsset
              key={key}
              type={image.type}
              imageZoomUrl={image.zoomurl}
              imageUrl={image.mediumurl}
              alt={image.label}
              title={image.label}
            />);
          })}
        </div>
        <div className="magazine__gallery--container-mobile">
          <Slider {...sliderSettings}>
            {images.map((image, key) => {
              return (
                <img key={key} src={image.mediumurl} />
              );
            })}
          </Slider>
        </div>
      </div>
    );
  }
}
