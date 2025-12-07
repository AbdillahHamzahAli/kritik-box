const express = require("express");
const router = express.Router();
const landingController = require("../controllers/homeController");

router.get("/", landingController.getHomePage);

module.exports = router;
