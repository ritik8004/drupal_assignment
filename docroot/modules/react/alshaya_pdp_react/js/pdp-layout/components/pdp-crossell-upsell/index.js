import React from 'react';
import Slider from 'react-slick';
import ConditionalView from '../../../common/components/conditional-view';
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

  openModal = (relatedSku) => {
    document.querySelector('body').classList.add('crossel-overlay');
    return (
      <CrossellPopupContent
        closeModal={this.closeModal}
        relatedSku={relatedSku}
      />
    );
  };

  closeModal = () => {
    const { removePanelData } = this.props;
    document.querySelector('body').classList.remove('crossel-overlay');
    removePanelData();
  };

  render() {
    const { currentPage, totalPagers, limits } = this.state;
    const {
      sectionTitle,
      products,
      getPanelData,
    } = this.props;

    const isTouchDevice = window.outerWidth < 1024;

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
        <ConditionalView condition={window.innerWidth > 767}>
          <Slider
            dots={crossellUpsellSliderSettings.dots}
            infinite={crossellUpsellSliderSettings.infinite}
            arrows={crossellUpsellSliderSettings.arrows}
            centerMode={crossellUpsellSliderSettings.centerMode}
            variableWidth={crossellUpsellSliderSettings.variableWidth}
            slidesToShow={crossellUpsellSliderSettings.slidesToShow}
            slidesToScroll={isTouchDevice ? 2 : crossellUpsellSliderSettings.slidesToScroll}
            draggable={isTouchDevice ? true : crossellUpsellSliderSettings.draggable}
            ref={(slider) => { this.slider = slider; }}
            beforeChange={this.beforeChange}
          >
            {Object.keys(products).map((sku) => (
              <PdpCrossellUpsellImage
                key={products[sku].gallery.mediumurl}
                imageUrl={products[sku].gallery.mediumurl}
                alt={products[sku].gallery.label}
                title={products[sku].title}
                finalPrice={products[sku].finalPrice}
                pdpProductPrice={products[sku].priceRaw}
                productUrl={products[sku].productUrl}
                productLabels={products[sku].productLabels}
                productPromotions={products[sku].promotions}
                openModal={this.openModal}
                getPanelData={getPanelData}
                relatedSku={sku}
              />
            ))}
          </Slider>
        </ConditionalView>
        <ConditionalView condition={window.innerWidth < 768}>
          <Slider
            dots={crossellUpsellSliderSettings.dots}
            infinite={crossellUpsellSliderSettings.infinite}
            arrows={crossellUpsellSliderSettings.arrows}
            centerMode={crossellUpsellSliderSettings.centerMode}
            variableWidth={crossellUpsellSliderSettings.variableWidth}
            slidesToShow={crossellUpsellSliderSettings.slidesToShow}
            slidesToScroll={isTouchDevice ? 1 : crossellUpsellSliderSettings.slidesToScroll}
            draggable={isTouchDevice ? true : crossellUpsellSliderSettings.draggable}
            ref={(slider) => { this.slider = slider; }}
            beforeChange={this.beforeChange}
          >
            {Object.keys(products).map((sku) => (
              <PdpCrossellUpsellImage
                key={products[sku].gallery.mediumurl}
                imageUrl={products[sku].gallery.mediumurl}
                alt={products[sku].gallery.label}
                title={products[sku].title}
                finalPrice={products[sku].finalPrice}
                pdpProductPrice={products[sku].priceRaw}
                productUrl={products[sku].productUrl}
                productLabels={products[sku].productLabels}
                openModal={this.openModal}
                getPanelData={getPanelData}
                relatedSku={sku}
              />
            ))}
          </Slider>
        </ConditionalView>
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
