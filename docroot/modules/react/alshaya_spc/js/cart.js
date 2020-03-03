import ReactDOM from 'react-dom';

import Cart from './cart/components/cart';
import { checkCartCustomer } from './utilities/cart_customer_util';

ReactDOM.render(
  <Cart />,
  document.getElementById('spc-cart'),
);
