<!doctype html>
<html lang="he" dir="rtl">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>כניסת מנהל</title>
    <style>
      body{font-family:system-ui,Segoe UI,Roboto,Helvetica,Arial;margin:0;background:#f5f7fa}
      .top{background:#004a7c;color:white;padding:20px}
      .container{max-width:600px;margin:32px auto;background:white;padding:24px;border-radius:8px;box-shadow:0 4px 14px rgba(0,0,0,.08)}
      label{display:block;margin-top:12px}
      input{width:95%;padding:10px;margin-top:6px}
      button{background:#0078d4;color:white;border:none;padding:10px 14px;border-radius:4px;margin-top:14px}
      .error{color:#a00;margin-top:8px}
      a{color:#0078d4}
    </style>
  </head>
  <body>
    <div class="top"><h2 style="margin:0">עיריית ירושלים — מערכת פניות</h2></div>
    <div class="container">
      <h3>כניסת מנהל</h3>

      @if(session('error'))
        <div class="error">{{ session('error') }}</div>
      @endif

      <form method="post" action="/admin/login">
        @csrf
        <label>שם משתמש
          <input name="username" required>
        </label>
        <label>סיסמה
          <input name="password" type="password" required>
        </label>
        <button type="submit">כניסה</button>
      </form>

      <p style="margin-top:12px">חזור ל<a href="/">עמוד הבית</a></p>
    </div>
  </body>
</html>
