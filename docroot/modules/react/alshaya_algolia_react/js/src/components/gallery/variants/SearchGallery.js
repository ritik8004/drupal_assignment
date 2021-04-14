import React from 'react';
import Slider from 'react-slick';
import Labels from '../../labels';
import { updateAfter } from '../../../utils';
import ImageElement from '../imageHelper/ImageElement';
import ConditionalView from '../../../../common/components/conditional-view';

const SliderElement = ({
  src, title, mouseenter, mouseout,
}) => (
  <ImageElement
    src={src}
    title={title}
    onMouseOver={mouseenter}
    onMouseOut={mouseout}
  />
);

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
  constructor(props) {
    super(props);
    this.mainImageRef = React.createRef();
    this.mainImage = props.media.length > 0 ? props.media[0] : {};
  }

  changeImg = (event) => {
    clearTimeout(this.setTimeoutConst);
    this.setTimeoutConst = null;
    if (event.target.hasAttribute('src') && event.target.getAttribute('src').length > 0) {
      this.mainImageRef.current.firstChild.src = event.target.getAttribute('src');
    }
  };

  resetImg = () => {
    const obj = this;
    this.setTimeoutConst = setTimeout(() => {
      obj.mainImageRef.current.firstChild.src = obj.mainImage.url;
    }, updateAfter);
  };

  render() {
    const {
      media, title, initiateSlider, labels, sku,
    } = this.props;
    const origObj = this;
    const mainImageUrl = typeof this.mainImage.url !== 'undefined' ? this.mainImage.url : '';
    const thumbnails = [];
    let sliderStatus = false;

    if (initiateSlider) {
      media.forEach((element) => {
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

      sliderStatus = thumbnails.length > sliderSettings.slidesToShow ? 'true' : 'false';
    }

    return (
      <div className="alshaya_search_gallery">
        <div className="alshaya_search_mainimage" ref={this.mainImageRef} data-sku-image={`${mainImageUrl}`}>
          <ImageElement
            src={drupalSettings.reactTeaserView.gallery.lazy_load_placeholder}
            data-src={mainImageUrl}
            title={title}
            className="b-lazy"
          />
          <Labels labels={labels} sku={sku} />
        </div>
        <ConditionalView condition={initiateSlider}>
          <div className="alshaya_search_slider" data-slider-status={sliderStatus}>
            <Slider {...sliderSettings} className="search-lightSlider">
              {thumbnails}
            </Slider>
          </div>
        </ConditionalView>
      </div>
    );
  }
}

export default SearchGallery;
