const express = require("express");
const router = express.Router();
const dashboardController = require("../controllers/dashboardController");
const businessController = require("../controllers/businessController");
const settingsController = require("../controllers/settingsController");

const { checkAuth } = require("../middleware/authMiddleware");

router.get("/dashboard", checkAuth, dashboardController.getDashboard);
router.get("/business", checkAuth, businessController.getBusinessPage);

router.post("/business/add", checkAuth, businessController.createBusiness);
router.post("/business/update", checkAuth, businessController.updateBusiness);
router.post("/business/delete", checkAuth, businessController.deleteBusiness);

router.get("/settings", checkAuth, settingsController.getSettingsPage);

module.exports = router;
