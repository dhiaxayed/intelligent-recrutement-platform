// Person 4: Safa Khedhawria will contribute here.
// Responsibility: Nodemailer acceptance email service with Google Meet interview link.

require("dotenv").config();

const express = require("express");
const cors = require("cors");
const nodemailer = require("nodemailer");

const app = express();

app.use(cors());
app.use(express.json());

const PORT = process.env.MAIL_SERVICE_PORT || 3000;

function escapeHtml(value) {
  return String(value)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

function isHttpUrl(value) {
  try {
    const url = new URL(value);
    return url.protocol === "http:" || url.protocol === "https:";
  } catch (error) {
    return false;
  }
}

function isEmail(value) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(value));
}

app.get("/", (req, res) => {
  res.json({
    success: true,
    message: "Recruitment mail service is running"
  });
});

app.post("/send-acceptance-email", async (req, res) => {
  try {
    const { candidateEmail, candidateName, jobTitle, meetLink } = req.body;

    if (!candidateEmail || !candidateName || !jobTitle || !meetLink) {
      return res.status(400).json({
        success: false,
        message: "Missing required fields"
      });
    }

    if (!isEmail(candidateEmail)) {
      return res.status(400).json({
        success: false,
        message: "Invalid candidate email"
      });
    }

    if (!isHttpUrl(meetLink)) {
      return res.status(400).json({
        success: false,
        message: "Invalid meeting link"
      });
    }

    const transporter = nodemailer.createTransport({
      host: process.env.MAIL_HOST,
      port: Number(process.env.MAIL_PORT) || 587,
      secure: process.env.MAIL_SECURE === "true",
      auth: {
        user: process.env.MAIL_USER,
        pass: process.env.MAIL_PASS
      }
    });

    const safeCandidateName = escapeHtml(candidateName);
    const safeJobTitle = escapeHtml(jobTitle);
    const safeMeetLink = escapeHtml(meetLink);

    await transporter.sendMail({
      from: process.env.MAIL_FROM,
      to: candidateEmail,
      subject: "Application Accepted - Interview Invitation",
      text: `Hello ${candidateName},

Congratulations! Your application for ${jobTitle} has been accepted.

Your interview meeting link:
${meetLink}

Best regards,
Recruitment Team`,
      html: `
        <p>Hello ${safeCandidateName},</p>
        <p>Congratulations! Your application for <strong>${safeJobTitle}</strong> has been accepted.</p>
        <p>Your interview meeting link:</p>
        <p><a href="${safeMeetLink}">${safeMeetLink}</a></p>
        <p>Best regards,<br>Recruitment Team</p>
      `
    });

    return res.json({
      success: true,
      message: "Acceptance email sent successfully"
    });
  } catch (error) {
    console.error("Mail service error:", error.message);

    return res.status(500).json({
      success: false,
      message: "Failed to send acceptance email"
    });
  }
});

app.listen(PORT, () => {
  console.log(`Mail service running on port ${PORT}`);
});
