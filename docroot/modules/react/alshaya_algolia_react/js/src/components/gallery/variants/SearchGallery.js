import React from 'react';
import Slider from 'react-slick';
import ImageElement from '../imageHelper/ImageElement';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
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

const sliderSettings = {
  dots: true,
  infinite: true,
  slidesToShow: 1,
  slidesToScroll: 1,
  arrows: false,
  touchThreshold: 1750,
  variableWidth: false,
  autoplaySpeed: hasValue(drupalSettings.reactTeaserView.gallery.image_slide_timing)
    ? drupalSettings.reactTeaserView.gallery.image_slide_timing * 1000
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

  onHoverAppendMarkup = (thumbnails) => (
    <div className="alshaya_search_slider">
      <Slider {...sliderSettings} className="search-lightSlider" ref={this.getref}>
        {thumbnails}
      </Slider>
    </div>
  )

  getref = (slider) => {
    const { setSlider } = this.props;
    setSlider(slider);
  }

  render() {
    const {
      media, title, labels, sku, initSlider,
    } = this.props;
    const mainImage = media.length ? media[0] : {};
    const mainImageUrl = hasValue(mainImage.url) ? mainImage.url : '';
    const thumbnails = [];

    media.forEach((element) => {
      thumbnails.push((
        <SliderElement
          key={element.url}
          title={title}
          src={element.url}
        />
      ));
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
            {(sliderStatus && initSlider) ? this.onHoverAppendMarkup(thumbnails) : ''}
          </div>
          <Lozenges labels={labels} sku={sku} />
        </div>
      </div>
    );
  }
}

export default SearchGallery;
