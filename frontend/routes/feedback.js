const express = require("express");
const router = express.Router();
const feedbackController = require("../controllers/feedbackController");

router.get("/:code", feedbackController.getFeedbackForm);
router.post("/:code", feedbackController.submitFeedback);

module.exports = router;
