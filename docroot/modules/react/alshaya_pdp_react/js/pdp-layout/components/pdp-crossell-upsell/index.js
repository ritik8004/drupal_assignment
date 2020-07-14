import React from 'react';
import Slider from 'react-slick';
import ConditionalView from '../../../common/components/conditional-view';
import { crossellUpsellSliderSettings } from '../../../common/components/utilities/slider_settings';
import PdpCrossellUpsellImage from '../pdp-crossell-upsell-images';

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

  render() {
    const { currentPage, totalPagers, limits } = this.state;

    const {
      skuCode,
      pdpGallery,
    } = this.props;
    const images = skuCode ? pdpGallery.thumbnails : [];

    const isTouchDevice = window.outerWidth < 1024;

    if (images.length === 0) {
      return (
        <div>Images not available</div>
      );
    }

    return (
      <div className="magv2-pdp-crossell-upsell-container fadeInUp" style={{ animationDelay: '1.6s' }}>
        <div className="magv2-pdp-crossell-upsell-heading">
          <div className="magv2-pdp-crossell-upsell-title">
            <span className="magv2-pdp-crossell-upsell-label">{Drupal.t('How about these?')}</span>
            <span className="magv2-pdp-crossell-upsell-sublabel">{Drupal.t('Similar items')}</span>
          </div>
          <div className="magv2-pdp-crossell-upsell-view-more-wrapper">
            <a className="magv2-pdp-crossell-upsell-view-more-label">{Drupal.t('View more')}</a>
            <span className="magv2-pdp-crossell-upsell-view-more-icon" />
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
            {images.map((image) => (
              <PdpCrossellUpsellImage
                key={image.thumburl}
                imageUrl={image.thumburl}
                alt={image.label}
                title={image.label}
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
            {images.map((image) => (
              <PdpCrossellUpsellImage
                key={image.thumburl}
                imageUrl={image.thumburl}
                alt={image.label}
                title={image.label}
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
            {`${currentPage} ${Drupal.t('of')} ${totalPagers}`}
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
