Chart.register({
  id: "noDataPlugin",
  beforeDraw(chart) {
    const dataset = chart.data.datasets[0];
    const hasData =
      dataset && dataset.data && dataset.data.some((value) => value > 0);

    if (!hasData) {
      const ctx = chart.ctx;
      const { width, height } = chart;

      chart.clear();

      ctx.save();
      ctx.textAlign = "center";
      ctx.textBaseline = "middle";
      ctx.font = "20px Times New Roman";
      ctx.fillStyle = "gray";
      ctx.fillText("No available data", width / 2, height / 2);
      ctx.restore();
    }
  },
});
