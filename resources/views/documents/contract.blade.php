<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>عقد عمل</title>
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
        font-size: 30px;
    }

    .header p {
        margin: 5px 0;
        color: #777;
    }

    .meta {
        margin: 20px 0;
    }

    .meta span {
        display: inline-block;
        margin-left: 30px;
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
        font-size: 18px;
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
    <h1>عقد عمل</h1>
    <p>EMPLOYMENT CONTRACT · INTERNAL TEMPLATE V1.0</p>
</div>

<div class="meta">
    <span>
        <strong>التاريخ:</strong>
        {{ $date }}
    </span>
</div>

<p>
تم إبرام هذا العقد بتاريخ {{ $date }} بين كل من:
</p>

<p>
<strong>الطرف الأول (الشركة):</strong>
شركة Bot Journey، ويمثلها السيد بكر صالح، ويشار إليها فيما بعد بـ
<strong>"صاحب العمل"</strong>.
</p>

<p>
<strong>الطرف الثاني (الموظف):</strong>
السيد/السيدة:
<span class="field" style="min-width:300px;">{{ $employee_name }}</span>
ويشار إليه/إليها فيما بعد بـ
<strong>"الموظف"</strong>.
</p>

<p>
وقد اتفق الطرفان وهما بكامل الأهلية القانونية على ما يلي:
</p>

<!-- المادة الأولى -->
<div class="article">
    <div class="article-title">المادة الأولى — موضوع العقد</div>
    <p>
        يعمل الموظف لدى شركة Bot Journey بوظيفة
        <span class="field">{{ $position }}</span>
        ويلتزم بأداء جميع المهام والمسؤوليات المتعلقة بوظيفته وأي مهام أخرى ذات صلة يُكلّف بها من قبل صاحب العمل ضمن حدود اختصاصه وقدراته المهنية.
    </p>
</div>

<!-- المادة الثانية -->
<div class="article">
    <div class="article-title">المادة الثانية — مدة العقد والتجديد</div>
    <p>
        عقد محدد المدة لمدة سنة واحدة تبدأ من تاريخ
        {{ $hire_date }}
        وتنتهي بتاريخ
        {{ $hire_date_plus_year }}
    </p>
    <p>
        ويُجدّد العقد لمدة سنة أخرى عند اتفاق الطرفين خطياً على ذلك قبل انتهاء مدته، وإلا اعتُبر منتهياً بانتهاء مدته دون حاجة إلى إشعار.
    </p>
</div>

<!-- المادة الثالثة -->
<div class="article">
    <div class="article-title">المادة الثالثة — مكان العمل</div>
    <p>
        يؤدي الموظف مهامه عن بُعد بشكل أساسي، مع الحضور الحضوري بشكل دوري بمعدل مرة واحدة أسبوعياً تقريباً في مكان يتفق عليه الطرفان، وذلك لاجتماع الفريق والعمل المشترك.
    </p>
</div>

<!-- المادة الرابعة -->
<div class="article">
    <div class="article-title">المادة الرابعة — فترة التجربة</div>
    <p>
        يخضع الموظف لفترة تجربة مدتها ثلاثة (3) أشهر من تاريخ مباشرته العمل، ويحق لأي من الطرفين إنهاء العقد خلال هذه الفترة دون تعويض في حال عدم الملاءمة، مع إشعار الطرف الآخر بذلك.
    </p>
</div>

<!-- المادة الخامسة -->
<div class="article">
    <div class="article-title">المادة الخامسة — ساعات العمل</div>
    <p>
        يلتزم الموظف بساعات العمل المعتمدة لدى الشركة بما يتوافق مع القانون، ويجوز تكليفه بساعات عمل إضافية عند الحاجة وفقاً للأنظمة والقوانين النافذة.
    </p>
</div>

<!-- المادة السادسة -->
<div class="article">
    <div class="article-title">المادة السادسة — الراتب</div>
    <p>
        يتقاضى الموظف راتباً شهرياً إجمالياً مقداره
        <span class="field">{{ $salary_amount ?? '___________' }}</span>
        دينار أردني، يُدفع نقداً في نهاية كل شهر ميلادي دون أي اقتطاعات.
    </p>
    <p>
        ويتحمّل الموظف مسؤولية أي التزامات ضريبية قد تترتب على هذا الدخل.
    </p>
</div>

<!-- المادة السابعة -->
<div class="article">
    <div class="article-title">المادة السابعة — الإجازات</div>
    <p>
        يستحق الموظف الإجازات السنوية والمرضية والرسمية وأي إجازات أخرى وفقاً لأحكام القانون والأنظمة المعمول بها في الشركة.
    </p>
</div>

<!-- المادة الثامنة -->
<div class="article">
    <div class="article-title">المادة الثامنة — السرية والملكية الفكرية</div>
    <p>
        يلتزم الموظف بالحفاظ على سرية جميع المعلومات والبيانات والوثائق المتعلقة بالشركة أو عملائها أو مشاريعها، وعدم الإفصاح عنها لأي طرف ثالث أثناء فترة العمل أو بعد انتهائها.
    </p>
    <p>
        كما يلتزم بالتوقيع على اتفاقية عدم إفشاء المعلومات (NDA) المعتمدة لدى الشركة والالتزام بكافة بنودها.
    </p>
    <p>
        وتُعتبر جميع الأعمال والبرامج والتصاميم والوثائق والأفكار والابتكارات التي يطوّرها أو ينتجها الموظف خلال فترة عمله لصالح الشركة ملكاً حصرياً لشركة Bot Journey، ما لم يُتفق خطياً على خلاف ذلك.
    </p>
</div>

<!-- المادة التاسعة -->
<div class="article">
    <div class="article-title">المادة التاسعة — إنهاء العقد</div>
    <p>
        يجوز لأي من الطرفين إنهاء هذا العقد قبل انتهاء مدته بموجب إشعار خطي مسبق مدته ثلاثون (30) يوماً، دون أي تعويض عن الإنهاء المبكر، مع احتفاظ كل طرف بأي مستحقات مالية فعلية قائمة حتى تاريخ الإنهاء.
    </p>
</div>

<!-- المادة العاشرة -->
<div class="article">
    <div class="article-title">المادة العاشرة — القانون الواجب التطبيق والاختصاص القضائي</div>
    <p>
        يخضع هذا العقد في تفسيره وتنفيذه لأحكام قوانين المملكة الأردنية الهاشمية، وتختص المحاكم الأردنية المختصة بالنظر في أي نزاع ينشأ عن هذا العقد أو يتعلق به.
    </p>
</div>

<p>
حُرر هذا العقد من نسختين أصليتين، بيد كل طرف نسخة للعمل بموجبها.
</p>

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
