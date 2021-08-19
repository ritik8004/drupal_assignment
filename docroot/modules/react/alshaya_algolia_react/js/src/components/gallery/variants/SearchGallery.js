import React from 'react';
import Slider from 'react-slick';
import ImageElement from '../imageHelper/ImageElement';
import Lozenges
  from '../../../../common/components/lozenges';

const SliderElement = ({
  src, title, width, height,
}) => (
  <ImageElement
    src={src}
    title={title}
    width={width}
    height={height}
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
  autoplaySpeed: 1750,
  autoplay: true,
  pauseOnHover: false,
};

class SearchGallery extends React.PureComponent {
  constructor(props) {
    super(props);
    this.mainImageRef = React.createRef();
    this.mainImage = props.media.length > 0 ? props.media[0] : {};
    this.onHoverAppendMarkup = this.onHoverAppendMarkup.bind(this);
  }

  onHoverAppendMarkup = (sliderStatus, thumbnails) => (
    <div className="alshaya_search_slider" data-slider-status={sliderStatus}>
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
    const mainImageUrl = typeof this.mainImage.url !== 'undefined' ? this.mainImage.url : '';
    const thumbnails = [];
    // Dimensions.
    const {
      width, height,
    } = this.mainImage;

    media.forEach((element) => {
      thumbnails.push((
        <SliderElement
          key={element.url}
          title={title}
          src={element.url}
          width={element.width}
          height={element.height}
        />
      ));
    });

    const sliderStatus = thumbnails.length > sliderSettings.slidesToShow ? 'true' : 'false';

    return (
      <div className="alshaya_search_gallery">
        <div className="alshaya_search_mainimage" ref={this.mainImageRef} data-sku-image={`${mainImageUrl}`}>
          <div className="img-wrapper">
            <ImageElement
              src={drupalSettings.reactTeaserView.gallery.lazy_load_placeholder}
              data-src={mainImageUrl}
              title={title}
              className="b-lazy"
              width={width}
              height={height}
            />
            {initSlider ? this.onHoverAppendMarkup(sliderStatus, thumbnails) : ''}
          </div>
          <Lozenges labels={labels} sku={sku} />
        </div>
      </div>
    );
  }
}

export default SearchGallery;
