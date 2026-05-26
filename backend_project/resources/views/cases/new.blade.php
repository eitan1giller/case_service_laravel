<!doctype html>
<html lang="he" dir="rtl">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>יצירת פניה</title>
    <style>
      body{font-family:system-ui,Segoe UI,Roboto,Helvetica,Arial;margin:24px;direction:rtl}
      label{display:block;margin-top:8px}
      input,textarea{width:100%;padding:8px;margin-top:4px}
      button{margin-top:12px;padding:8px 12px}
      .result{margin-top:12px;padding:8px;border:1px solid #ddd;background:#f9f9f9}
    </style>
  </head>
  <body>
    <h1>יצירת פניה לתושב</h1>

    <form id="caseForm">
      <label>שם המבקש
        <input name="applicant[name]" required>
      </label>

      <label>תעודת זהות
        <input name="applicant[national_id]">
      </label>

      <label>דוא"ל ליצירת קשר
        <input name="contact[email]" type="email">
      </label>

      <label>טלפון ליצירת קשר
        <input name="contact[phone]">
      </label>

      <label>נושא הפניה
        <input name="subject" required>
      </label>

      <label>תיאור
        <textarea name="description" rows="4"></textarea>
      </label>

      <button type="submit">שלח פניה</button>
    </form>

    <div id="result" class="result" style="display:none"></div>

    <p style="margin-top:16px"><a href="/admin/cases">מעבר לממשק ניהול</a></p>

    <script>
      const form = document.getElementById('caseForm');
      const result = document.getElementById('result');

      function formDataToJson(fd) {
        const obj = {};
        for (const [k,v] of fd.entries()){
          // handle nested keys like applicant[name]
          const m = k.match(/([^[\]]+)(?:\[([^\]]+)\])?/);
          if(!m) continue;
          const a = m[1];
          const b = m[2];
          if(b){
            obj[a] = obj[a] || {};
            obj[a][b] = v;
          } else {
            obj[a] = v;
          }
        }
        return obj;
      }

      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        result.style.display = 'none';
        const fd = new FormData(form);
        const body = JSON.stringify(formDataToJson(fd));
        const idempotency = crypto.randomUUID ? crypto.randomUUID() : Date.now().toString();

        try{
              const resp = await fetch('/api/cases', {
            method: 'POST',
            headers: {'Content-Type':'application/json','Idempotency-Key': idempotency},
            body
          });
          const json = await resp.json();
          result.style.display = 'block';
          if (resp.ok) {
            result.style.borderColor = '#15803d';
            result.style.background = '#ecfdf5';
            result.textContent = 'תודה! הפניה נשלחה בהצלחה. מספר מעקב: ' + (json.tracking_id ?? json.id ?? 'לא זמין');
            form.reset();
          } else {
            result.style.borderColor = '#b91c1c';
            result.style.background = '#fee2e2';
            result.textContent = 'שגיאה בשליחת הפניה: ' + (json.message || JSON.stringify(json));
          }
        } catch(err){
          result.style.display = 'block';
          result.style.borderColor = '#b91c1c';
          result.style.background = '#fee2e2';
          result.textContent = 'שגיאה: '+err.message;
        }
      });
    </script>
  </body>
</html>
