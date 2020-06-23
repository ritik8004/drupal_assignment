import React from 'react';

export default class AppointmentCategories extends React.Component {
  handleItemClick = (item) => {
    const { handleItemClick } = this.props;
    handleItemClick(item);
  }

  render() {
    const { categoryItems, activeItem } = this.props;
    return (
      <div className="appointment-categories-wrapper">
        <label>
          {Drupal.t('Select Appointment Category')}
          :*
        </label>
        <ul className="appointment-categories">
          { categoryItems && categoryItems.map((item) => (
            <li
              className={activeItem === item.id ? 'appointment-category active' : 'appointment-category'}
              onClick={() => this.handleItemClick(item)}
            >
              <span className={`appointment-category-icon ${item.id}`} />
              <span className="appointment-category-title">{item.name}</span>
            </li>
          ))}
        </ul>
      </div>
    );
  }
}
