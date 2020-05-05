import React from 'react';
import PdpAsset from '../pdp-asset';

export default class PdpGallery extends React.Component {
  constructor(props) {
    super(props);

    const { skuCode } = props;

    this.images = skuCode ? drupalSettings.pdpGallery[skuCode]['#thumbnails'] : [];

  }

  render() {
    const {images} = this;

    return (
      <div className="magv2-pdp-gallery">
        <div className="magazine__gallery--container">
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
      </div>
    );
  }
}
