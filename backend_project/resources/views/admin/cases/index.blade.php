<!doctype html>
<html lang="he" dir="rtl">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>ניהול פניות</title>
    <style>
      body{font-family:system-ui,Segoe UI,Roboto,Helvetica,Arial;margin:0;background:#f5f7fa;direction:rtl}
      .top{background:#004a7c;color:white;padding:16px}
      .wrap{max-width:1100px;margin:24px auto;padding:16px}
      .card{background:white;padding:16px;border-radius:8px;box-shadow:0 6px 18px rgba(0,0,0,.06)}
      table{width:100%;border-collapse:collapse;margin-top:12px}
      th,td{border-bottom:1px solid #eee;padding:12px;text-align:right}
      th{background:#fafbfd}
      a.btn{background:#0078d4;color:white;padding:6px 10px;border-radius:4px;text-decoration:none}
      .tools{display:flex;gap:8px;align-items:center}
    </style>
  </head>
  <body>
    <div class="top"><div class="wrap"><h2 style="margin:0">עיריית ירושלים — ממשק ניהול פניות</h2></div></div>
    <div class="wrap">
      <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center">
          <h3 style="margin:0">רשימת פניות אחרונות</h3>
          <div class="tools">
            <a class="btn" href="/cases/new">צור פניה חדשה</a>
            <form method="post" action="/admin/logout" style="display:inline">@csrf<button class="btn" style="background:#a00;margin-left:8px">התנתק</button></form>
          </div>
        </div>

        <table>
          <thead>
            <tr>
              <th style="width:140px">מספר מעקב</th>
              <th>נושא</th>
              <th style="width:120px">סטטוס</th>
              <th style="width:180px">נוצר בתאריך</th>
              <th style="width:90px">פעולות</th>
            </tr>
          </thead>
          <tbody>
            @foreach($cases as $c)
            <tr>
              <td>{{ $c->id }}</td>
              <td>{{ $c->subject }}</td>
              <td>{{ $c->status }}</td>
              <td>{{ $c->created_at }}</td>
              <td><a href="/admin/cases/{{ $c->id }}">הצג</a></td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </body>
</html>
