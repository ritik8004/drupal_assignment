import React from 'react';
import Slider from "react-slick";
import { ImageWrapper } from '../imageHelper/ImageWrapper';
import ImageLazyLoad from '../imageHelper/ImageLazyLoad';
import { updateAfter } from '../../../utils';

const SliderElement = props => {
  return(
    <div
      onMouseEnter={props.mouseenter.bind(this)}
      onMouseOut={props.mouseout.bind(this)}
    >
      <ImageLazyLoad
        src={props.src}
        title={props.title}
        className="b-lazy b-loaded"
      />
    </div>
  );
};

class SearchGallery extends React.Component {

  static defaultProps = {
    media: [],
  }

  constructor(props) {
    super(props);
    this.mainImage = props.media.length > 0 ? props.media[0] : {};
    this.state = {
      mainImage: this.mainImage
    };
    this.setTimeoutConst = null;

    this.settings = {
      dots: false,
      infinite: false,
      slidesToShow: drupalSettings.reactTeaserView.gallery.plp_slider.item,
      slidesToScroll: 1,
      vertical: false,
      arrows: true,
      touchThreshold: 1000,
      variableWidth: false,
    };
  }

  changeImg = event => {
    clearTimeout(this.setTimeoutConst);
    if (event.target.hasAttribute("src") && event.target.getAttribute("src").length > 0) {
      this.setState({ mainImage: {url: event.target.getAttribute("src")} });
    }
  };

  resetImg = event => {
    const obj = this;
    this.setTimeoutConst = setTimeout(function() {
      obj.setState({ mainImage: obj.mainImage });
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
          src={element.url}
          title={title}
          mouseenter={origObj.changeImg}
          mouseout={origObj.resetImg}
        />
      ));
    });

    const sliderStatus = thumbnails.length > this.settings.slidesToShow ? 'true' : 'false';

    return (
      <div className="alshaya_search_gallery">
        <ImageWrapper
          src={typeof this.state.mainImage.url != 'undefined' ? this.state.mainImage.url : ''}
          title={title}
          className='alshaya_search_mainimage'
          showDefaultImage={true}
        />
        <div className="alshaya_search_slider" data-slider-status={sliderStatus}>
          <Slider {...this.settings} className="search-lightSlider">
            {thumbnails}
          </Slider>
        </div>
      </div>
    );
  }
}

export default SearchGallery;
