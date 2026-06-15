<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>خطاب عرض عمل</title>
<style>
    body {
        font-family: "Tahoma", Arial, sans-serif;
        max-width: 900px;
        margin: 40px auto;
        padding: 20px;
        line-height: 1.8;
        color: #333;
    }

    .header {
        text-align: center;
        margin-bottom: 30px;
    }

    .header img {
        height: 60px;
        margin-bottom: 12px;
        display: block;
        margin-left: auto;
        margin-right: auto;
    }

    .header h1 {
        margin: 0;
        font-size: 28px;
    }

    .header p {
        margin: 5px 0;
        color: #666;
    }

    .section {
        margin-bottom: 20px;
    }

    .field {
        margin-bottom: 15px;
    }

    .label {
        font-weight: bold;
    }

    .value {
        border-bottom: 1px solid #999;
        display: inline-block;
        min-width: 200px;
        padding: 0 5px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    table td {
        border: 1px solid #ddd;
        padding: 12px;
        vertical-align: top;
    }

    .title-cell {
        width: 30%;
        font-weight: bold;
        background: #f8f8f8;
    }

    .signature-section {
        margin-top: 40px;
    }

    .signature-box {
        width: 45%;
        display: inline-block;
        vertical-align: top;
    }

    .signature-line {
        border-bottom: 1px solid #999;
        display: inline-block;
        min-width: 180px;
        margin-right: 10px;
    }

    @media print {
        body {
            margin: 0;
            max-width: 100%;
        }
    }
</style>
</head>

<body>

<div class="header">
    @if($logo_src)
    <img src="{{ $logo_src }}" alt="Bot Journey">
    @endif
    <h1>خطاب عرض عمل</h1>
    <p>JOB OFFER LETTER · CONFIDENTIAL</p>
</div>

<div class="section">
    <div class="field">
        <span class="label">التاريخ:</span>
        <span class="value">{{ $date }}</span>
    </div>

    <div class="field">
        <span class="label">السيد/السيدة:</span>
        <span class="value" style="min-width:400px;">{{ $employee_name }}</span>
    </div>
</div>

<p>
تحية طيبة وبعد،
</p>

<p>
يسر شركة <strong>Bot Journey</strong> أن تتقدم إليكم بعرض للعمل لديها وفق الشروط والتفاصيل الموضحة أدناه، وتتطلع إلى انضمامكم إلى فريقها.
وفيما يلي أبرز بنود العرض:
</p>

<table>
    <tr>
        <td class="title-cell">المسمى الوظيفي</td>
        <td>{{ $position }}</td>
    </tr>

    <tr>
        <td class="title-cell">نوع العقد</td>
        <td>
            عقد محدد المدة لمدة سنة واحدة، قابل للتجديد لسنة أخرى باتفاق الطرفين خطياً.
        </td>
    </tr>

    <tr>
        <td class="title-cell">تاريخ المباشرة</td>
        <td>{{ $hire_date }}</td>
    </tr>

    <tr>
        <td class="title-cell">مكان العمل</td>
        <td>
            عن بُعد بشكل أساسي، مع حضور حضوري بمعدل مرة واحدة أسبوعياً تقريباً في مكان يُتفق عليه.
        </td>
    </tr>

    <tr>
        <td class="title-cell">ساعات العمل</td>
        <td>وفق ساعات العمل المعتمدة لدى الشركة.</td>
    </tr>

    <tr>
        <td class="title-cell">الراتب الشهري الإجمالي</td>
        <td>
            @if($salary_amount)
                {{ $salary_amount }} دينار أردني، يُدفع نقداً في نهاية كل شهر ميلادي دون اقتطاعات.
            @else
                يُحدد بالاتفاق.
            @endif
        </td>
    </tr>

    <tr>
        <td class="title-cell">فترة التجربة</td>
        <td>
            ثلاثة (3) أشهر، يحق خلالها لأي من الطرفين إنهاء العقد دون تعويض.
        </td>
    </tr>

    <tr>
        <td class="title-cell">فترة الإشعار للإنهاء</td>
        <td>
            ثلاثون (30) يوماً قبل الإنهاء، دون تعويض عن الإنهاء المبكر.
        </td>
    </tr>

    <tr>
        <td class="title-cell">السرية والملكية الفكرية</td>
        <td>
            يخضع العمل لاتفاقية عدم إفشاء (NDA)، وتعود ملكية جميع الأعمال والابتكارات للشركة.
        </td>
    </tr>

    <tr>
        <td class="title-cell">القانون الواجب التطبيق</td>
        <td>قوانين المملكة الأردنية الهاشمية.</td>
    </tr>
</table>

<p>
يُعد هذا العرض مشروطاً بتوقيعكم على عقد العمل واتفاقية عدم إفشاء المعلومات (NDA) المعتمدين لدى الشركة، وبتقديم أي مستندات مطلوبة.
</p>

<p>
يرجى تأكيد قبولكم لهذا العرض بالتوقيع في حقل الإقرار أدناه وإعادته إلى الشركة في موعد أقصاه 7 أيام.
ويُعتبر هذا العرض لاغياً إذا لم يُقبل خلال هذه المدة.
</p>

<p>
وتفضلوا بقبول فائق الاحترام والتقدير،
</p>

</body>
</html>
