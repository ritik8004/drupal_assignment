import React from 'react';
import Gallery from '../gallery/Gallery';
import Price from '../price/Price';

class Teaser extends React.Component {

  render() {
    return (
      <article>
        <div className="content">
          <div className="field field--name-field-skus field--type-sku field--label-hidden field__items">
            <div className="field field--name-field-skus field--type-sku field--label-hidden field__item">
              <a href="/en/buy-trousers-high-slits-blackfloral.html"
              data--original-url="/en/buy-trousers-high-slits-blackfloral.html"
              class="list-product-gallery product-selected-url">
                <Gallery />
              </a>
              <div className="product-plp-detail-wrapper">
                <h2 className="field--name-name">
                  <a href="/en/buy-trousers-high-slits-blackfloral.html" class="product-selected-url">Trousers with high
                    slits</a>
                </h2>
                <Price />
              </div>
            </div>
          </div>
          <div className="labels-container" data-type="plp" data-sku="_716958002" data-main-sku="_716958002">
          </div>
        </div>
      </article>
    );
  }

}

export default Teaser;
