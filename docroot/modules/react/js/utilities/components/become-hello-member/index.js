import React from 'react';
import parse from 'html-react-parser';
import { isMobile } from '../../display';
import { hasValue } from '../../conditionsUtility';

class BecomeHelloMember extends React.Component {
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
    const { destination } = this.props;
    const redirectURL = (hasValue(destination)) ? `?destination=${destination}` : '';
    return (
      <>
        {
          isBlockVisible && (
            <div id="hello-member-become-hello-member">
              <div className={`become-hello-member ${mobileDeviceClass}`}>
                {
                  isMobile() && (
                    <button type="button" className="become-hello-member__close" onClick={() => this.hideBlock()} />
                  )
                }
                <div className="become-hello-member__header">
                  {parse(Drupal.t('<span>Members get</span> <span>10% off on your first purchase.</span>', {}, { context: 'hello_member' }))}
                </div>
                <div className="become-hello-member__join-text">
                  {Drupal.t('Not a member yet? Join now, it’s free!', {}, { context: 'hello_member' })}
                </div>
                <div className="become-hello-member__actions">
                  <a className="become-hello-member__actions-link become-hello-member__actions-link--secondary" href={`${Drupal.url(`user/login${redirectURL}`)}`}>
                    {Drupal.t('SIGN IN', {}, { context: 'hello_member' })}
                  </a>
                  <a className="become-hello-member__actions-link become-hello-member__actions-link--primary" href={`${Drupal.url('user/register')}`}>
                    {Drupal.t('Become a Member', {}, { context: 'hello_member' })}
                  </a>
                </div>
              </div>
            </div>
          )
        }
      </>
    );
  }
}

export default BecomeHelloMember;
