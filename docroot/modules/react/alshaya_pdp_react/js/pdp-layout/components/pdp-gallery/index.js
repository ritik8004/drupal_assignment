import React from 'react';
import Slider from 'react-slick';
import Popup from 'reactjs-popup';
import ConditionalView from '../../../common/components/conditional-view';
import { sliderSettings, fullScreenSliderSettings } from '../../../common/components/utilities/slider_settings';
import PdpImageElement from '../pdp-image-element';
import PdpAsset from '../pdp-asset';
import 'slick-carousel/slick/slick.css';
import 'slick-carousel/slick/slick-theme.css';
import 'react-magic-slider-dots/dist/magic-dots.css';

export default class PdpGallery extends React.PureComponent {
  constructor(props) {
    super(props);
    this.state = {
      open: false,
    };
  }

  showFullScreenModal = (event) => {
    let el = event.target;

    while (el.getAttribute('data-index') === null) {
      el = el.parentNode;
    }

    const indexOfChildWRTSiblings = parseInt(el.getAttribute('data-index'), 10);

    this.setState({
      open: true,
      currentIndex: indexOfChildWRTSiblings,
    });
  };

  closeModal = () => {
    this.setState({
      open: false,
    });
  };

  render() {
    const { pdpGallery } = this.props;
    const images = pdpGallery ? pdpGallery.thumbnails : [];
    const emptyRes = (
      <div>Images not available</div>
    );
    const { open, currentIndex } = this.state;
    const isTouchDevice = window.innerWidth < 767;
    const onlyTablet = window.innerWidth < 1024;
    let centerPaddingValue;
    if (isTouchDevice) {
      centerPaddingValue = null;
    } else if (onlyTablet) {
      centerPaddingValue = '100px';
    } else {
      centerPaddingValue = '350px';
    }

    return (images) ? (
      <div className="magv2-pdp-gallery">
        <ConditionalView condition={window.innerWidth > 767}>
          <div className="magazine__gallery--container-desktop">
            {images.map((image, key) => (
              <PdpAsset
                key={image.zoomurl}
                type={image.type}
                imageZoomUrl={image.zoomurl}
                imageUrl={image.mediumurl}
                alt={image.label}
                title={image.label}
                onClick={this.showFullScreenModal}
                viewport="desktop"
                index={key}
              />
            ))}
          </div>
        </ConditionalView>
        <ConditionalView condition={window.innerWidth < 768}>
          <div className="magazine__gallery--container-mobile">
            <Slider
              dots={sliderSettings.dots}
              infinite={sliderSettings.infinite}
              arrows={sliderSettings.arrows}
              appendDots={sliderSettings.appendDots}
            >
              {images.map((image, key) => (
                <PdpImageElement
                  key={image.zoomurl}
                  imageUrl={image.mediumurl}
                  alt={image.label}
                  title={image.label}
                  onClick={this.showFullScreenModal}
                  viewport="mobile"
                  index={key}
                />
              ))}
            </Slider>
          </div>
        </ConditionalView>
        <Popup
          open={open}
          closeOnDocumentClick={false}
        >
          <div className="fullscreen-slider-wrapper">
            <a className="close" onClick={this.closeModal} />
            <Slider
              initialSlide={currentIndex}
              dots={fullScreenSliderSettings.dots}
              infinite={!isTouchDevice}
              arrows={fullScreenSliderSettings.arrows}
              centerMode={!isTouchDevice}
              centerPadding={centerPaddingValue}
              appendDots={fullScreenSliderSettings.appendDots}
            >
              {images.map((image, key) => (
                <PdpImageElement
                  key={image.zoomurl}
                  imageUrl={image.zoomurl}
                  alt={image.label}
                  title={image.label}
                  index={key}
                />
              ))}
            </Slider>
          </div>
        </Popup>
      </div>
    ) : emptyRes;
  }
}
