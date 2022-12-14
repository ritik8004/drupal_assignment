import React from 'react';
import Slider from 'react-slick';
import Popup from 'reactjs-popup';
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
    document.querySelector('body').classList.add('pdp-modal-overlay');
  };

  closeModal = () => {
    this.setState({
      open: false,
    });
    document.querySelector('body').classList.remove('pdp-modal-overlay');
  };

  render() {
    const {
      pdpGallery, children, showFullVersion, context, miniFullScreenGallery, animateMobileGallery,
    } = this.props;
    let images;
    if (pdpGallery) {
      images = context === 'main' ? pdpGallery.thumbnails : pdpGallery.images;
    }

    const emptyRes = (
      <div>Images not available</div>
    );

    const { open, currentIndex } = this.state;
    const isTouchDevice = window.innerWidth < 1024;

    let centerPaddingValue;
    if (isTouchDevice && !showFullVersion) {
      centerPaddingValue = null;
    } else {
      centerPaddingValue = '300px';
    }

    return (images) ? (
      <div className="magv2-pdp-gallery">
        {(showFullVersion)
          ? (
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
                >
                  {key === 0 ? children : ''}
                </PdpAsset>
              ))}
            </div>
          )
          : (
            <div
              className={`magazine__gallery--container-mobile ${(animateMobileGallery ? 'fadeInUp' : '')}`}
              style={(animateMobileGallery ? { animationDelay: '0.4s' } : null)}
            >
              <Slider
                dots={sliderSettings.dots}
                infinite={sliderSettings.infinite}
                arrows={(context === 'main') ? sliderSettings.arrows : true}
                appendDots={sliderSettings.appendDots}
              >
                {images.map((image, key) => (
                  <PdpImageElement
                    key={(context === 'main') ? image.zoomurl : image.url}
                    imageUrl={(context === 'main') ? image.mediumurl : image.url}
                    alt={image.label}
                    title={image.label}
                    onClick={this.showFullScreenModal}
                    viewport="mobile"
                    index={key}
                    miniFullScreenGallery={miniFullScreenGallery}
                  >
                    {key === 0 ? children : ''}
                  </PdpImageElement>
                ))}
              </Slider>
            </div>
          )}
        <Popup
          open={open}
          closeOnDocumentClick={false}
        >
          <div className={`fullscreen-slider-wrapper ${miniFullScreenGallery ? 'fullscreen-slider-wrapper--mini' : ''}`}>
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
                  key={(context === 'main') ? image.zoomurl : image.url}
                  imageUrl={(context === 'main') ? image.zoomurl : image.url}
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
