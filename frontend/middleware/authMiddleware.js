exports.checkAuth = (req, res, next) => {
  const token = req.cookies.session_token;

  if (!token) {
    return res.redirect(
      "/auth/login?error=Silakan login untuk mengakses dashboard",
    );
  }
  next();
};
