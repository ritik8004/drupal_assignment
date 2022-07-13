import React from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import AddressHours from '../AddressHours';

export class ListItemClick extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  render() {
    const {
      specificPlace,
      isPopup,
    } = this.props;
    const { address } = specificPlace;
    return (
      <div className={isPopup ? 'store-info-wrap' : ''}>
        <div className="store-name">
          {specificPlace.store_name}
        </div>
        <div className="store-address">
          <ConditionalView condition={hasValue(address)}>
            <AddressHours
              type="addresstext"
              address={address}
              classname="address--line2"
            />
          </ConditionalView>
        </div>
        <div className="store-delivery-time">
          { Drupal.t('Collect from store in @time', { '@time': specificPlace.sts_delivery_time_label }, { context: 'click and collect' })}
        </div>
      </div>
    );
  }
}

export default ListItemClick;
