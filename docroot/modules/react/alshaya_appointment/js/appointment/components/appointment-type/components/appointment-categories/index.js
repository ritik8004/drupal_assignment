import React from 'react';
import SectionTitle from '../../../section-title';
import getStringMessage from '../../../../../../../js/utilities/strings';

export default class AppointmentCategories extends React.Component {
  handleItemClick = (item) => {
    const { handleItemClick } = this.props;
    handleItemClick(item);
  }

  render() {
    const { categoryItems, activeItem } = this.props;

    return (
      <div className="appointment-categories-wrapper appointment-type-item">
        <SectionTitle>
          {getStringMessage('select_program_label')}
          :*
        </SectionTitle>
        <ul className="appointment-categories">
          { categoryItems && categoryItems.map((item) => (
            <li
              key={item.id}
              className={`appointment-category fadeInUp ${item.id} ${activeItem.id === item.id ? ' active' : ''}`}
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
