<!doctype html>
<html lang="he" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $payload['subject'] ?? 'עדכון פניה' }}</title>
    <style> body { font-family: Arial, Helvetica, sans-serif; direction: rtl; }</style>
</head>
<body>
    <h2>{{ $payload['subject'] ?? 'עדכון פניה' }}</h2>
    <p>{{ $payload['body'] ?? '' }}</p>

    @if(!empty($payload['case']))
        <h3>פרטי פניה</h3>
        <ul>
            <li>מספר מעקב: {{ $payload['case']['id'] ?? '' }}</li>
            <li>שם: {{ $payload['case']['applicant_name'] ?? '' }}</li>
            <li>טלפון: {{ $payload['case']['applicant_phone'] ?? '' }}</li>
            <li>דוא"ל: {{ $payload['case']['applicant_email'] ?? '' }}</li>
        </ul>
    @endif

    <p>תודה,<br>עיריית ירושלים</p>
</body>
</html>
