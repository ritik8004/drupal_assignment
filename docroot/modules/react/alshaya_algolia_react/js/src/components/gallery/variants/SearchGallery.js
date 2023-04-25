import React from 'react';
import Slider from 'react-slick';
import ImageElement from '../imageHelper/ImageElement';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import { isDesktop } from '../../../../../../js/utilities/display';
import Lozenges
  from '../../../../common/components/lozenges';

const SliderElement = ({
  src, title,
}) => (
  <ImageElement
    src={src}
    title={title}
  />
);

const slickEffect = hasValue(drupalSettings.reactTeaserView.swipe_image.slide_effect)
  ? drupalSettings.reactTeaserView.swipe_image.slide_effect : null;

const sliderSettings = {
  dots: true,
  fade: slickEffect === 'fade',
  infinite: true,
  slidesToShow: 1,
  slidesToScroll: 1,
  arrows: false,
  touchThreshold: 1750,
  variableWidth: false,
  autoplaySpeed: hasValue(drupalSettings.reactTeaserView.swipe_image.image_slide_timing)
    ? drupalSettings.reactTeaserView.swipe_image.image_slide_timing * 1000
    : 2000,
  autoplay: true,
  pauseOnHover: false,
};

class SearchGallery extends React.PureComponent {
  constructor(props) {
    super(props);
    this.mainImageRef = React.createRef();
    this.onHoverAppendMarkup = this.onHoverAppendMarkup.bind(this);
  }

  onHoverAppendMarkup = (showLimitThumb, thumbnails) => (
    <div className="alshaya_search_slider">
      <Slider
        {...sliderSettings}
        className={`search-lightSlider ${slickEffect ? `slick-effect-${slickEffect}` : ''}`}
        ref={this.getref}
      >
        { isDesktop() ? showLimitThumb : thumbnails }
      </Slider>
    </div>
  )

  getref = (slider) => {
    const { setSlider } = this.props;
    setSlider(slider);
  }

  render() {
    const scrollImage = hasValue(drupalSettings.reactTeaserView.swipe_image
      .no_of_image_scroll) ? drupalSettings.reactTeaserView
        .swipe_image.no_of_image_scroll : 6;
    const {
      media, title, labels, sku, initSlider,
    } = this.props;
    const mainImage = media.length ? media[0] : {};
    const mainImageUrl = hasValue(mainImage.url) ? mainImage.url : '';
    const thumbnails = [];
    let showLimitThumb = [];

    media.forEach((element) => {
      thumbnails.push((
        <SliderElement
          key={element.url}
          title={title}
          src={element.url}
        />
      ));
      showLimitThumb = thumbnails.slice(0, scrollImage);
    });

    const sliderStatus = thumbnails.length > sliderSettings.slidesToShow;
    let classWrapper = 'img-wrapper';
    if (sliderStatus) {
      classWrapper += ' slider-wrapper';
    }

    return (
      <div className="alshaya_search_gallery">
        <div className="alshaya_search_mainimage" ref={this.mainImageRef} data-sku-image={`${mainImageUrl}`}>
          <div className={classWrapper}>
            <ImageElement
              src={mainImageUrl}
              title={title}
              loading="lazy"
            />
            {(sliderStatus && initSlider) ? this.onHoverAppendMarkup(showLimitThumb, thumbnails) : ''}
          </div>
          <Lozenges labels={labels} sku={sku} />
        </div>
      </div>
    );
  }
}

export default SearchGallery;
