import React from 'react';
import PriceElement from "../../../utilities/special-price/PriceElement";

class MiniCartContent extends React.Component {
 render() {
   return (
     <React.Fragment>
       <a className="cart-link-total" href={Drupal.url('cart')}>
         <PriceElement amount={this.props.amount}/>
       </a>
       <a className="cart-link" href={Drupal.url('cart')}>
         <span className="quantity">{this.props.qty}</span>
       </a>
     </React.Fragment>
   )
 }
}

export default MiniCartContent;
