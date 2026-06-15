<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>اتفاقية عدم إفشاء المعلومات</title>
<style>
    body {
        font-family: "Tahoma", Arial, sans-serif;
        max-width: 1000px;
        margin: 40px auto;
        padding: 20px;
        color: #333;
        line-height: 1.9;
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
        font-size: 26px;
    }

    .header p {
        margin: 5px 0;
        color: #777;
    }

    .field {
        border-bottom: 1px solid #999;
        display: inline-block;
        min-width: 200px;
        padding: 0 5px;
    }

    .article {
        margin-top: 25px;
    }

    .article-title {
        font-weight: bold;
        font-size: 17px;
        margin-bottom: 10px;
        color: #222;
    }

    .signature-table {
        width: 100%;
        margin-top: 60px;
        border-collapse: collapse;
    }

    .signature-table td {
        width: 50%;
        vertical-align: top;
        padding: 0 20px;
    }

    .signature-table td:first-child {
        padding-right: 0;
    }

    .signature-table td:last-child {
        padding-left: 0;
    }

    .signature-line {
        display: inline-block;
        min-width: 180px;
        border-bottom: 1px solid #999;
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
    <h1>اتفاقية عدم إفشاء المعلومات والسرية (NDA)</h1>
    <p>NON-DISCLOSURE AGREEMENT · CONFIDENTIAL</p>
</div>

<p>
    تم إبرام هذا العقد بتاريخ <span class="field">{{ $date }}</span> بين كل من:
</p>

<p>
    <strong>الطرف الأول (الشركة):</strong>
    شركة Bot Journey، ويمثلها السيد بكر صالح، ويشار إليها فيما بعد بـ <strong>"صاحب العمل"</strong>.
</p>

<p>
    <strong>الطرف الثاني (الموظف):</strong>
    السيد/السيدة: <span class="field" style="min-width:300px;">{{ $employee_name }}</span>
    ويشار إليه/إليها فيما بعد بـ <strong>"الموظف"</strong>.
</p>

<p>
    نظرًا لالتحاق الموظف بالعمل لدى الشركة أو تعاونه معها، وما قد يترتب على ذلك من اطلاعه على معلومات وبيانات ووثائق خاصة بالشركة وعملائها ومشاريعها، فقد اتفق الطرفان على إبرام هذه الاتفاقية بهدف حماية المعلومات السرية والمحافظة عليها وفقًا للشروط والأحكام التالية:
</p>

<!-- المادة الأولى -->
<div class="article">
    <div class="article-title">المادة الأولى: تعريف المعلومات السرية</div>
    <p>
        يقصد بالمعلومات السرية كافة المعلومات والبيانات التي يحصل عليها الموظف أو يطلع عليها بشكل مباشر أو غير مباشر أثناء فترة عمله أو بسببها، سواء كانت مكتوبة أو إلكترونية أو شفوية أو بأي وسيلة أخرى.
    </p>
</div>

<!-- المادة الثانية -->
<div class="article">
    <div class="article-title">المادة الثانية: التزامات الموظف</div>
    <p>
        يتعهد الموظف بالمحافظة على سرية المعلومات وعدم إفشائها أو نشرها أو تداولها بأي شكل من الأشكال وعدم استخدام المعلومات السرية لأي غرض شخصي أو تجاري خارج نطاق العمل المصرح به من قبل الشركة كما يتعهد بعدم تمكين أي شخص أو جهة غير مخولة من الوصول إلى المعلومات السرية.
    </p>
</div>

<!-- المادة الثالثة -->
<div class="article">
    <div class="article-title">المادة الثالثة: ملكية المعلومات</div>
    <p>
        تبقى جميع المعلومات والوثائق والملفات والبرامج والتصاميم والبيانات المتعلقة بالشركة أو عملائها ملكًا حصريًا للشركة. كما أن جميع الأعمال والبرمجيات والتصاميم والوثائق التي يتم تطويرها أو إعدادها من قبل الموظف أثناء عمله لصالح الشركة تعتبر ملكًا حصريًا للشركة ما لم يتم الاتفاق خطيًا على خلاف ذلك.
    </p>
</div>

<!-- المادة الرابعة -->
<div class="article">
    <div class="article-title">المادة الرابعة: إعادة وتسليم الممتلكات</div>
    <p>
        يلتزم الموظف عند انتهاء علاقة العمل أو عند طلب الشركة بإعادة جميع الأجهزة والمستندات والوثائق والملفات والمعلومات المتعلقة بالشركة. كما يتعهد بحذف أي نسخ إلكترونية أو احتياطية يحتفظ بها على أجهزته أو حساباته الشخصية وعدم الاحتفاظ بأي بيانات أو معلومات أو نسخ تخص الشركة أو عملاءها بعد انتهاء العلاقة.
    </p>
</div>

<!-- المادة الخامسة -->
<div class="article">
    <div class="article-title">المادة الخامسة: مدة الالتزام بالسرية</div>
    <p>
        تبقى التزامات الموظف المنصوص عليها في هذه الاتفاقية سارية طوال مدة عمله لدى الشركة، وتستمر لمدة سنتين (2) من تاريخ انتهاء علاقة العمل لأي سبب كان.
    </p>
</div>

<!-- المادة السادسة -->
<div class="article">
    <div class="article-title">المادة السادسة: الإخلال بالاتفاقية</div>
    <p>
        في حال مخالفة الموظف لأي من أحكام هذه الاتفاقية، يحق للشركة اتخاذ كافة الإجراءات القانونية اللازمة للمطالبة بالتعويض عن أي أضرار مادية أو معنوية أو خسائر مباشرة أو غير مباشرة ناتجة عن هذا الإخلال، وذلك دون الإخلال بأي حقوق أخرى مقررة بموجب القانون.
    </p>
</div>

<table class="signature-table">
    <tr>
        <td>
            <h3>الطرف الأول (صاحب العمل)</h3>
            <p>شركة Bot Journey</p>
            <p>يمثلها: بكر صالح</p>
            <p>التوقيع: <span class="signature-line"></span></p>
            <p>التاريخ: ____ / ____ / ______</p>
        </td>
        <td>
            <h3>الطرف الثاني (الموظف)</h3>
            <p>الاسم: <span class="signature-line"></span></p>
            <p>التوقيع: <span class="signature-line"></span></p>
            <p>التاريخ: ____ / ____ / ______</p>
        </td>
    </tr>
</table>

</body>
</html>
