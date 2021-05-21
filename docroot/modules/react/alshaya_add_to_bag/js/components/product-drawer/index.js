import React from 'react';
import ConditionalView from '../../../../alshaya_pdp_react/js/common/components/conditional-view';
import getStringMessage from '../../../../js/utilities/strings';

const ProductDrawer = (props) => {
  // direction = left/right.
  // status = opened/closed.
  const {
    direction, status, children, onDrawerClose,
  } = props;

  return (
    <div className={`product-drawer-container ${direction} ${status}`}>
      <ConditionalView condition={status !== 'closed'}>
        <div className="product-drawer-header-wrapper">
          <label>
            {getStringMessage('quick_view')}
          </label>
          <button type="button" className="close-button" onClick={onDrawerClose} />
        </div>
        <div className="product-drawer-content-wrapper">{children}</div>
      </ConditionalView>
    </div>
  );
};

export default ProductDrawer;
