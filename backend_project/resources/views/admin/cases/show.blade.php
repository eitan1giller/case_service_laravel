<!doctype html>
<html lang="he" dir="rtl">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>פרטי פניה</title>
    <style>
      body{font-family:system-ui,Segoe UI,Roboto,Helvetica,Arial;margin:0;background:#f5f7fa;direction:rtl}
      .top{background:#004a7c;color:white;padding:16px}
      .wrap{max-width:900px;margin:24px auto;padding:16px}
      .card{background:white;padding:20px;border-radius:8px;box-shadow:0 8px 20px rgba(0,0,0,.06)}
      label{display:block;margin-top:8px}
      pre{background:#f6f6f6;padding:12px;border:1px solid #eee}
    </style>
  </head>
  <body>
    <div class="top"><div class="wrap"><h2 style="margin:0">עיריית ירושלים — פרטי פניה</h2></div></div>
    <div class="wrap">
      <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center">
          <h3 style="margin:0">פניה: {{ $case->id }}</h3>
          <div>
            <a href="/admin/cases" style="margin-left:8px">חזרה לרשימה</a>
          </div>
        </div>

        <label>שם מבקש: <strong>{{ $case->applicant_name }}</strong></label>
        <label>נושא: <strong>{{ $case->subject }}</strong></label>
        <label>סטטוס: <strong>{{ $case->status }}</strong></label>
        <label>דוא"ל: <strong>{{ $case->contact_email }}</strong></label>
        <label>טלפון: <strong>{{ $case->contact_phone }}</strong></label>

        <h4 style="margin-top:16px">תיאור</h4>
        <pre>{{ $case->description }}</pre>

        <h4 style="margin-top:12px">מטא</h4>
        <pre>{{ json_encode($case->metadata ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
      </div>
    </div>
  </body>
</html>
