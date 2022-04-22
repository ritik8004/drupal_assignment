import React from 'react';
import ConditionalView from '../../../../../../js/utilities/components/conditional-view';

/**
 * Return the default shipping element.
 */
const DefaultShippingElement = ({ method, price }) => (
  <>
    <label className="radio-sim radio-label">
      <span className="carrier-title">{method.carrier_title}</span>
      <span className="method-title">{method.method_title}</span>
      <span className="spc-price">{price}</span>
    </label>
    <ConditionalView condition={!method.available}>
      <div className="method-error-message">{method.error_message}</div>
    </ConditionalView>
  </>
);

export default React.memo(DefaultShippingElement);
