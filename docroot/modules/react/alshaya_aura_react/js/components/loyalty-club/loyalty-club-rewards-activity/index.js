import React from 'react';

class LoyaltyClubRewardsActivity extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      activity: null,
    };
  }

  componentDidMount() {
    // @todo: API call here to get rewards activity.
    // Assuming some code for, H - Hold, C - Credited, R - Redeemed, E - Expired.
    const activityObj = [
      {
        order: 'HMEGHDE0006815',
        date: '23 Apr 2020',
        amount: 'KWD 123.123',
        type: 'Online',
        points: '240',
        status: 'H',
      },
      {
        order: 'MCEGHDE0006815',
        date: '21 Apr 2020',
        amount: 'KWD 70.123',
        type: 'Online',
        points: '80',
        status: 'C',
      },
      {
        order: 'FLEGHDE00068150',
        date: '20 Apr 2020',
        amount: 'KWD 8.123',
        type: 'Online',
        points: '-2000',
        status: 'R',
      },
      {
        order: 'HMEGHDE0006765',
        date: '9 Apr 2020',
        amount: 'KWD 40.123',
        type: 'Online',
        points: '-1600',
        status: 'E',
      },
    ];

    this.setState({
      activity: activityObj,
    });
  }

  generateStatement = () => {
    const { activity } = this.state;
    const statement = [];

    if (activity !== null && activity !== 'undefined') {
      activity.forEach((transaction) => {
        let statusString = null;
        if (transaction.status === 'H') {
          statusString = Drupal.t('On Hold');
        } else if (transaction.status === 'R') {
          statusString = Drupal.t('Redeemed');
        } else if (transaction.status === 'E') {
          statusString = Drupal.t('Expired');
        } else {
          statusString = Drupal.t('Credited');
        }
        statement.push(
          <div className="statement-row">
            <span className="order-id">{transaction.order}</span>
            <span className="date">{transaction.date}</span>
            <span className="amount">{transaction.amount}</span>
            <span className="type">{transaction.type}</span>
            <span className={`aura-points style-${transaction.status}`}>{transaction.points}</span>
            <span className={`status style-${transaction.status}`}>{statusString}</span>
          </div>,
        );
      });
    }

    return statement;
  };

  render() {
    return (
      <div className="loyalty-club-rewards-wrapper loyalty-tab-content fadeInUp" style={{ animationDelay: '0.6s' }}>
        <div className="header-row">
          <span className="order-id">{Drupal.t('Order No.')}</span>
          <span className="date">{Drupal.t('Date')}</span>
          <span className="amount">{Drupal.t('Order Total')}</span>
          <span className="type">{Drupal.t('Online / Offline')}</span>
          <span className="aura-points">{Drupal.t('AURA points')}</span>
          <span className="status">{Drupal.t('Status')}</span>
        </div>
        <div className="reward-activity">
          {this.generateStatement()}
        </div>
      </div>
    );
  }
}

export default LoyaltyClubRewardsActivity;
