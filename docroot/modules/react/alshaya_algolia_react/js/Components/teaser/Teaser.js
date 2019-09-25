import React from 'react';
import Gallery from '../gallery/Gallery';
import PriceBlockWrapper from '../price/PriceBlockWrapper';
import Promotion from '../Promotions/Promotion';

class Teaser extends React.Component {

  render() {
    const {hit} = this.props;
    const promotionList = (this.props.promotions) ? this.props.promotions.map(promotion => <Promotion info={promotion}/>) : '';
    const promotions = (promotions != '' && promotions != 'null') ? <div className="promotions">{promotions}</div> : '';
    const swatches = '';

    return (
      <article>
        <div className="content">
          <div className="field field--name-field-skus field--type-sku field--label-hidden field__items">
            <div className="field field--name-field-skus field--type-sku field--label-hidden field__item">
              <a
                href={hit.url}
                data--original-url={hit.url}
                className="list-product-gallery product-selected-url">
                <Gallery media={hit.media} title={hit.title} />
              </a>
              <div className="product-plp-detail-wrapper">
                <h2 className="field--name-name">
                  <a href={hit.url} className="product-selected-url">{hit.title}</a>
                </h2>
                <PriceBlockWrapper price={hit.original_price} final_price={hit.final_price}/>
                {promotions}
                {swatches}
              </div>
            </div>
          </div>
          <div className="labels-container" data-type="plp" data-sku={hit.sku} data-main-sku={hit.sku}>
          </div>
        </div>
      </article>
    );
  }

}

export default Teaser;
