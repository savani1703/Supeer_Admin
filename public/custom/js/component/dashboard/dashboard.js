let chartPayInOptions = {
    series: [{
        name: "Session Duration",
        data: [45, 52, 38, 24, 33, 26, 21, 20, 6, 8, 15, 10]
    },
        {
            name: "Page Views",
            data: [35, 41, 62, 42, 13, 18, 29, 37, 36, 51, 32, 35]
        },
        {
            name: 'Total Visits',
            data: [87, 57, 74, 99, 75, 38, 62, 47, 82, 56, 45, 47]
        }
    ],
    chart: {
        id: 'client_summary',
        group: 'social',
        height: 350,
        type: 'line',
        zoom: {
            enabled: false
        },
        fontFamily: '"Lato","Lato-Regular","Helvetica Neue",Helvetica,Arial,sans-serif'
    },
    dataLabels: {
        enabled: false
    },
    stroke: {
        curve: 'straight',
        width: 1,
    },
    responsive: [{
        breakpoint: undefined,
        options: {},
    }],
    title: {
        text: 'Client\'s PayIn Summary',
        align: 'left'
    },
    legend: {
        tooltipHoverFormatter: function(val, opts) {
            return val + ' - ' + opts.w.globals.series[opts.seriesIndex][opts.dataPointIndex] + ''
        }
    },
    markers: {
        size: 0,
        hover: {
            sizeOffset: 6
        }
    },
    xaxis: {
        categories: ['01 Jan', '02 Jan', '03 Jan', '04 Jan', '05 Jan', '06 Jan', '07 Jan', '08 Jan', '09 Jan',
            '10 Jan', '11 Jan', '12 Jan'
        ],
    },
    tooltip: {
        y: [
            {
                title: {
                    formatter: function (val) {
                        return val + " (mins)"
                    }
                }
            },
            {
                title: {
                    formatter: function (val) {
                        return val + " per session"
                    }
                }
            },
            {
                title: {
                    formatter: function (val) {
                        return val;
                    }
                }
            }
        ]
    },
    grid: {
        borderColor: '#f1f1f1',
    }
};

let chartPayOutOptions = {
    series: [{
        name: "Session Duration",
        data: [45, 52, 38, 24, 33, 26, 21, 20, 6, 8, 15, 10]
    },
        {
            name: "Page Views",
            data: [35, 41, 62, 42, 13, 18, 29, 37, 36, 51, 32, 35]
        },
        {
            name: 'Total Visits',
            data: [87, 57, 74, 99, 75, 38, 62, 47, 82, 56, 45, 47]
        }
    ],
    chart: {
        id: 'client_summary',
        group: 'social',
        height: 350,
        type: 'line',
        zoom: {
            enabled: false
        },
        fontFamily: '"Lato","Lato-Regular","Helvetica Neue",Helvetica,Arial,sans-serif'
    },
    dataLabels: {
        enabled: false
    },
    stroke: {
        curve: 'straight',
        width: 1,
    },
    responsive: [{
        breakpoint: undefined,
        options: {},
    }],
    title: {
        text: 'Client\'s Payout Summary',
        align: 'left'
    },
    legend: {
        tooltipHoverFormatter: function(val, opts) {
            return val + ' - ' + opts.w.globals.series[opts.seriesIndex][opts.dataPointIndex] + ''
        }
    },
    markers: {
        size: 0,
        hover: {
            sizeOffset: 6
        }
    },
    xaxis: {
        categories: ['01 Jan', '02 Jan', '03 Jan', '04 Jan', '05 Jan', '06 Jan', '07 Jan', '08 Jan', '09 Jan',
            '10 Jan', '11 Jan', '12 Jan'
        ],
    },
    tooltip: {
        y: [
            {
                title: {
                    formatter: function (val) {
                        return val + " (mins)"
                    }
                }
            },
            {
                title: {
                    formatter: function (val) {
                        return val + " per session"
                    }
                }
            },
            {
                title: {
                    formatter: function (val) {
                        return val;
                    }
                }
            }
        ]
    },
    grid: {
        borderColor: '#f1f1f1',
    }
};

var payInChart = new ApexCharts(document.querySelector("#client_payin_summary"), chartPayInOptions);
payInChart.render();

var payOutChart = new ApexCharts(document.querySelector("#client_payout_summary"), chartPayOutOptions);
payOutChart.render();
