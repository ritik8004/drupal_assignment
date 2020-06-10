import React from 'react';

const categoryItems = ['Pharmacy', 'Opticians', 'Hearing Care', 'Beauty'];

export default class AppointmentCategories extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      activeItem: -1,
    };
  }

  handleItemClick = (index) => {
    this.setState({
      activeItem: index
    });
  }

  render () {
    return (
      <div className="appointment-categories-wrapper">
        <label>{Drupal.t('Select Appointment Category')}:*</label>
        <ul className="appointment-categories">
          { categoryItems.map((item, index) =>
            <li 
              className={this.state.activeItem === index ? 'appointment-category active' : 'appointment-category'}
              key={index}
              onClick={() => this.handleItemClick(index)}
            >
              <span className="appointment-category-icon"></span>
              <span className="appointment-category-title">{item}</span>
            </li>
          )}
        </ul>
      </div>
    );
  };

}