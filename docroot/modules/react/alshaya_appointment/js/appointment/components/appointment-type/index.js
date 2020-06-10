import React from 'react';
import AppointmentCategories from "../appointment-categories";
import ReadMoreAndLess from 'react-read-more-less';

const AppointmentTypeList = [
  { value: 'WInter Flu Jab Service (KD 22- 45)', label: 'WInter Flu Jab Service (KD 22- 45)' },
  { value: 'Pneumonia Vaccination Service (KD 11- 17)', label: 'Pneumonia Vaccination Service (KD 11- 17)' },
  { value: 'Travel Vaccination and Advice Service (KD 27- 51)', label: 'Travel Vaccination and Advice Service (KD 27- 51)' },
  { value: 'Malaria Prevention Service (KD 9 - 18)', label: 'Malaria Prevention Service (KD 9 - 18)' } 
];

const AppointmentTypeDescription = "There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don't look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet. It uses a dictionary of over 200 Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.";

const AppointmentCompanion = [
  { value: '1', label: '1' },
  { value: '2', label: '2' },
  { value: '3', label: '3' },
  { value: '4', label: '4' } 
];

const AppointmentAckText = "There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don't look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet. It uses a dictionary of over 200 Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.";

export default class AppointmentType extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      appointmentCategory: '',
      appointmentType: '',
      appointmentCompanion: '',
      appointmentForYou: 'Yes',
      appointmentAck: '',
    };
  }

  handleChange = (event) => {
    this.setState({
      [event.target.name]: event.target.value
    });

  }

  handleClick = (event) => {
    this.setState({
      appointmentForYou: event.target.value
    });
  }

  render () {
    return (
      <div className="appointment-type-wrapper">
        <AppointmentCategories />
        <div className="appointment-type-list-wrapper">
          <label>{Drupal.t('Appointment Type')}:*</label>
          <div className="appointment-type-list-inner-wrapper">
            <select 
              className="appointment-type-select" 
              name="appointmentType"
              onChange={this.handleChange}
            >
              {AppointmentTypeList.map(v => (
                <option value={v.value}>{v.label}</option>
              ))}
            </select>
            <ReadMoreAndLess
              charLimit={250}
              readMoreText="Read More"
              readLessText="Show Less"
            >
              {AppointmentTypeDescription}
            </ReadMoreAndLess>
          </div>
        </div>
        <div className="appointment-companion-wrapper">
          <label>{Drupal.t('How many people do you want to book the appointment for?')}*</label>
          <select 
              className="appointment-companion-select" 
              name="appointmentCompanion"
              onChange={this.handleChange}
            >
              {AppointmentCompanion.map(v => (
                <option value={v.value}>{v.label}</option>
              ))}
            </select>
        </div>
        <div className="appointment-for-you-wrapper">
          <label>{Drupal.t('Is one of these appointments for you?')}*</label>
          <div className="appointment-for-you">
            <label>
              <input
                type="radio"
                value="Yes"
                name="appointmentForYou"
                checked={this.state.appointmentForYou === "Yes"}
                onChange={this.handleChange}
              />
              {Drupal.t('Yes')}
            </label>
            <label>
              <input
                type="radio"
                value="No"
                name="appointmentForYou"
                checked={this.state.appointmentForYou === "No"}
                onChange={this.handleChange}
              />
              {Drupal.t('No')}
            </label>
          </div>
        </div>
        <div className="appointment-ack-wrapper">
          <input
            type="checkbox"
            name="appointmentAck"
            onChange={this.handleChange}
          />
          <div className="appointment-ack-inner-wrapper">
            <label>{Drupal.t('Please tick to confirm the following')}*</label>
            <div className="">
              {AppointmentAckText}
            </div>
          </div>
        </div>
        <button
          className="appointment-type-button"
          type="button"
          onClick={this.handleClick}
        >
          {Drupal.t('Continue')}
        </button>
      </div>
    );
  }
}

