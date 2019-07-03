/**
 * @file
 * Javascript to draw google chart and show data.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.showPageSpeedDataChart = {
    attach: function (context, settings) {
      let urlObject = {
        url: [],
        data: [0,0,0,0,0,0,0],
        metric_id: 0,
        screen: '',
        time: ''
      };

      $('document').ready(function () {
        initialiseData();
      });

      $('#gps-metric-select, #gps-screen-select, #gps-time-select').on('change', function () {
        initialiseData();
      });

      let initialiseData = function() {
        urlObject = {
          url: $(this).value,
          data: [0,0,0,0,0,0,0]
        };

        let metric_id = document.getElementById('gps-metric-select').value;
        let screen = document.getElementById('gps-screen-select').value;
        let time = document.getElementById('gps-time-select').value;

        let getUrls = '/google-page-speed/url/' + metric_id + '/' + screen + '/' + time;

        jQuery.get(getUrls, function(data, status){
          if (status === 'success' && data) {
            urlObject.url = jQuery.parseJSON(data);
            urlObject.metric_id = metric_id;
            urlObject.screen = screen;
            urlObject.time = time;
            google.charts.load('current', {'packages':['corechart','line']});
            google.charts.setOnLoadCallback(drawChart);
          }
        });
      };

      function drawChart() {
        var dataTable = new google.visualization.DataTable();
        dataTable.addColumn('datetime', 'Date');

        for (var url_id in urlObject.url){
          if (urlObject.url.hasOwnProperty(url_id)) {
            dataTable.addColumn('number', urlObject.url[url_id]);
          }
        }

        let getScores = '/google-page-speed/' + urlObject.metric_id + '/' + urlObject.screen + '/' + urlObject.time;


        jQuery.get(getScores, function(data, status){
          if (status === 'success' && data) {
            let scoreData = jQuery.parseJSON(data);
            scoreData.forEach(function (element) {
              element[0] = new Date(element[0]*1000);
              console.log(element);
              dataTable.addRows([
                element
              ]);
            });

            var options = {
              hAxis: {
                title: 'Date and time'
              },
              vAxis: {
                title: 'Time(s)'
              },
              'width':'100%',
              'height':'auto',
              pointsVisible: true
            };

            var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
            chart.draw(dataTable, options);
          }
        });
      }
    }
  };

})(jQuery, Drupal);
