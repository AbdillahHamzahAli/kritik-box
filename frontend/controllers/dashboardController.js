const axios = require("axios");

const API_URL = process.env.BACKEND_API_URL;

exports.getDashboard = async (req, res) => {
  try {
    const token = req.cookies.session_token;
    if (!token) {
      return res.redirect(
        "/auth/login?error=Sesi habis, silakan login kembali.",
      );
    }

    const response = await axios.get(`${API_URL}/api/dashboard`, {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });

    const apiData = response.data.data;

    const chartLabels = apiData.chart.map((item) => {
      const date = new Date(item.date);
      return date.toLocaleDateString("id-ID", {
        day: "numeric",
        month: "short",
      });
    });

    const chartValues = apiData.chart.map((item) => item.count);

    res.render("admin/dashboard", {
      title: "Dashboard Admin",
      page: "dashboard",

      user: { name: "Admin" },
      stats: apiData.summary,

      chartLabels: chartLabels,
      chartValues: chartValues,
      reviews: apiData.recent_feedbacks,
    });
  } catch (error) {
    console.error("Error fetching dashboard:", error.message);

    if (error.response) {
      if (error.response.status === 401) {
        res.clearCookie("session_token");
        return res.redirect(
          "/auth/login?error=Sesi berakhir, silakan login lagi.",
        );
      }
    }

    res.render("admin/dashboard", {
      title: "Dashboard (Offline)",
      page: "dashboard",
      user: { name: "Admin" },
      stats: {
        total_businesses: 0,
        total_feedbacks: 0,
        average_rating: 0,
      },
      chartLabels: [],
      chartValues: [],
      reviews: [],
    });
  }
};
