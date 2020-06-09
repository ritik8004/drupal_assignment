import React from 'react';
import Slider from 'react-slick';
import Popup from 'reactjs-popup';
import ConditionalView from '../../../common/components/conditional-view';
import { sliderSettings, fullScreenSliderSettings } from '../../../common/components/utilities/slider_settings';
import PdpImageElement from '../pdp-image-element';
import PdpAsset from '../pdp-asset';


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
    const { skuCode, pdpGallery } = this.props;
    const images = skuCode ? pdpGallery.thumbnails : [];
    const emptyRes = (
      <div>Images not available</div>
    );
    const { open, currentIndex } = this.state;
    const { showFullScreenModal, closeModal } = this;
    const isTouchDevice = window.innerWidth < 1024;

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
                onClick={showFullScreenModal}
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
            >
              {images.map((image, key) => (
                <PdpImageElement
                  key={image.zoomurl}
                  imageUrl={image.mediumurl}
                  alt={image.label}
                  title={image.label}
                  onClick={showFullScreenModal}
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
            <a className="close" onClick={closeModal} />
            <Slider
              initialSlide={currentIndex}
              dots={fullScreenSliderSettings.dots}
              infinite={!isTouchDevice}
              arrows={fullScreenSliderSettings.arrows}
              centerMode={!isTouchDevice}
              centerPadding={isTouchDevice ? null : '350px'}
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
