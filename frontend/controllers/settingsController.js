const axios = require("axios");
const API_URL = process.env.BACKEND_API_URL;

exports.getSettingsPage = async (req, res) => {
  try {
    const token = req.cookies.session_token;
    if (!token) {
      return res.redirect(
        "/auth/login?error=Sesi habis, silakan login kembali.",
      );
    }

    const response = await axios.get(`${API_URL}/api/me`, {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });

    const apiResponse = {
      status: "success",
      message: "Profile retrieved",
      data: {
        id: 3,
        name: "admin",
        username: "admin",
        email: "admin@example.com",
        is_premium: false, // Boolean
        membership_expires_at: "2026-01-01 08:13:03",
        joined_at: "2025-12-01 11:22:52",
      },
    };

    const userData = response.data.data;
    const formatDate = (dateString) => {
      if (!dateString) return "-";
      const options = { day: "numeric", month: "short", year: "numeric" };
      return new Date(dateString).toLocaleDateString("id-ID", options);
    };

    const usageData = {
      used: userData.count_businesses,
      limit: userData.is_premium ? "Unlimited" : 2,
    };

    res.render("admin/settings", {
      title: "Pengaturan Akun",
      page: "settings",
      user: {
        ...userData,
        formatted_joined: formatDate(userData.joined_at),
        formatted_expires: formatDate(userData.membership_expires_at),
        usage: usageData,
      },
    });
  } catch (error) {
    console.error(error);
    res.status(500).send("Internal Server Error");
  }
};
