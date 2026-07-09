(function() {
    "use strict";

    function cssVar(name) {
        return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    }

    function hexToRgba(hex, alpha) {
        if (!hex || hex.indexOf("#") !== 0) {
            return hex;
        }

        var cleanHex = hex.replace("#", "");
        var bigint = parseInt(cleanHex, 16);
        var r = (bigint >> 16) & 255;
        var g = (bigint >> 8) & 255;
        var b = bigint & 255;

        return "rgba(" + r + ", " + g + ", " + b + ", " + alpha + ")";
    }

    function parseBase64Json(encoded) {
        if (!encoded) {
            return {};
        }

        try {
            var binary = window.atob(encoded);
            var bytes = new Uint8Array(binary.length);

            for (var i = 0; i < binary.length; i++) {
                bytes[i] = binary.charCodeAt(i);
            }

            var jsonText = new TextDecoder("utf-8").decode(bytes);

            return JSON.parse(jsonText);
        } catch (error) {
            return {};
        }
    }

    function getDashboardData() {
        var dataElement = document.getElementById("bc-dashboard-data");

        if (!dataElement) {
            return {};
        }

        var encodedData = dataElement.getAttribute("data-chart");

        return parseBase64Json(encodedData);
    }

    function initProgressBars() {
        var progressBars = document.querySelectorAll(".bc-progress-bar[data-progress]");

        progressBars.forEach(function(bar) {
            var value = parseFloat(bar.getAttribute("data-progress"));

            if (isNaN(value)) {
                value = 0;
            }

            if (value < 0) {
                value = 0;
            }

            if (value > 100) {
                value = 100;
            }

            bar.style.width = value + "%";
        });
    }

    function createLineChart(chartData) {
        var canvas = document.getElementById("bcRevenueChart");

        if (!canvas || typeof Chart === "undefined") {
            return;
        }

        var ctx = canvas.getContext("2d");

        var blue = cssVar("--commission-blue");
        var orange = cssVar("--commission-orange");

        var gradient = ctx.createLinearGradient(0, 0, 0, 320);
        gradient.addColorStop(0, hexToRgba(blue, 0.22));
        gradient.addColorStop(1, hexToRgba(blue, 0.02));

        new Chart(ctx, {
            type: "line",
            data: {
                labels: chartData.revenueLabels || [],
                datasets: [{
                        label: "Doanh thu (Tr)",
                        data: chartData.revenueValues || [],
                        borderColor: blue,
                        backgroundColor: gradient,
                        borderWidth: 3,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: cssVar("--commission-bg-white"),
                        pointBorderColor: blue,
                        pointBorderWidth: 2,
                        fill: true,
                        tension: 0.42
                    },
                    {
                        label: "Hoa hồng chi (Tr)",
                        data: chartData.commissionValues || [],
                        borderColor: orange,
                        backgroundColor: "transparent",
                        borderWidth: 2,
                        borderDash: [5, 5],
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        pointBackgroundColor: cssVar("--commission-bg-white"),
                        pointBorderColor: orange,
                        pointBorderWidth: 2,
                        fill: false,
                        tension: 0.35
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: "index"
                },
                plugins: {
                    legend: {
                        position: "top",
                        align: "end",
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            color: cssVar("--commission-muted"),
                            font: {
                                size: 12,
                                weight: "700"
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: cssVar("--commission-title"),
                        titleColor: cssVar("--commission-white"),
                        bodyColor: cssVar("--commission-white"),
                        padding: 12,
                        displayColors: true
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: cssVar("--commission-muted"),
                            font: {
                                size: 12,
                                weight: "700"
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: cssVar("--commission-border-soft"),
                            borderDash: [4, 4]
                        },
                        ticks: {
                            color: cssVar("--commission-muted"),
                            font: {
                                size: 12,
                                weight: "700"
                            }
                        }
                    }
                }
            }
        });
    }

    function createDoughnutChart(canvasId, values, colors) {
        var canvas = document.getElementById(canvasId);

        if (!canvas || typeof Chart === "undefined") {
            return;
        }

        new Chart(canvas.getContext("2d"), {
            type: "doughnut",
            data: {
                datasets: [{
                    data: values || [],
                    backgroundColor: colors,
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: "62%",
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: cssVar("--commission-title"),
                        titleColor: cssVar("--commission-white"),
                        bodyColor: cssVar("--commission-white"),
                        padding: 12
                    }
                }
            }
        });
    }

    function initCharts() {
        var chartData = getDashboardData();

        createLineChart(chartData);

        createDoughnutChart(
            "bcSourceChart",
            chartData.sourceValues || [], [
                cssVar("--commission-blue"),
                cssVar("--commission-cyan"),
                cssVar("--commission-muted")
            ]
        );

        createDoughnutChart(
            "bcOrderStatusChart",
            chartData.orderStatusValues || [], [
                cssVar("--commission-green"),
                cssVar("--commission-orange"),
                cssVar("--commission-red")
            ]
        );
    }

    document.addEventListener("DOMContentLoaded", function() {
        initProgressBars();
        initCharts();
    });
})();