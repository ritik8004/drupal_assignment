import React from 'react';
import parse from 'html-react-parser';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import { isMobile } from '../../../../../js/utilities/display';

class BecomeMember extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      isBlockVisible: true,
    };
  }

  hideBlock = () => {
    this.setState({
      isBlockVisible: false,
    });
  };

  render() {
    const { isBlockVisible } = this.state;
    const mobileDeviceClass = isMobile() ? 'mobile-device' : '';
    return (
      <>
        <ConditionalView condition={isBlockVisible}>
          <div className={`become-a-member-hello-member ${mobileDeviceClass}`}>
            <ConditionalView condition={isMobile()}>
              <button type="button" className="close" onClick={() => this.hideBlock()} />
            </ConditionalView>
            <div className="become-a-member-header">
              {parse(Drupal.t('Members get <span>free delivery and 10% off on your first purchase.</span>', {}, { context: 'hello_member' }))}
            </div>
            <div className="become-a-member-join-text">
              {Drupal.t('Not a member yet? Join now, it’s free!', {}, { context: 'hello_member' })}
            </div>
            <div className="become-a-member-actions">
              <div className="become-a-member-sign_in">
                <a className="view-all-benefits" href={`${Drupal.url('user/login')}`}>
                  {Drupal.t('SIGN IN', {}, { context: 'hello_member' })}
                </a>
              </div>
              <div className="become-a-member-sign_up">
                <a className="view-all-benefits" href={`${Drupal.url('user/register')}`}>
                  {Drupal.t('Become a Member', {}, { context: 'hello_member' })}
                </a>
              </div>
            </div>
          </div>
        </ConditionalView>
      </>
    );
  }
}

export default BecomeMember;
