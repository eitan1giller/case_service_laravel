## א. פתרון מערכת Web לניהול פניות

### ארכיטקטורה כללית
- פרונטנד: טפסי יצירת פניה עבור הלקוח, דף מעקב סטטוס.
- Backend: שירות HTTP ב-PHP/Laravel.
- DB: MySQL עם טבלאות `citizen_cases`, `outbox_events`, `idempotency`.
- אינטגרציות חיצוניות:
  - CRM: יצירת פנייה וקבלת סטטוס.
  - מערכת התראות: SMTP / SMS provider.
  - אימות נתונים חיצוני: API חיצוני.

### רכיבים עיקריים
- API גילוי פניה: `POST /api/cases`, `GET /api/cases/{id}`
- `Outbox`: כתיבה אסינכרונית של אירועים ליציאה
- `Publisher` רקע: קורא `outbox_events`, שולח התראות, מסמן `published`
- אימות נתונים חיצוני: שירות `ExternalValidator`

---

## 1. זרימת טיפול בפניה (Flow)

1. הלקוח ממלא טופס פנייה.
2. הבקשה נשלחת ל-API.
3. API מבצע:
   - אימות קלט בסיסי.
   - קריאה חיצונית ל-API אימות נתונים.
   - יצירת רשומת פניה ב-DB.
   - הצבת אירוע `OutboxEvent` ליצירת פניה ב-CRM + התראות.
   - שמירה של `idempotency_key` אם נשלח.
4. הלקוח מקבל תשובה מידית עם `tracking_id` וסטטוס `queued`.
5. תהליך רקע קורא את `outbox_events`:
   - שולח ל-CRM
   - אם יצירת פניה ב-CRM הצליחה, מעדכן סטטוס מקומי/חיצוני
   - שולח מייל/SMS
6. משך הטיפול משוקף ללקוח דרך `GET /api/cases/{id}` או Web UI.

### תרחיש כשל מורכב
- ה-API החיצוני לאימות הנתונים לא זמין.
- הפתרון:
  - מאפשר יצירה מקומית אם לאימות אין השפעה קריטית.
  - שומר את תוצאת הבעיה ב-`metadata.external_validation`.
  - שולח התראה פנימית / מערכת ניהול במקרה של כשל.
  - אם האימות הוא חובה, מחזיר `422` עם שגיאה.

### טרנזקציות
- טרנזקציה פנימית ב-DB עבור:
  - יצירת פניה
  - יצירת `outbox_event`
  - יצירת `idempotency`
- כך נמנעת איבוד פניות: כל הכתיבה המקומית היא אטומית.
- אינטגרציה עם CRM ו-System notifications אינה חלק מהטרנזקציה הראשית, אלא נעשית אסינכרונית דרך Outbox.

### סוג ה-Consistency
- קונסיסטנטיות חזקה לנתונים מקומיים (write-after-write ב-MySQL).
- בין המערכת ל-CRM / התראות: 
  - `eventual consistency` עם retries ו-outbox.
  - לכן המערכת מקודמת ל-`strong consistency` פנימי, ו-`eventual consistency` באינטגרציות.

---

## 2. עבודה מול מערכת חיצונית איטית

### איך לא להמתין למשמש
- לא לקרוא ישירות בסנכרון בתוך בקשת המשתמש.
- זרימה:
  - קבל את הפניה ושמור מקומית.
  - החזר ללקוח `202 Accepted` + `tracking_id` מיד.
  - יצור `outbox_event` שמסמן צורך ב-CRM / אימות / התראות.
- המעבד הרקע מבצע את הקריאה האיטית אחרי שהמשתמש יצא.

### עדכון סטטוס לאחר קבלת תשובה
- תהליך הרקע מקבל תשובה מהשרות החיצוני.
- מעדכן את הסטטוס ב-DB המקומי.
- שולח הודעה ללקוח / שומר לוג השתנות סטטוס.
- אם יש push/socket, ניתן לדחוף עדכון בזמן אמת.

### חוסר זמינות / כשלים
- Retry אסינכרוני עם דיליי.
- circuit breaker / backoff.
- אם התלות קריטית: 
  - יצר `pending` או `retry-later`
  - שלח התרעה למפעיל.
- אם צריך: fallback ל-flow חלקי ולשלוח המשך תהליך למשתמש.

---

## 3. התמודדות עם עומסים

### זיהוי עומס
- מדדים:
  - משימות בתור `outbox_events` או `queue`
  - latency של request
  - שיעור שגיאות 5xx
  - CPU/RAM ו-DB connections
- כלים: Prometheus, Grafana, Laravel Telescope / Laravel Horizon.

### צווארי בקבוק צפויים
- DB כתיבת/קריאת עומס
- שירות חיצוני איטי
- SMTP/SMS provider
- תור/worker מוגבל
- I/O של לוגים/דיסק

### מנגנונים להתמודדות
- Rate limiting / backpressure ב-api הגולש
- caching קריאות סטטוס בשכבת read
- sharding או replica לקריאות DB
- queue workers רבים יותר / auto-scaling
- שימוש ב-`failover` ו-`circuit breaker`
- רמת העדיפות לבקשות קריטיות

---

## 4. טיפול בכשלים

### מצבי כשל אפשריים
- DB לא זמין
- שרות חיצוני איטי / לא זמין
- שליחת מייל/SMS נכשלה
- כשל בצד לקוח (idempotency/duplicate)
- בעיית רשת

### תכנית טיפול
- DB: circuit breaker, fallback קריאה מתוך cache / פול בדאטה קאש.
- חיצוני: retry עם backoff; אם כשל זמני - שמור כ`pending`.
- התראות: אם SMTP נכשל, fallback ל-`log` או `retry later`.
- כשל קשה: raise alert, נשמר בלוח DLQ.
- duplicate: `Idempotency-Key` כדי למנוע יצירת פניות כפולות.

---

## 5. ניטור ותחקור תקלות

### יכולות בקוד/שירותים
- Logging מובנה עם metadata: `case_id`, `event_type`, `correlation_id`.
- Audit trail לכל שינוי סטטוס.
- אגירת שגיאות ב-DB וב־logs.
- תיעוד `outbox_event` ו-`publish_attempts`.

### tracing distributed
- הוסף `correlation_id` לכל בקשה ו-event.
- השתמש ב-OpenTelemetry / Zipkin / Jaeger.
- עקוב אחרי flow מ-API → validation → outbox → CRM → notification.
- שים tags ב־trace לפי `case_id`, `event_id`, `external_service`.

### metrics מומלצים
- request latency / p95 / p99
- queue depth / retry rate
- רמת כשלות של חיבורים ל-CRM
- מספר פניות שנכשלו ב-`external validation`
- כמות דוא"לים/SMS שנשלחו / נכשלו
- DB connections ו-CPU

---

## 6. אבטחת מידע

### הגנה על מידע רגיש
- הצפנת שדות רגישים ב-DB לפי הצורך.
- GDPR: שמירה רק של מה שנדרש, מחיקה / pseudonymization.
- תעבורה TLS לכל HTTP ו-API.
- שמירת סיסמאות ומפתחות ב-`env`.
- הרשאות גישה מבוסס תפקיד / session.

### מניעת גישה לפניות של משתמש אחר
- ברמת קוד:
  - `case.owner_id = user.id`
  - ב-API: `GET /api/cases/{id}` בודק שהמשתמש הוא הבעלים של הפניה.
  - אם admin, בדיקה שונה.
- service-level:
  - token/jwt מבוסס תעודת זהות.
  - אחרי אימות, filter queries לפי `applicant_user_id`.

---

## 7. תרחיש Debug

### הנחות לפתור קודם
- האם הפניות באמת נשמרות ב-DB?
- האם הרובוטים של העיבוד מקבלים את האירועים?
- האם ה-CRM או תהליך הרקע חסום?

### גישה לחקירה
1. בדוק לוגים של workers ו-outbox.
2. בדוק סטטוס הפניות ב-DB (`status`, `updated_at`).
3. בדוק `publish_attempts` ו-`last_error` ב-`outbox_events`.
4. בדוק אם CRM מחזיר `accepted` או מתקבל שגיאה.

### נתונים לבדוק
- האם הסטטוס נשמר רק ב-local או גם ב-CRM?
- האם יש `500` בעדכון הסטטוס?
- האם יש retries לא מוצלחים של `outbox`.
- האם הגורם לכשל הוא קריאה חיצונית, שליחת התראה או קוד הפנימי.

### בידוד הבעיה
- בידוד בקוד: האם זה באירוע `status change` או ב-worker?
- בדיקה אם הבעיה מתרחשת רק על פניות ספציפיות.
- בדיקה אם יש תרחישי timeout / deadlock ב-DB.

### מניעה בעתיד
- הוספת alert על סטטוס תקוע לאורך זמן.
- מדד TTL לפניות ב-`IN_PROGRESS` / `PENDING`.
- retries מבוקרים ו-DLQ ל`outbox_events`.
- dashboards ל-flow כולו.

---

## תרשימים מומלצים
- Sequence diagram:
  - `User → API → DB → Outbox`
  - `Worker → CRM / Validation / Notification`
- Architecture:
  - UI, API, Queue/Outbox, DB, CRM, Notification provider, External validator
- Error handling:
  - retry / fallback / DLQ / monitoring

בהצלחה, ואם תרצה אפשר לגבש תשובה קונקרטית באותו פורמט עבור מצגת או דו"ח.
