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
    const apiUrl = `/get/questions?location=${selectedStoreItem.locationExternalId}&program=${appointmentCategory.id}&activity=${appointmentType.value}`;
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

  handleAddFields = () => {
    const { handleAddCompanion } = this.props;
    handleAddCompanion();
  };

  render() {
    const { questions, appointmentCompanionItems } = this.state;
    const {
      companionData,
      appointmentCompanion,
    } = this.props;
    const companionLimitReached = (appointmentCompanion.value >= appointmentCompanionItems.length);

    const companionQuestions = [...Array(parseInt(appointmentCompanion.value, 10))].map((e, i) => {
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

      if (companionData) {
        ({
          [firstName]: defaultfirstName,
          [lastName]: defaultlastName,
          [dob]: defaultdob,
        } = companionData);
      }

      if (firstNameData && lastNameData && dobData) {
        return (
          <div className={`companion-details-item ${companionNamePrefix}-details`}>
            <div className="details-header-wrapper">
              <div className="companion-detail-heading">{`${Drupal.t('Companion')} ${companionNum} ${Drupal.t('Details')}`}</div>
            </div>
            <div className="user-details-wrapper">
              <div className="user-detail-name-wrapper">
                <div className="item user-firstname">
                  <TextField
                    type="text"
                    required={firstNameData.required}
                    name={firstName}
                    defaultValue={defaultfirstName}
                    className={firstName !== '' ? 'focus' : ''}
                    label={Drupal.t('First name')}
                    handleChange={this.handleChange}
                  />
                </div>
                <div className="item user-lastname">
                  <TextField
                    type="text"
                    required={lastNameData.required}
                    name={lastName}
                    defaultValue={defaultlastName}
                    className={lastName !== '' ? 'focus' : ''}
                    label={Drupal.t('Last name')}
                    handleChange={this.handleChange}
                  />
                </div>
              </div>
              <div className="item user-dob">
                <TextField
                  type="date"
                  required={dobData.required}
                  name={dob}
                  defaultValue={defaultdob}
                  className={dob !== '' ? 'focus' : ''}
                  label={`${Drupal.t('Date of Birth')}*`}
                  handleChange={this.handleChange}
                  section="companionData"
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
        <div className="companion-details-questions">
          {companionQuestions}
        </div>
        <div className="add-companion">
          {!companionLimitReached && (
          <button
            className="btn btn-link"
            type="button"
            onClick={() => this.handleAddFields()}
          >
            {Drupal.t('Add Companion')}
          </button>
          )}
        </div>
      </div>
    );
  }
}
