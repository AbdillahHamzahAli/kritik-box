const axios = require("axios");
const API_URL = process.env.BACKEND_API_URL;

exports.getBusinessPage = async (req, res) => {
  try {
    const token = req.cookies.session_token;
    if (!token) {
      return res.redirect(
        "/auth/login?error=Sesi habis, silakan login kembali.",
      );
    }

    const response = await axios.get(`${API_URL}/api/business`, {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });

    const businessData = response.data.data;

    console.log;

    res.render("admin/business", {
      title: "Business Management",
      page: "business",
      user: req.user || { name: "Admin" },
      businesses: businessData,
    });
  } catch (error) {
    console.error("Error fetching business data:", error);

    if (error.response) {
      if (error.response.status === 401) {
        res.clearCookie("session_token");
        return res.redirect(
          "/auth/login?error=Sesi berakhir, silakan login lagi.",
        );
      }
    }
    res.status(500).send("Terjadi kesalahan pada server.");
  }
};

exports.createBusiness = async (req, res) => {
  try {
    const token = req.cookies.session_token;
    if (!token) {
      return res.redirect(
        "/auth/login?error=Sesi habis, silakan login kembali.",
      );
    }

    const { name, location, address } = req.body;
    await axios.post(
      `${API_URL}/api/business`,
      {
        name,
        location,
        address,
      },
      { headers: { Authorization: `Bearer ${token}` } },
    );
    res.redirect("/admin/business");
  } catch (error) {
    console.error("Gagal menambah bisnis:", error.message);
    res.redirect("/admin/business?error=Gagal menambah bisnis");
  }
};

exports.updateBusiness = async (req, res) => {
  try {
    const token = req.cookies.session_token;
    if (!token) {
      return res.redirect(
        "/auth/login?error=Sesi habis, silakan login kembali.",
      );
    }
    const { id, name, location, address } = req.body;

    await axios.put(
      `${API_URL}api/business/${id}`,
      {
        name,
        location,
        address,
        qrcode: "",
      },
      { headers: { Authorization: `Bearer ${token}` } },
    );

    res.redirect("/admin/business");
  } catch (error) {
    console.error("Gagal update bisnis:", error.message);
    res.redirect("/admin/business?error=Gagal update");
  }
};

exports.deleteBusiness = async (req, res) => {
  try {
    const token = req.cookies.session_token;
    if (!token) {
      return res.redirect(
        "/auth/login?error=Sesi habis, silakan login kembali.",
      );
    }
    const { id } = req.body;

    await axios.delete(`${API_URL}/api/business/${id}`, {
      headers: { Authorization: `Bearer ${token}` },
    });

    res.redirect("/admin/business");
  } catch (error) {
    console.error("Gagal menghapus bisnis:", error.message);
    res.redirect("/admin/business?error=Gagal menghapus");
  }
};
