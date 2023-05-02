import React from 'react';
import Slider from 'react-slick';
import ImageElement from '../imageHelper/ImageElement';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import Lozenges
  from '../../../../common/components/lozenges';
import { isDesktop } from '../../../../../../js/utilities/display';

const SliderElement = ({
  src, title,
}) => (
  <ImageElement
    src={src}
    title={title}
  />
);

const slickEffect = hasValue(drupalSettings.reactTeaserView.swipeImage.slideEffect)
  ? drupalSettings.reactTeaserView.swipeImage.slideEffect
  : null;

// Common slider configurations for all viewports.
const sliderSettings = {
  infinite: true,
  slidesToShow: 1,
  slidesToScroll: 1,
  arrows: false,
  touchThreshold: 1750,
  variableWidth: false,
  pauseOnHover: false,
};

// Slider configurations for desktop devices.
const sliderHoverSettings = {
  ...sliderSettings,
  fade: slickEffect === 'fade',
  dots: true,
  autoplaySpeed: hasValue(drupalSettings.reactTeaserView.swipeImage.imageSlideTiming)
    ? drupalSettings.reactTeaserView.swipeImage.imageSlideTiming * 1000
    : 2000,
  autoplay: true,
};

// Slider configurations for mobile devices.
const swipeSettings = {
  ...sliderSettings,
  dots: false,
  autoplay: false,
  initialSlide: 1,
  swipe: true,
  touchThreshold: 40,
};

// Slider configurations based on device.
const slideSettings = !isDesktop() ? swipeSettings : sliderHoverSettings;
let slickClassName = 'search-lightSlider';

if (!isDesktop()) {
  slickClassName += ' search-lightSliderSwipe';
}

if (isDesktop() && slickEffect) {
  slickClassName += ` slick-effect-${slickEffect}`;
}
class SearchGallery extends React.PureComponent {
  constructor(props) {
    super(props);
    this.mainImageRef = React.createRef();
    this.slideAppendMarkup = this.slideAppendMarkup.bind(this);
  }


  // Function for slick carousel initialization.
  slideAppendMarkup = (thumbnails) => (
    <div className="alshaya_search_slider">
      <Slider {...slideSettings} className={slickClassName} ref={this.getref}>
        {thumbnails}
      </Slider>
    </div>
  )

  getref = (slider) => {
    const { setSlider } = this.props;
    setSlider(slider);
  }

  render() {
    // Get no Of Slides To Show in Desktop view.
    const noOfSlidesToShowDesktop = drupalSettings.reactTeaserView.swipeImage.noOfImageScroll;
    const {
      media, title, labels, sku, initSlider,
    } = this.props;
    const mainImage = media.length ? media[0] : {};
    const mainImageUrl = hasValue(mainImage.url) ? mainImage.url : '';
    let thumbnails = [];

    media.forEach((element) => {
      thumbnails.push((
        <SliderElement
          key={element.url}
          title={title}
          src={element.url}
        />
      ));
    });

    // Set no Of Slides in thumbnails object.
    if (isDesktop()) {
      thumbnails = thumbnails.slice(0, noOfSlidesToShowDesktop);
    }
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
            {(sliderStatus && initSlider) ? this.slideAppendMarkup(thumbnails) : ''}
          </div>
          <Lozenges labels={labels} sku={sku} />
        </div>
      </div>
    );
  }
}

export default SearchGallery;
