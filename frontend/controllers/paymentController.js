const axios = require("axios");
const API_URL = process.env.BACKEND_API_URL;

exports.createUpgradeTransaction = async (req, res) => {
  try {
    const token = req.cookies.session_token;

    if (!token) {
      return res.status(401).json({ status: "error", message: "Unauthorized" });
    }

    const response = await axios.post(
      `${API_URL}/api/payment/create`,
      {},
      {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      },
    );

    const midtransData = response.data.data;

    res.json({
      status: "success",
      snap_token: midtransData.token,
      order_id: midtransData.order_id,
    });
  } catch (error) {
    console.error("Payment Error:", error.message);
    res
      .status(500)
      .json({ status: "error", message: "Gagal membuat transaksi." });
  }
};
