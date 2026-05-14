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

function buildEmailHtml(candidateName, jobTitle, meetLink) {
  const safe = {
    name: escapeHtml(candidateName),
    job:  escapeHtml(jobTitle),
    link: escapeHtml(meetLink),
  };

  return `
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Candidature acceptée — RecruitPro</title>
</head>
<body style="margin:0; padding:0; background-color:#dde8f5; font-family:'Segoe UI',Arial,sans-serif;">

  <table width="100%" cellpadding="0" cellspacing="0" style="background:#dde8f5; padding:40px 0;">
    <tr>
      <td align="center">
        <table width="560" cellpadding="0" cellspacing="0"
               style="max-width:560px; width:100%; border-radius:20px; overflow:hidden;
                      box-shadow:0 8px 32px rgba(30,60,120,0.13);">

          <!-- HEADER -->
          <tr>
            <td style="background:#1a2744; padding:36px 44px; text-align:center;">
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td align="center" style="padding-bottom:16px;">
                    <div style="width:60px; height:60px; background:rgba(255,255,255,0.12);
                                border-radius:14px; display:inline-block; line-height:60px;
                                font-size:28px;">
                      💼
                    </div>
                  </td>
                </tr>
                <tr>
                  <td align="center">
                    <h1 style="color:#ffffff; font-size:26px; font-weight:700;
                                margin:0 0 6px; letter-spacing:-0.3px;">
                      Félicitations !
                    </h1>
                    <p style="color:rgba(255,255,255,0.65); font-size:14px; margin:0;">
                      Votre candidature a été acceptée
                    </p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- BODY -->
          <tr>
            <td style="background:#ffffff; padding:36px 44px;">

              <p style="font-size:15px; color:#1a2744; margin:0 0 18px;">
                Bonjour <strong style="color:#2563eb;">${safe.name}</strong>,
              </p>

              <p style="font-size:14px; color:#4b5563; line-height:1.75; margin:0 0 24px;">
                Nous avons le plaisir de vous informer que votre candidature pour le poste
                <strong style="color:#1a2744;">${safe.job}</strong> a été examinée et
                <strong style="color:#2563eb;">acceptée</strong> par notre équipe.
              </p>

              <!-- INFO BOX -->
              <table width="100%" cellpadding="0" cellspacing="0"
                     style="background:#eff6ff; border:1px solid #bfdbfe;
                            border-radius:12px; margin-bottom:24px;">
                <tr>
                  <td style="padding:18px 22px;">
                    <p style="font-size:11px; font-weight:700; color:#1e40af;
                               text-transform:uppercase; letter-spacing:1.2px; margin:0 0 6px;">
                      Prochaine étape
                    </p>
                    <p style="font-size:14px; color:#1a2744; margin:0; line-height:1.6;">
                      Votre entretien aura lieu en ligne via <strong>Google Meet</strong>.
                      Rejoignez la réunion en cliquant sur le bouton ci-dessous.
                    </p>
                  </td>
                </tr>
              </table>

              <!-- MEET BUTTON -->
              <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
                <tr>
                  <td align="center">
                    <a href="${safe.link}"
                       style="display:inline-block; background:#1a2744; color:#ffffff;
                              font-size:15px; font-weight:700; text-decoration:none;
                              padding:14px 36px; border-radius:50px; letter-spacing:0.2px;">
                      📹 &nbsp; Rejoindre l'entretien
                    </a>
                  </td>
                </tr>
              </table>

              <!-- LINK TEXT -->
              <table width="100%" cellpadding="0" cellspacing="0"
                     style="background:#f8fafc; border-radius:10px; margin-bottom:24px;">
                <tr>
                  <td style="padding:12px 16px;">
                    <p style="font-size:11px; color:#9ca3af; margin:0 0 4px;">
                      Lien direct :
                    </p>
                    <a href="${safe.link}"
                       style="font-size:12px; color:#2563eb; word-break:break-all; text-decoration:none;">
                      ${safe.link}
                    </a>
                  </td>
                </tr>
              </table>

              <!-- TIPS -->
              <table width="100%" cellpadding="0" cellspacing="0"
                     style="border-left:3px solid #2563eb; margin-bottom:24px;">
                <tr>
                  <td style="padding:12px 16px;">
                    <p style="font-size:13px; font-weight:700; color:#1a2744; margin:0 0 8px;">
                      Conseils pour l'entretien :
                    </p>
                    <p style="font-size:13px; color:#6b7280; margin:0; line-height:1.9;">
                      ✅ &nbsp; Testez votre caméra et microphone avant la réunion<br>
                      ✅ &nbsp; Choisissez un endroit calme et bien éclairé<br>
                      ✅ &nbsp; Soyez ponctuel et préparez vos questions
                    </p>
                  </td>
                </tr>
              </table>

              <p style="font-size:13px; color:#6b7280; margin:0; line-height:1.7;">
                Nous sommes impatients de vous rencontrer.<br>
                Bonne chance pour votre entretien !
              </p>

            </td>
          </tr>

          <!-- DIVIDER -->
          <tr>
            <td style="background:#ffffff; padding:0 44px;">
              <hr style="border:none; border-top:1px solid #e5e7eb; margin:0;">
            </td>
          </tr>

          <!-- FOOTER -->
          <tr>
            <td style="background:#f8fafc; padding:22px 44px; text-align:center;
                       border-radius:0 0 20px 20px;">
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td align="center" style="padding-bottom:10px;">
                    <table cellpadding="0" cellspacing="0" style="display:inline-table;">
                      <tr>
                        <td style="vertical-align:middle; padding-right:8px;">
                          <div style="width:28px; height:28px; background:#1a2744;
                                      border-radius:8px; display:inline-block;
                                      line-height:28px; font-size:14px; text-align:center;">
                            💼
                          </div>
                        </td>
                        <td style="vertical-align:middle;">
                          <span style="font-size:15px; font-weight:700; color:#1a2744;">
                            RecruitPro
                          </span>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
                <tr>
                  <td align="center">
                    <p style="font-size:11px; color:#9ca3af; margin:0; line-height:1.6;">
                      Cet email a été envoyé automatiquement — merci de ne pas y répondre.<br>
                      © 2026 RecruitPro. Tous droits réservés.
                    </p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>

</body>
</html>
  `;
}

app.get("/", (req, res) => {
  res.json({ success: true, message: "Recruitment mail service is running" });
});

app.post("/send-acceptance-email", async (req, res) => {
  try {
    const { candidateEmail, candidateName, jobTitle, meetLink } = req.body;

    if (!candidateEmail || !candidateName || !jobTitle || !meetLink) {
      return res.status(400).json({ success: false, message: "Missing required fields" });
    }

    if (!isEmail(candidateEmail)) {
      return res.status(400).json({ success: false, message: "Invalid candidate email" });
    }

    if (!isHttpUrl(meetLink)) {
      return res.status(400).json({ success: false, message: "Invalid meeting link" });
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

    await transporter.sendMail({
      from: process.env.MAIL_FROM,
      to: candidateEmail,
      subject: `Félicitations ${candidateName} — Votre candidature pour ${jobTitle} a été acceptée`,
      text: `Bonjour ${candidateName},\n\nFélicitations ! Votre candidature pour ${jobTitle} a été acceptée.\n\nLien entretien : ${meetLink}\n\nCordialement,\nRecruitPro`,
      html: buildEmailHtml(candidateName, jobTitle, meetLink)
    });

    return res.json({ success: true, message: "Email envoyé avec succès" });

  } catch (error) {
    console.error("Mail service error:", error.message);
    return res.status(500).json({ success: false, message: "Failed to send email" });
  }
});

app.listen(PORT, () => {
  console.log(`Mail service running on port ${PORT}`);
});