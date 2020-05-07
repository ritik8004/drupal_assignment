import React from 'react';

export default class PdpImageElement extends React.Component {
  constructor(props) {
    super(props);
  }

  imageClick = (event) => {
    const { onClick } = this.props;
    onClick(event);
  }

  render() {
    const {
      imageUrl, alt, title, index,
    } = this.props;

    return (
      <div className="magv2-pdp-image" onClick={this.imageClick}>
        <img
          src={imageUrl}
          alt={alt}
          title={title}
          data-index={index}
        />
      </div>
    );
  }
}
