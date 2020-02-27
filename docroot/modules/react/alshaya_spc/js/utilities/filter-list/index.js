import React from 'react';
import SectionTitle from "../section-title";

export default class FilterList extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      'items': [],
      'selected': null
    };
  }

  /**
   * Filter the list on search.
   */
  filterList = () => {
    var updatedList = this.getInitialItems();
    updatedList = updatedList.filter(function (item) {
      return item['label'].toLowerCase().search(
        event.target.value.toLowerCase()) !== -1;
    });

    this.setState({
      items: updatedList
    });
  }

  /**
   * Prepare initial list.
   */
  getInitialItems = () => {
    return this.props.options;
  };

  /**
   * Handle click on <li>.
   */
  handleLiClick = (e) => {
    this.setState({
      selected: e.target.value
    });

    this.props.toggleFilterList();

    // Call the process.
    this.props.processingCallback(e.target.value);
  };

  backButtonClick = () => {
    this.props.toggleFilterList();
  };

  componentDidMount() {
    this.setState({
      items: this.getInitialItems(),
      selected: this.props.selected
    });
  }

  render () {
    let items = this.state.items;
    if (items === 0) {
      return (null);
    }

    return (
      <div className='filter-list'>
        <div className='spc-filter-panel-header'>
          <span className='spc-filter-panel-back' onClick={() => this.backButtonClick()}/>
          <SectionTitle>{this.props.panelTitle}</SectionTitle>
        </div>
        <div className='spc-filter-panel-search-form-item'>
          <input className='spc-filter-panel-search-field' type='text' placeholder={this.props.placeHolderText} onChange={this.filterList}/>
        </div>
        <ul>{
          items.map((item) => {
            return(
              <li
                key={item.value}
                value={item.value}
                className={this.state.selected == item.value ? 'active' : 'in-active'}
                onClick={this.handleLiClick}
              >
              {item.label}
            </li>)})
          }
        </ul>
      </div>
    );
  }

}
