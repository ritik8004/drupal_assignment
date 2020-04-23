import React from 'react';

export default class PdpGallery extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      galleryMarkup: null,
    };
  }

  componentDidMount() {
    const { skuCode, productInfo } = this.props;
    this.setState({
      galleryMarkup: productInfo[skuCode].gallery,
    });
  }

  render() {
    const { galleryMarkup } = this.state;

    return (
      <>
        <div className="pdp-gallery"><div dangerouslySetInnerHTML={ { __html: galleryMarkup} }></div></div>
      </>
    );
  }

}
