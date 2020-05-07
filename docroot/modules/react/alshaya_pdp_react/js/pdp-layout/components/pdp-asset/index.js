import React from 'react';

export default class PdpAsset extends React.Component {
  static imageZoomIn(event) {
    let el = event.target;

    if (el.nodeName !== 'FIGURE') {
      el = el.parentNode;
    }

    el.classList.add('magazine-image-zoomed');
    el.lastElementChild.style.transform = `scale(${el.getAttribute('data-scale')})`;
  }

  static imageZoomOut(event) {
    let el = event.target;

    if (el.nodeName !== 'FIGURE') {
      el = el.parentNode;
    }

    el.classList.remove('magazine-image-zoomed');
    el.lastElementChild.style.transform = 'scale(1)';
  }

  static imagePositionZoom(event) {
    let el = event.target;

    if (el.nodeName !== 'FIGURE') {
      el = el.parentNode;
    }

    el.lastElementChild.style.transformOrigin = `${(((event.pageX - window.pageXOffset) - el.firstElementChild.getBoundingClientRect().left) / el.firstElementChild.offsetWidth) * 100}% ${(((event.pageY - window.pageYOffset) - el.firstElementChild.getBoundingClientRect().top) / el.firstElementChild.offsetHeight) * 100}%`;
  }

  openFullScreenView = (event) => {
    const { onClick } = this.props;
    onClick(event);
  }

  render() {
    const {
      type, imageZoomUrl, imageUrl, alt, title, viewport, index,
    } = this.props;

    if (type === 'image' && viewport !== 'mobile') {
      return (
        <figure
          className="magv2-pdp-image"
          onMouseOver={PdpAsset.imageZoomIn}
          onMouseOut={PdpAsset.imageZoomOut}
          onMouseMove={PdpAsset.imagePositionZoom}
          onClick={this.openFullScreenView}
          data-scale="2"
          data-index={index}
        >
          <img
            src={imageUrl}
            alt={alt}
            title={title}
          />
          <div className="magazine-image-zoom-placeholder" style={{ backgroundImage: `url(${imageZoomUrl})` }} />
        </figure>
      );
    }
    return null;
  }
}
