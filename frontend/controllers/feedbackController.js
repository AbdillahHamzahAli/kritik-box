const axios = require("axios");
const API_URL = process.env.BACKEND_API_URL;

exports.getFeedbackForm = async (req, res) => {
  const { code } = req.params;

  const response = await axios.get(`${API_URL}/api/public/business/${code}`);

  const business = response.data.data;

  if (!business) {
    return res
      .status(404)
      .send("Lokasi bisnis tidak ditemukan atau link salah.");
  }

  console.log(business);

  res.render("public/form", {
    title: `Feedback - ${business.name}`,
    business: business,
  });
};

exports.submitFeedback = async (req, res) => {
  const { code } = req.params;
  const { rating, comment } = req.body;

  console.log("Received feedback:", req.body);
  try {
    const response = await axios.post(`${API_URL}/api/feedback/${code}`, {
      rating: Number(rating),
      text: comment,
    });

    if (response.status === 201) {
      res.status(201).json({
        status: "success",
        message: "Feedback diterima",
      });
    } else {
      console.error("Unexpected status code:", response.status);
    }
  } catch (error) {
    console.error("Error submit feedback:", error);
    res.status(500).json({
      status: "error",
      message: "Terjadi kesalahan server",
    });
  }
};
