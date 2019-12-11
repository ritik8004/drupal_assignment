import React from 'react';
import Slider from "react-slick";
import { ImageWrapper } from '../imageHelper/ImageWrapper';
import ImageLazyLoad from '../imageHelper/ImageLazyLoad';
import { updateAfter } from '../../../utils';
import ImageElement from '../imageHelper/ImageElement';

const SliderElement = props => {
  return(
    <div
      onMouseEnter={props.mouseenter.bind(this)}
      onMouseLeave={props.mouseout.bind(this)}
    >
      <ImageLazyLoad
        src={drupalSettings.reactTeaserView.gallery.lazy_load_placeholder}
        data-src={props.src}
        title={props.title}
        className="b-lazy"
      />
    </div>
  );
};

const sliderSettings = {
  dots: false,
  infinite: false,
  slidesToShow: drupalSettings.reactTeaserView.gallery.plp_slider.item,
  slidesToScroll: 1,
  vertical: false,
  arrows: true,
  touchThreshold: 1000,
  variableWidth: false,
};


class SearchGallery extends React.PureComponent {

  static defaultProps = {
    media: [],
  }

  constructor(props) {
    super(props);
    this.mainImageRef = React.createRef();
    this.mainImage = props.media.length > 0 ? props.media[0] : {};
  }

  changeImg = event => {
    clearTimeout(this.setTimeoutConst);
    this.setTimeoutConst = null;
    if (event.target.hasAttribute("src") && event.target.getAttribute("src").length > 0) {
      this.mainImageRef.current.firstChild.src = event.target.getAttribute("src");
    }
  };

  resetImg = () => {
    const obj = this;
    this.setTimeoutConst = setTimeout(function() {
      obj.mainImageRef.current.firstChild.src = obj.mainImage.url
    }, updateAfter);
  };

  render() {
    const { media, title } = this.props;

    const origObj = this;
    const thumbnails = [];
    media.forEach((element, index) => {
      thumbnails.push((
        <SliderElement
          key={element.url}
          title={title}
          src={element.url}
          mouseenter={origObj.changeImg}
          mouseout={origObj.resetImg}
        />
      ));
    });

    const sliderStatus = thumbnails.length > sliderSettings.slidesToShow ? 'true' : 'false';

    return (
      <div className="alshaya_search_gallery">
        <div className='alshaya_search_mainimage' ref={this.mainImageRef}>
          <ImageElement
            src={drupalSettings.reactTeaserView.gallery.lazy_load_placeholder}
            data-src={typeof this.mainImage.url != 'undefined' ? this.mainImage.url : ''}
            title={title}
            className='b-lazy'
          />
        </div>
        <div className="alshaya_search_slider" data-slider-status={sliderStatus}>
          <Slider {...sliderSettings} className="search-lightSlider">
            {thumbnails}
          </Slider>
        </div>
      </div>
    );
  }
}

export default SearchGallery;
