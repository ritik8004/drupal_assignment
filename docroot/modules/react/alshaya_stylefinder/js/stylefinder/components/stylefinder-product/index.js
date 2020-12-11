import React from 'react';

export default class StyleFinderProduct extends React.Component {
  constructor() {
    super();
    this.state = {};
  }

  render() {
    const { startegyId, item } = this.props;

    const { path } = drupalSettings;
    const { baseUrl, pathPrefix } = path;

    return (
      <a
        className="dy-recommendation-product product-quick-view-link swiper-slide-active"
        href={item.url}
        data-dy-sku={item.sku}
        data-dialog-type="modal"
        data-sku={item.sku}
        data-url-quick-view={`${baseUrl}${pathPrefix}product-quick-view/${item.sku}/nojs`}
      >
        <div data-dy-product-id={item.sku} data-dy-strategy-id={startegyId}>
          <img src={item.image_url} />
          <div className="bf-slide-copy">
            <div className="bf-slide-copy-inner">
              {item.name}
            </div>
          </div>
        </div>
      </a>
    );
  }
}
