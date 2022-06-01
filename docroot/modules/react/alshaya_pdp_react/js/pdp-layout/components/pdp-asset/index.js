import React from 'react';

export default class PdpAsset extends React.Component {
  static imageZoomIn(event) {
    let el = event.target;

    while (el.nodeName !== 'FIGURE') {
      el = el.parentNode.parentNode;
    }

    el.classList.add('magazine-image-zoomed');
    const wrapper = el.children[0];
    wrapper.lastElementChild.style.transform = `scale(${el.getAttribute('data-scale')})`;
  }

  static imageZoomOut(event) {
    let el = event.target;

    while (el.nodeName !== 'FIGURE') {
      el = el.parentNode.parentNode;
    }

    el.classList.remove('magazine-image-zoomed');
    const wrapper = el.children[0];
    wrapper.lastElementChild.style.transform = 'scale(1)';
  }

  static imagePositionZoom(event) {
    let el = event.target;

    while (el.nodeName !== 'FIGURE') {
      el = el.parentNode.parentNode;
    }

    const wrapper = el.children[0];

    wrapper.lastElementChild.style.transformOrigin = `${(((event.pageX - window.pageXOffset) - wrapper.firstElementChild.getBoundingClientRect().left) / wrapper.firstElementChild.offsetWidth) * 100}% ${(((event.pageY - window.pageYOffset) - wrapper.firstElementChild.getBoundingClientRect().top) / wrapper.firstElementChild.offsetHeight) * 100}%`;
  }

  openFullScreenView = (event) => {
    const { onClick } = this.props;
    onClick(event);
  }

  render() {
    const {
      type, imageZoomUrl, imageUrl, alt, title, viewport, index, children,
    } = this.props;

    if (type === 'image' && viewport !== 'mobile') {
      return (
        <figure
          className="magv2-pdp-image fadeInUp"
          onMouseOver={PdpAsset.imageZoomIn}
          onMouseOut={PdpAsset.imageZoomOut}
          onMouseMove={PdpAsset.imagePositionZoom}
          onClick={this.openFullScreenView}
          data-scale="2"
          data-index={index}
        >
          <div className="magv2-pdp-image-zoom-wrapper">
            <img
              src={imageUrl}
              alt={alt}
              title={title}
              loading={index <= 1 ? 'eager' : 'lazy'}
            />
            <div className="magazine-image-zoom-placeholder" style={{ backgroundImage: `url(${imageZoomUrl})` }} />
          </div>
          {children}
        </figure>
      );
    }
    return null;
  }
}
