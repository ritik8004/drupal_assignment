import React from 'react';

const categoryItems = ['Pharmacy', 'Opticians', 'Hearing Care', 'Beauty'];

export default class AppointmentCategories extends React.Component {
  handleItemClick = (item) => {
    const { handleItemClick } = this.props;
    handleItemClick(item);
  }

  render() {
    const { activeItem } = this.props;
    return (
      <div className="appointment-categories-wrapper">
        <label>
          {Drupal.t('Select Appointment Category')}
          :*
        </label>
        <ul className="appointment-categories">
          { categoryItems.map((item) => (
            <li
              className={activeItem === item ? 'appointment-category active' : 'appointment-category'}
              onClick={() => this.handleItemClick(item)}
            >
              <span className="appointment-category-icon" />
              <span className="appointment-category-title">{item}</span>
            </li>
          ))}
        </ul>
      </div>
    );
  }
}
