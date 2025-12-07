require("dotenv").config();
const express = require("express");
const path = require("path");
const cookieParser = require("cookie-parser"); // Import cookie parser
const app = express();

app.set("view engine", "ejs");
app.set("views", path.join(__dirname, "views"));
app.use(express.static(path.join(__dirname, "public")));

app.use(express.urlencoded({ extended: true }));
app.use(express.json());
app.use(cookieParser());

const indexRoutes = require("./routes/index");
const authRoutes = require("./routes/auth");
const dashboardRoutes = require("./routes/admin");
const feedbackRoutes = require("./routes/feedback");

app.use("/", indexRoutes);
app.use("/auth", authRoutes);
app.use("/admin", dashboardRoutes);
app.use("/feedback", feedbackRoutes);

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`Frontend running on http://localhost:${PORT}`);
});
