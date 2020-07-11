import React from 'react';
import _find from 'lodash/find';
import { getStorageInfo } from '../../../../../utilities/storage';
import TextField from '../../../../../utilities/textfield';
import { fetchAPIData } from '../../../../../utilities/api/fetchApiData';

export default class CompanionDetails extends React.Component {
  constructor(props) {
    super(props);
    const localStorageValues = getStorageInfo();

    if (localStorageValues) {
      this.state = {
        ...localStorageValues,
      };
    }
  }

  componentDidMount() {
    const { selectedStoreItem, appointmentCategory, appointmentType } = this.state;
    const apiUrl = `/get/questions?location=${selectedStoreItem.locationExternalId}&program=${appointmentCategory.id}&activity=${appointmentType.id}`;
    const apiData = fetchAPIData(apiUrl);

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.error === undefined && result.data !== undefined) {
          this.setState((prevState) => ({
            ...prevState,
            questions: result.data,
          }));
        }
      });
    }
  }

  handleChange = (e) => {
    const { handleChange } = this.props;
    handleChange(e);
  }

  render() {
    const {
      appointmentCompanion,
      questions,
    } = this.state;

    const {
      clientData,
    } = this.props;

    const companionQuestions = [...Array(parseInt(appointmentCompanion.id, 10))].map((e, i) => {
      const companionNum = i + 1;
      const companionNamePrefix = `bootscompanion${companionNum}`;
      const firstName = `${companionNamePrefix}name`;
      const lastName = `${companionNamePrefix}lastname`;
      const dob = `${companionNamePrefix}dob`;
      const firstNameData = _find(questions, ['questionExternalId', firstName]);
      const lastNameData = _find(questions, ['questionExternalId', lastName]);
      const dobData = _find(questions, ['questionExternalId', dob]);
      let defaultfirstName; let defaultlastName; let
        defaultdob;

      if (clientData) {
        ({
          [firstName]: defaultfirstName,
          [lastName]: defaultlastName,
          [dob]: defaultdob,
        } = clientData);
      }

      if (firstNameData && lastNameData && dobData) {
        return (
          <div className={`${companionNamePrefix}-details`}>
            <div className="details-header-wrapper">
              <h3>{`${Drupal.t('Companion')} ${companionNum} ${Drupal.t('Details')}`}</h3>
            </div>
            <div className="details-wrapper">
              <div className="item">
                <TextField
                  type="text"
                  required={firstNameData.required}
                  name={firstName}
                  defaultValue={defaultfirstName}
                  className={firstName !== '' ? 'focus' : ''}
                  label={firstNameData.questionText}
                  handleChange={this.handleChange}
                />
              </div>
              <div className="item">
                <TextField
                  type="text"
                  required={lastNameData.required}
                  name={lastName}
                  defaultValue={defaultlastName}
                  className={lastName !== '' ? 'focus' : ''}
                  label={lastNameData.questionText}
                  handleChange={this.handleChange}
                />
              </div>
              <div className="item">
                <TextField
                  type="date"
                  required={dobData.required}
                  name={dob}
                  defaultValue={defaultdob}
                  className={dob !== '' ? 'focus' : ''}
                  label={dobData.questionText}
                  handleChange={this.handleChange}
                />
              </div>
            </div>
          </div>
        );
      }
      return null;
    });

    return (
      <div className="companion-details-wrapper">
        {companionQuestions}
      </div>
    );
  }
}
