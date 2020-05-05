import React from 'react';

export default class PdpAsset extends React.Component {
  constructor(props) {
    super(props);

    this.pdpImageContainer = React.createRef();
    this.imageZoomIn = this.imageZoomIn.bind(this);
    this.imageZoomOut = this.imageZoomOut.bind(this);
    this.imagePositionZoom = this.imagePositionZoom.bind(this);

  }

  imageZoomIn(event) {
    var el = event.target;

    if(el.nodeName !== 'FIGURE') {
      el = el.parentNode;
    }

    el.classList.add('magazine-image-zoomed');
    el.lastElementChild.style.transform = 'scale('+ el.getAttribute('data-scale') +')';
  }

  imageZoomOut(event) {
    var el = event.target;

    if(el.nodeName !== 'FIGURE') {
      el = el.parentNode;
    }

    el.classList.remove('magazine-image-zoomed');
    el.lastElementChild.style.transform = 'scale(1)';
  }

  imagePositionZoom(event) {
    var el = event.target;

    if(el.nodeName !== 'FIGURE') {
      el = el.parentNode;
    }

    el.lastElementChild.style.transformOrigin = (((event.pageX - window.pageXOffset) - el.firstElementChild.getBoundingClientRect().left) / el.firstElementChild.offsetWidth) * 100 + '% ' + (((event.pageY - window.pageYOffset) - el.firstElementChild.getBoundingClientRect().top) / el.firstElementChild.offsetHeight) * 100 +'%';
  }

  render() {
    const {type, imageZoomUrl, imageUrl, alt, title} = this.props;

    if(type == 'image') {
      return (
        <figure className="magv2-pdp-images" onMouseOver={this.imageZoomIn} onMouseOut={this.imageZoomOut} onMouseMove={this.imagePositionZoom} data-scale="2">
          <img
            src={imageUrl}
            alt={alt} title={title} />
          <div className="magazine-image-zoom-placeholder" style={{backgroundImage: 'url(' + imageZoomUrl + ')'}}></div>
        </figure>
      );
    }
  }
}
