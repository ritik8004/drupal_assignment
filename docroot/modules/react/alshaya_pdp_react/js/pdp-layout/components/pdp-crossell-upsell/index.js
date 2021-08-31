import React from 'react';
import Slider from 'react-slick';
import { crossellUpsellSliderSettings } from '../../../common/components/utilities/slider_settings';
import PdpCrossellUpsellImage from '../pdp-crossell-upsell-images';
import CrossellPopupContent from '../pdp-crossel-popup';

export default class PdpCrossellUpsell extends React.PureComponent {
  constructor(props) {
    super(props);
    this.state = {
      currentPage: 1,
      totalPagers: 1,
      limits: {
        prev: true,
        next: false,
      },
    };
  }

  componentDidMount = () => {
    const sliderProps = this.slider.innerSlider.props;
    const totalPagers = Math.ceil(sliderProps.children.length / sliderProps.slidesToScroll);
    const currentPage = Math.ceil((sliderProps.initialSlide + 1) / sliderProps.slidesToScroll);

    if (this.checkSlider()) {
      document.querySelector('body').classList.add('rtl-slider-not-initialised');
    } else {
      document.querySelector('body').classList.remove('rtl-slider-not-initialised');
    }

    this.setState({
      currentPage,
      totalPagers,
      limits: {
        prev: true,
        next: totalPagers === currentPage,
      },
    });
  }

  beforeChange = (current, next) => {
    const sliderProps = this.slider.innerSlider.props;
    const currentPage = Math.ceil((next + 1) / sliderProps.slidesToScroll);

    this.setState((prevState) => ({
      currentPage,
      limits: {
        prev: currentPage === 1,
        next: prevState.totalPagers === currentPage,
      },
    }));
  }

  goToNextSlide = () => {
    this.slider.slickNext();
  }

  goToPrevSlide = () => {
    this.slider.slickPrev();
  }

  openModal = (relatedSku) => (
    <CrossellPopupContent
      closeModal={this.closeModal}
      relatedSku={relatedSku}
      overlayClass="overlay-crossel"
    />
  );

  closeModal = () => {
    const { removePanelData } = this.props;
    document.querySelector('body').classList.remove('overlay-crossel');
    setTimeout(() => {
      removePanelData();
    }, 400);
  };

  checkSlider = () => {
    const { products } = this.props;
    let flag = false;
    if (drupalSettings.path.currentLanguage === 'ar') {
      if (window.innerWidth < 768) {
        flag = Object.keys(products).length <= 1;
      } else if (window.innerWidth < 1025) {
        flag = Object.keys(products).length <= 2;
      } else {
        flag = Object.keys(products).length <= 4;
      }
    }
    return flag;
  };

  render() {
    const { currentPage, totalPagers, limits } = this.state;
    const {
      sectionTitle,
      products,
      getPanelData,
    } = this.props;

    if (products.length === 0) {
      return (
        <></>
      );
    }

    return (
      <div className="magv2-pdp-crossell-upsell-container fadeInUp" style={{ animationDelay: '1.6s' }}>
        <div className="magv2-pdp-crossell-upsell-heading">
          <div className="magv2-pdp-crossell-upsell-title">
            <span className="magv2-pdp-crossell-upsell-label">{sectionTitle}</span>
          </div>
        </div>
        {/* disable eslint to disable jsx-props-no-spreading rule for slider */}
        {/* eslint-disable */}
        <Slider
          ref={(slider) => { this.slider = slider; }}
          beforeChange={this.beforeChange}
          {...crossellUpsellSliderSettings}
        >
          {/* eslint-disable */}
          {Object.keys(products).map((sku) => (
            <PdpCrossellUpsellImage
              key={products[sku].gallery.mediumurl}
              keyId={products[sku].gallery.mediumurl}
              imageUrl={products[sku].gallery.mediumurl}
              alt={products[sku].gallery.label}
              title={products[sku].title}
              finalPrice={parseFloat(products[sku].finalPrice)
                .toFixed(drupalSettings.reactTeaserView.price.decimalPoints)}
              pdpProductPrice={parseFloat(products[sku].priceRaw)
                .toFixed(drupalSettings.reactTeaserView.price.decimalPoints)}
              productUrl={products[sku].productUrl}
              productLabels={products[sku].productLabels}
              productPromotions={products[sku].promotions}
              openModal={this.openModal}
              getPanelData={getPanelData}
              relatedSku={sku}
              gtmAttributes={products[sku].gtm_attributes}
            />
          ))}
        </Slider>
        <div className="slider-nav">
          <span
            onClick={(drupalSettings.path.currentLanguage === 'en') ? this.goToPrevSlide : this.goToNextSlide}
            className={`slider-prev slider-pagers${(drupalSettings.path.currentLanguage === 'en' ? limits.prev : limits.next) ? ' disabled' : ''}`}
          />
          <span className="slider-pagination">
            <span>
              {currentPage}
            </span>
            <span>{Drupal.t('of')}</span>
            <span>
              {totalPagers}
            </span>
          </span>
          <span
            onClick={(drupalSettings.path.currentLanguage === 'en') ? this.goToNextSlide : this.goToPrevSlide}
            className={`slider-next slider-pagers${(drupalSettings.path.currentLanguage === 'en' ? limits.next : limits.prev) ? ' disabled' : ''}`}
          />
        </div>
      </div>
    );
  }
}
