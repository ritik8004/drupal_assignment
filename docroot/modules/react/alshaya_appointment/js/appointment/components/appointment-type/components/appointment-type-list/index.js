import React from 'react';
import ReadMoreAndLess from 'react-read-more-less';

const AppointmentTypeItems = [
  { value: '', label: 'Please Select' },
  { value: 'WInter Flu Jab Service (KD 22- 45)', label: 'WInter Flu Jab Service (KD 22- 45)' },
  { value: 'Pneumonia Vaccination Service (KD 11- 17)', label: 'Pneumonia Vaccination Service (KD 11- 17)' },
  { value: 'Travel Vaccination and Advice Service (KD 27- 51)', label: 'Travel Vaccination and Advice Service (KD 27- 51)' },
  { value: 'Malaria Prevention Service (KD 9 - 18)', label: 'Malaria Prevention Service (KD 9 - 18)' },
];

const AppointmentTypeDescription = "There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don't look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet. It uses a dictionary of over 200 Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.";

export default class AppointmentTypeList extends React.Component {
  handleChange = (e) => {
    const { handleChange } = this.props;
    handleChange(e);
  }

  render() {
    const { activeItem } = this.props;
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
            {AppointmentTypeItems.map((v) => (
              <option
                value={v.value}
                selected={activeItem === v.value}
              >
                {v.label}
              </option>
            ))}
          </select>
          { activeItem
            ? (
              <ReadMoreAndLess
                charLimit={250}
                readMoreText="Read More"
                readLessText="Show Less"
              >
                {AppointmentTypeDescription}
              </ReadMoreAndLess>
            )
            : null}
        </div>
      </div>
    );
  }
}
