<!doctype html>
<html lang="he" dir="rtl">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>עיריית ירושלים — מערכת פניות</title>

    <style>
      body{font-family:system-ui,Segoe UI,Roboto,Helvetica,Arial;margin:0;background:#eef4f8;direction:rtl;color:#0f172a}
      .top{background:#00375f;color:white;padding:28px 0;box-shadow:0 15px 35px rgba(15,23,42,.12)}
      .wrap{max-width:1180px;margin:0 auto;padding:24px 20px}
      .page-title{margin:0;font-size:2rem;line-height:1.2;letter-spacing:.01em}
      .hero{display:grid;grid-template-columns:1.5fr 1.8fr 380px;gap:22px;align-items:start}
      @media (max-width: 1080px){.hero{grid-template-columns:1fr;}}
      .card{background:white;padding:26px;border-radius:18px;box-shadow:0 20px 40px rgba(15,23,42,.08);border:1px solid rgba(148,163,184,.16)}
      .card h2,.card h3{margin-top:0;color:#0f172a}
      .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;background:#0078d4;color:white;padding:12px 20px;border-radius:14px;text-decoration:none;font-weight:700;transition:transform .18s ease,box-shadow .18s ease}
      .btn:hover{transform:translateY(-1px);box-shadow:0 16px 30px rgba(0,120,212,.18)}
      .muted{color:#475569;line-height:1.8}
      .links{display:flex;flex-wrap:wrap;gap:12px;margin-top:18px}
      .section-title{margin:0 0 18px;font-size:1.5rem;color:#0f172a}
      .form-field{display:block;margin-top:16px}
      .form-field label{display:block;font-weight:700;color:#0f172a;margin-bottom:10px}
      .input,.textarea{width:95%;border:1px solid #cbd5e1;border-radius:14px;padding:14px 16px;background:#f8fafc;font-size:1rem;color:#0f172a;transition:border-color .2s ease,box-shadow .2s ease}
      .input:focus,.textarea:focus{outline:none;border-color:#2563eb;box-shadow:0 0 0 4px rgba(59,130,246,.14)}
      .textarea{min-height:140px;resize:vertical}
      .result-box{display:none;margin-top:18px;padding:16px;border-radius:14px;border:1px solid #d1d5db;background:#f8fafc;white-space:pre-wrap;color:#0f172a}
      .section-note{color:#475569;margin-top:0}
      .wide-card{grid-column:span 2}
      @media (max-width: 1080px){.wide-card{grid-column:auto}}
    </style>
  </head>
  <body>
    <div class="top"><div class="wrap"><h1 class="page-title">עיריית ירושלים — מערכת פניות לתושבים</h1></div></div>

    <div class="wrap">
      <div class="hero">
        <div>
          <div class="card">
            <h2>דווחו על תקלה או בקשה</h2>
            <p class="muted">שלחו פניה מהירה לעירייה — תחזוקה, תברואה, תאורה ועוד. קבלו מספר מעקב והתקדמות בזמן אמת.</p>

            <div class="links">
              {{-- <a class="btn" href="/cases/new">יצירת פניה</a> --}}
              <a class="btn" href="/admin/login" style="background:#0b5e3a">כניסת מנהל</a>
              {{-- <a class="btn" href="/admin/cases" style="background:#6b7280">ממשק ניהול</a> --}}
            </div>
          </div>
        </div>

        <div class="wide-card">
          <div class="card">
            <h3>שלח פניה ישירות מעמוד הבית</h3>
            <form id="homeForm">
              <div class="form-field">
                <label>שם המבקש</label>
                <input class="input" name="applicant[name]" required>
              </div>
              <div class="form-field">
                <label>דוא"ל ליצירת קשר</label>
                <input class="input" name="contact[email]" type="email">
              </div>
              <div class="form-field">
                <label>טלפון ליצירת קשר</label>
                <input class="input" name="contact[phone]">
              </div>
              <div class="form-field">
                <label>נושא הפניה</label>
                <input class="input" name="subject" required>
              </div>
              <div class="form-field">
                <label>תיאור</label>
                <textarea class="textarea" name="description" rows="4"></textarea>
              </div>
              <button type="submit" class="btn" style="margin-top:18px">שלח פניה</button>
            </form>
            <div id="homeResult" class="result-box"></div>
          </div>
        </div>

        <div style="width:360px">
          <div class="card">
            <h3 style="margin-top:0">איך זה עובד?</h3>
            <ol class="muted">
              <li>מילוי טופס קצר עם פרטי הפניה.</li>
              <li>קבלת מספר מעקב ובדיקת סטטוס באתר.</li>
              <li>הפניה מטופלת ע"י מחלקות העירייה ומתועדת למעקב.</li>
            </ol>
          </div>
        </div>
      </div>

      <div style="margin-top:18px" class="card">
        <h3 style="margin-top:0">מידע נוסף</h3>
        <p class="muted">מערכת ניסיונית — מטרתה להדגים תהליך הניהול וההתממשקות בין ממשק התושב לממשק הניהול.</p>
      </div>
    </div>
    <script>
      const homeForm = document.getElementById('homeForm');
      const homeResult = document.getElementById('homeResult');

      function formDataToJson(fd) {
        const obj = {};
        for (const [k, v] of fd.entries()) {
          const m = k.match(/([^[\]]+)(?:\[([^\]]+)\])?/);
          if (!m) continue;
          const a = m[1];
          const b = m[2];
          if (b) {
            obj[a] = obj[a] || {};
            obj[a][b] = v;
          } else {
            obj[a] = v;
          }
        }
        return obj;
      }

      homeForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        homeResult.style.display = 'none';
        const fd = new FormData(homeForm);
        const body = JSON.stringify(formDataToJson(fd));
        const idempotency = crypto.randomUUID ? crypto.randomUUID() : Date.now().toString();

        try {
          const resp = await fetch('/api/cases', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'Idempotency-Key': idempotency},
            body,
          });
          const json = await resp.json();
          homeResult.style.display = 'block';
          if (resp.ok) {
            homeResult.style.borderColor = '#15803d';
            homeResult.style.background = '#ecfdf5';
            homeResult.textContent = 'תודה! הפניה נשלחה בהצלחה. מספר מעקב: ' + (json.tracking_id ?? json.id ?? 'לא זמין');
            homeForm.reset();
          } else {
            homeResult.style.borderColor = '#b91c1c';
            homeResult.style.background = '#fee2e2';
            homeResult.textContent = 'שגיאה בשליחת הפניה: ' + (json.message || JSON.stringify(json));
          }
        } catch (err) {
          homeResult.style.display = 'block';
          homeResult.style.borderColor = '#b91c1c';
          homeResult.style.background = '#fee2e2';
          homeResult.textContent = 'שגיאה: ' + err.message;
        }
      });
    </script>
  </body>
</html>