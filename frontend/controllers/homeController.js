// controllers/landingController.js

exports.getHomePage = (req, res) => {
  res.render("index", {
    title: "Selamat Datang - Sistem Manajemen Usaha",
  });
};
