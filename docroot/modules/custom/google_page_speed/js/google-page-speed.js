/**
 * @file
 * Javascript to draw google chart and show data.
 */

(function ($, Drupal) {
  'use strict';

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

  Drupal.initialiseData = function() {
    urlObject = {
      url: $(this).value,
      data: [0,0,0,0,0,0,0]
    };

    var metric_id = document.getElementById('gps-metric-select').value;
    var device = document.getElementById('gps-device-select').value;
    var time = document.getElementById('gps-time-select').value;
    var getUrls = Drupal.url('google-page-speed/url/' + metric_id + '/' + device + '/' + time);

    $.get(getUrls, function(data, status){
      if (status === 'success' && data) {
        urlObject.url = $.parseJSON(data);
        urlObject.metric_id = metric_id;
        urlObject.device = device;
        urlObject.time = time;
        google.charts.load('current', {'packages':['corechart','line']});
        google.charts.setOnLoadCallback(Drupal.drawChart);
      }
    });
  };

  Drupal.drawChart = function () {
    var dataTable = new google.visualization.DataTable();
    dataTable.addColumn('datetime', 'Date');

    for (var url_id in urlObject.url){
      if (urlObject.url.hasOwnProperty(url_id)) {
        dataTable.addColumn('number', urlObject.url[url_id]);
      }
    }

    var getScores = Drupal.url('google-page-speed/' + urlObject.metric_id + '/' + urlObject.device + '/' + urlObject.time);

    $.get(getScores, function(data, status){
      if (status === 'success' && data) {
        var scoreData = $.parseJSON(data);
        scoreData.forEach(function (element) {
          element[0] = new Date(element[0]*1000);
          dataTable.addRow(
            element
          );
        });

        var options = {
          hAxis: {
            title: 'Date and time'
          },
          vAxis: {
            title: 'Scores between 0 & 1'
          },
          'width':'100%',
          'height':'auto',
          pointsVisible: true
        };

        var chart = new google.visualization.LineChart(document.getElementById('google-page-speed-chart-wrapper'));
        chart.draw(dataTable, options);
      }
    });
  };

})(jQuery, Drupal);
