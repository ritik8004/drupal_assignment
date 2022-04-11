import React from 'react';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

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
    return (
      <div className={isPopup ? 'store-info-wrap' : ''}>
        <div className="store-name">
          {specificPlace.store_name}
        </div>
        <div className="store-address">
          <div className="address--line2">
            <ConditionalView condition={hasValue(specificPlace.address)}>
              {specificPlace.address.map((item) => (
                <div key={item.code}>
                  {item.code === 'street' ? <span>{item.value}</span> : null}
                  {item.code === 'address_building_segment' ? item.value : null}
                </div>
              ))}
            </ConditionalView>
          </div>
        </div>
        <div className="store-delivery-time">
          {Drupal.t('Collect from store in ')}
          <em>{specificPlace.sts_delivery_time_label}</em>
        </div>
      </div>
    );
  }
}

export default ListItemClick;
