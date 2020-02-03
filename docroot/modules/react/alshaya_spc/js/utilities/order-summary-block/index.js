import React from 'react';
import SectionTitle from '../section-title';
import TotalLineItems from '../total-line-items';
import CheckoutCartItems from '../../checkout/components/checkout-cart-items';

class OrderSummaryBlock extends React.Component {
 render() {
   const promo_data = this.props.cart_promo ? this.props.cart_promo : null;
   let order_summary_title = Drupal.t('order summary');
   if (this.props.item_qty !== undefined) {
     // Thi to be used on checkout page.
     order_summary_title = Drupal.t('order summary (@qty items)', {'@qty': this.props.item_qty});
   }

   return (
     <div className="spc-order-summary-block">
       <SectionTitle>{order_summary_title}</SectionTitle>
       {/*To Be used on Checkout Delivery pages.*/}
         {!this.props.show_checkout_button &&
          <div className="product-content">
            <CheckoutCartItems items={this.props.items}/>
          </div>
         }
       <div className="block-content">
         {/*To Be used later on Checkout Delivery pages.*/}
         <div className="products"/>
         <TotalLineItems totals={this.props.totals} cart_promo={promo_data}/>
         {/*To Be used on cart page only.*/}
         {this.props.show_checkout_button &&
          <div className="actions">
           <div className="checkout-link submit">
             <a href={Drupal.url('cart/login')} className="checkout-link">{Drupal.t('continue to checkout')}</a>
           </div>
         </div>
         }
       </div>
     </div>
   );
 }
}

export default OrderSummaryBlock;
