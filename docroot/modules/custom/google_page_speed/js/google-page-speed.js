/**
 * @file
 * Javascript to draw google chart and show data.
 */

(function ($, Drupal) {

  var urlObject = {
    url: [],
    data: [0,0,0,0,0,0,0],
    metric_id: 0,
    device: '',
    time: ''
  };

  Drupal.behaviors.showPageSpeedDataChart = {
    attach: function (context, settings) {

      $('document').ready(function () {
        Drupal.initialiseData();
      });

      $('#gps-metric-select, #gps-device-select, #gps-time-select').on('change', function () {
        Drupal.initialiseData();
      });
    }
  };

  Drupal.initialiseData = function () {
    urlObject = {
      url: $(this).value,
      data: [0,0,0,0,0,0,0]
    };

    var metric_id = document.getElementById('gps-metric-select').value;
    var device = document.getElementById('gps-device-select').value;
    var time = document.getElementById('gps-time-select').value;
    var getUrls = Drupal.url('google-page-speed/url/' + metric_id + '/' + device + '/' + time);

    $.get(getUrls, function (data, status) {
      if (status === 'success' && data) {
        urlObject.url = data;
        urlObject.metric_id = metric_id;
        urlObject.device = device;
        urlObject.time = time;
        google.charts.load('current', {'packages':['corechart','line', 'controls']});
        google.charts.setOnLoadCallback(Drupal.drawChart);
      }
    });
  };

  Drupal.drawChart = function () {
    var scoreTable = new google.visualization.DataTable();
    var urlsTable = new google.visualization.DataTable();
    urlsTable.addColumn('number', 'colIndex');
    urlsTable.addColumn('string', 'colLabel');

    scoreTable.addColumn('datetime', 'Date');

    for (var url_id in urlObject.url){
      if (urlObject.url.hasOwnProperty(url_id)) {
        scoreTable.addColumn('number', urlObject.url[url_id]);
      }
    }

    var getScores = Drupal.url('google-page-speed/' + urlObject.metric_id + '/' + urlObject.device + '/' + urlObject.time);

    $.get(getScores, function (scoreData, status) {
      if (status === 'success' && scoreData) {
        scoreData.forEach(function (element) {
          element[0] = new Date(element[0] * 1000);
          scoreTable.addRow(
            element
          );
        });

        var initState = {selectedValues: []};
        /**
         * Put the urls into this url table.
         * Skipping column 0 because column 0 contains Date object.
         * We only need urls to be filtered.
         * See line 72.
         */
        for (var i = 1; i < scoreTable.getNumberOfColumns(); i++) {
          urlsTable.addRow([i, scoreTable.getColumnLabel(i)]);
          initState.selectedValues.push(scoreTable.getColumnLabel(i));
        }

        var options = {
          hAxis: {
            title: Drupal.t('Date and time')
          },
          vAxis: {
            title: Drupal.t('Scores between 0 & 1')
          },
          'width':'100%',
          'height':'auto',
          pointsVisible: true
        };

        // Initialise Line Chart wrapper.
        var chart = new google.visualization.ChartWrapper({
          chartType: 'LineChart',
          containerId: 'google-page-speed-chart-wrapper',
          dataTable: scoreTable,
          options
        });
        chart.draw();

        // Initialise Url filters wrapper.
        var urlFilter = new google.visualization.ControlWrapper({
          controlType: 'CategoryFilter',
          containerId: 'google-page-speed-curve-filter',
          dataTable: urlsTable,
          options: {
            filterColumnLabel: 'colLabel',
            ui: {
              label: Drupal.t('URL Filter'),
              allowTyping: true,
              allowMultiple: true,
              selectedValuesLayout: 'below',
              allowNone: false,
              caption: Drupal.t('Choose an URL'),
              labelStacking: 'vertical'
            }
          },
          state: initState
        });

        google.visualization.events.addListener(urlFilter, 'statechange', function () {
          var state = urlFilter.getState();
          var row;
          var urlIndices = [0];
          for (var i = 0; i < state.selectedValues.length; i++) {
            row = urlsTable.getFilteredRows([{column: 1, value: state.selectedValues[i]}])[0];
            urlIndices.push(urlsTable.getValue(row, 0));
          }
          // sort the indices into their original order
          urlIndices.sort(function (a, b) {
            return (a - b);
          });
          chart.setView({columns: urlIndices});
          chart.draw();
        });

        urlFilter.draw();
      }
    });
  };

})(jQuery, Drupal);
