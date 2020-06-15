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
    const { slider } = this;
    const sliderProps = slider.innerSlider.props;

    const totalPagers = Math.ceil(sliderProps.children.length / sliderProps.slidesToScroll);
    const currentPage = Math.ceil((sliderProps.initialSlide + 1) / sliderProps.slidesToScroll);

    this.setState({
      currentPage,
      totalPagers,
    });
  }

  beforeChange = (current, next) => {
    const { slider } = this;
    const { totalPagers } = this.state;
    const sliderProps = slider.innerSlider.props;
    const currentPage = Math.ceil((next + 1) / sliderProps.slidesToScroll);

    this.setState({
      currentPage,
      limits: {
        prev: currentPage === 1,
        next: totalPagers === currentPage,
      },
    });
  }

  goToNextSlide = () => {
    const { slider } = this;

    slider.slickNext();
  }

  goToPrevSlide = () => {
    const { slider } = this;

    slider.slickPrev();
  }

  render() {
    const {
      beforeChange, goToNextSlide, goToPrevSlide,
    } = this;
    const { currentPage, totalPagers, limits } = this.state;

    const {
      skuCode,
      pdpGallery,
    } = this.props;
    const images = skuCode ? pdpGallery.thumbnails : [];

    const emptyRes = (
      <div>Images not available</div>
    );

    const isTouchDevice = window.outerWidth < 1024;


    return (images) ? (
      <div className="magv2-pdp-crossell-upsell-container">
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
            beforeChange={beforeChange}
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
            beforeChange={beforeChange}
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
            onClick={(drupalSettings.path.currentLanguage === 'en') ? goToPrevSlide : goToNextSlide}
            className={`slider-prev slider-pagers${(drupalSettings.path.currentLanguage === 'en' ? limits.prev : limits.next) ? ' disabled' : ''}`}
          />
          <span className="slider-pagination">
            {`${currentPage} of ${totalPagers}`}
          </span>
          <span
            onClick={(drupalSettings.path.currentLanguage === 'en') ? goToNextSlide : goToPrevSlide}
            className={`slider-next slider-pagers${(drupalSettings.path.currentLanguage === 'en' ? limits.next : limits.prev) ? ' disabled' : ''}`}
          />
        </div>
      </div>
    ) : emptyRes;
  }
}
