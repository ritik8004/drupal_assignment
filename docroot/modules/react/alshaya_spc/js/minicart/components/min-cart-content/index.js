import React from 'react';
import Price from "../../../utilities/price";

class MiniCartContent extends React.Component {
 render() {
   return (
     <React.Fragment>
       <a className={'cart-link-total'} href={'/en/cart'}><Price price={this.props.amount}/></a>
       <a className={'cart-link'} href={'/en/cart'}><span className={'quantity'}>{this.props.qty}</span></a>
     </React.Fragment>
   )
 }
}

export default MiniCartContent;
