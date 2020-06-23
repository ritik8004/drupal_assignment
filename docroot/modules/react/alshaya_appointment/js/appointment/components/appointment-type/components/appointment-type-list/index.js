import React from 'react';
import ReadMoreAndLess from 'react-read-more-less';

export default class AppointmentTypeList extends React.Component {
  handleChange = (e) => {
    const { handleChange } = this.props;
    handleChange(e);
  }

  render() {
    const { appointmentTypeItems, activeItem } = this.props;
    let description = '';

    return (
      <div className="appointment-type-list-wrapper">
        <label>
          {Drupal.t('Appointment Type')}
          :*
        </label>
        <div className="appointment-type-list-inner-wrapper">
          <select
            className="appointment-type-select"
            name="appointmentType"
            onChange={this.handleChange}
          >
            {appointmentTypeItems && appointmentTypeItems.map((v) => {
              if (activeItem === v.id) {
                description = v.description;
              }

              return (
                <option
                  value={v.id}
                  selected={activeItem === v.id}
                  name={v.name}
                >
                  {v.name}
                </option>
              );
            })}
          </select>
          { activeItem
            ? (
              <ReadMoreAndLess
                charLimit={250}
                readMoreText={Drupal.t('Read More')}
                readLessText={Drupal.t('Show Less')}
              >
                {description}
              </ReadMoreAndLess>
            )
            : null}
        </div>
      </div>
    );
  }
}
