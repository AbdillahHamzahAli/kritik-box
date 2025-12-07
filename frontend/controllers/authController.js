const axios = require("axios");
const API_URL = process.env.BACKEND_API_URL;

exports.getLogin = (req, res) => {
  res.render("auth/login", {
    title: "Login - Admin",
    error: null,
  });
};

exports.getRegister = (req, res) => {
  res.render("auth/register", {
    title: "Daftar Akun",
    error: null,
  });
};

exports.postRegister = async (req, res) => {
  try {
    const { nama, email, username, password } = req.body;
    await axios.post(`${API_URL}/api/register`, {
      name: nama,
      email: email,
      username: username,
      password: password,
    });

    res.redirect("/auth/login?registered=true");
  } catch (error) {
    const errorMsg =
      error.response?.data?.message || "Gagal mendaftar. Coba lagi.";
    res.render("auth/register", {
      title: "Daftar Akun",
      error: errorMsg,
    });
  }
};

exports.postLogin = async (req, res) => {
  try {
    const { email, password } = req.body;

    const response = await axios.post(`${API_URL}/api/login`, {
      email,
      password,
    });

    const { token } = response.data.data;

    res.cookie("session_token", token, {
      httpOnly: true,
      maxAge: 24 * 60 * 60 * 1000, // 1 hari
    });

    res.redirect("/admin/dashboard");
  } catch (error) {
    const errorMsg =
      error.response?.data?.message || "Username atau password salah.";

    res.render("auth/login", {
      title: "Login - Admin",
      error: errorMsg,
    });
  }
};

exports.logout = (req, res) => {
  res.clearCookie("session_token");
  res.redirect("/auth/login");
};
