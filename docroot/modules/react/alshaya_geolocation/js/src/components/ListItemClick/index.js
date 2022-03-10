import React from 'react';

export class ListItemClick extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  render() {
    const { specificPlace } = this.props;
    return (
      <div>
        <div className="row-title">
          <span>
            {specificPlace.store_name}
          </span>
        </div>
        <div className="views-row">
          <div className="views-field-field-store-address">
            <div className="field-content">
              <div className="address--line2">
                {specificPlace.address.map((item) => (
                  <div key={item.code}>
                    {item.code === 'address_building_segment' ? item.value : null}
                    {item.code === 'street' ? <span>{item.value}</span> : null}
                  </div>
                ))}
              </div>
              <div className="field field--name-field-available">
                {Drupal.t('Collect from store in ')}
                {specificPlace.sts_delivery_time_label}
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
}

export default ListItemClick;
