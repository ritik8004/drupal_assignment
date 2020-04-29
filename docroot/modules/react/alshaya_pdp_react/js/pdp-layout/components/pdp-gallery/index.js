import React from 'react';

export default class PdpGallery extends React.Component {
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

    for( var i =0; i< this.pdpImageContainer.current.childElementCount; i ++) {
      var figure = this.pdpImageContainer.current.childNodes[i];

      figure.lastElementChild.style.backgroundImage = 'url('+ figure.firstElementChild.getAttribute('data-zoom-url') +')';
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
    return (
      <div className="magv2-pdp-gallery">
        <div className="magazine__gallery--container" ref={this.pdpImageContainer}>
          <figure className="pdp-images" onMouseOver={this.imageZoomIn} onMouseOut={this.imageZoomOut} onMouseMove={this.imagePositionZoom} data-scale="2">
            <img
              data-zoom-url="/modules/react/alshaya_pdp_react/js/pdp-layout/components/assets/images/magazine-1.jpg" 
              src="/modules/react/alshaya_pdp_react/js/pdp-layout/components/assets/images/magazine-1.jpg" 
              alt="img1" title="img-1" />
            <div className="magazine-image-zoom-placeholder"></div>
          </figure>
          <figure className="pdp-images" onMouseOver={this.imageZoomIn} onMouseOut={this.imageZoomOut} onMouseMove={this.imagePositionZoom} data-scale="2">
            <img
              data-zoom-url="/modules/react/alshaya_pdp_react/js/pdp-layout/components/assets/images/magazine-2.jpg" 
              src="/modules/react/alshaya_pdp_react/js/pdp-layout/components/assets/images/magazine-2.jpg" 
              alt="img2" title="img-2" />
            <div className="magazine-image-zoom-placeholder"></div>
          </figure>
          <figure className="pdp-images" onMouseOver={this.imageZoomIn} onMouseOut={this.imageZoomOut} onMouseMove={this.imagePositionZoom} data-scale="2">
            <img
              data-zoom-url="/modules/react/alshaya_pdp_react/js/pdp-layout/components/assets/images/magazine-3.jpg" 
              src="/modules/react/alshaya_pdp_react/js/pdp-layout/components/assets/images/magazine-3.jpg" 
              alt="img3" title="img-3" />
            <div className="magazine-image-zoom-placeholder"></div>
          </figure>
          <figure className="pdp-images" onMouseOver={this.imageZoomIn} onMouseOut={this.imageZoomOut} onMouseMove={this.imagePositionZoom} data-scale="2">
            <img
              data-zoom-url="/modules/react/alshaya_pdp_react/js/pdp-layout/components/assets/images/magazine-4.jpg" 
              src="/modules/react/alshaya_pdp_react/js/pdp-layout/components/assets/images/magazine-4.jpg" 
              alt="img4" title="img-4" />
            <div className="magazine-image-zoom-placeholder"></div>
          </figure>
        </div>
      </div>
    );
  }
}
