import React from 'react';
import PdpInfo from '../pdp-info';
import PdpPopupContainer from '../utilities/pdp-popup-container';
import PdpPopupWrapper from '../utilities/pdp-popup-wrapper';

const CrossellPopupContent = (props) => {
  const {
    finalPrice, closeModal,
  } = props;

  return (
    <PdpPopupContainer className="magv2-crossell-popup-container">
      <PdpPopupWrapper className="magv2-crossell-popup-wrapper">
        <div className="magv2-crossell-popup-header-wrapper">
          <a className="close" onClick={() => closeModal()}>
            &times;
          </a>
          <label>{Drupal.t('Quick View')}</label>
        </div>
        <div className="magv2-crossell-popup-content-wrapper">
          <PdpInfo
            title="Nike Air Force 1 Mid - Men's Shoes - Test-test"
            finalPrice={finalPrice}
            pdpProductPrice="559"
          />
          <div className="promotions promotions-full-view-mode">
            <p>
              <a href="buy-1-get-1-free-dee/">Buy 1 get 1 free - Dee</a>
            </p>
          </div>
          <div className="pdp-cart-form fadeInUp notInMobile">
            <form action="#" className="sku-base-form" method="post" id="pdp-add-to-cart-form" parentsku="LF314102662104" variantselected="LF314102662104080" noValidate="novalidate" data-drupal-form-fields="">
              <div id="add-to-cart-error" className="error" />
              <div className="cart-form-attribute color">
                <div className="non-groupped-attr">
                  <ul id="color" className="select-attribute">
                    <li id="value505" className="active" value="505">
                      <a href="#" style={{ backgroundImage: 'url("/sites/g/files/flsa/media/website/var/assets/FootLocker/314102442804_01.349717.jpg")' }} />
                    </li>
                    <li id="value2650" className="in-active" value="2650">
                      <a href="#" style={{ backgroundImage: 'url("/sites/g/files/flsa/media/website/var/assets/FootLocker/314102442804_01.349717.jpg")' }} />
                    </li>
                  </ul>
                </div>
              </div>
              <div className="cart-form-attribute size_shoe_eu">
                <div className="grouped-attr" />
              </div>
              <div className="magv2-size-btn-wrapper">EU, 41</div>
              <div id="product-quantity-dropdown" className="magv2-qty-wrapper">
                <div className="magv2-qty-container">
                  <button type="submit" className="magv2-qty-btn magv2-qty-btn--down" disabled="" />
                  <input type="text" id="qty" className="magv2-qty-input" readOnly="" value="1" />
                  <button type="submit" className="magv2-qty-btn magv2-qty-btn--up" />
                </div>
              </div>
              <div className="magv2-add-to-basket-container" data-top-offset="158">
                <button className="magv2-button" id="add-to-cart-main" type="submit">Add To Bag</button>
              </div>
            </form>
          </div>
        </div>
      </PdpPopupWrapper>
    </PdpPopupContainer>
  );
};
export default CrossellPopupContent;
