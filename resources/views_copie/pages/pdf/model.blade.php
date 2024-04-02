<html>

<head>
    <style>
    @page {
        margin: 1cm 1cm;
    }

    body {
        margin-top: 2.5cm;
        margin-left: 0.3cm;
        margin-right: 0.3cm;
        margin-bottom: 1.5cm;
    }

    header {
        position: fixed;
        top: -0.2cm;
        left: 0.3cm;
        right: 0.3cm;
        height: 2.3cm;
        line-height: 1.5cm;
    }

    footer {
        position: fixed;
        bottom: 0cm;
        left: 0.3cm;
        right: 0.3cm;
        height: 1.5cm;
    }

    .header-title {
        font-size: 12px;
        font-family: "Franklin Gothic Book", sans-serif;
        line-height: 2px;
        text-align: center;
    }

    .footer-text {
        font-size: 9px;
        font-family: "Franklin Gothic Book", sans-serif;
        line-height: 1px;
        text-align: right;
    }

    .nobreak {
        page-break-inside: avoid;
    }
    .page-number:before {
        content: "Page " counter(page);
        font-size: 9px;
        font-family: "Franklin Gothic Book", sans-serif;
    }
    </style>
</head>

<body>
    <header>
        {!! $htmlHeader !!}
    </header>
    <main>
        {!! $htmlMain !!}
    </main>
    <footer>
        {!! $htmlFooter !!} <span class="page-number"></span>
    </footer>

    
</body>

</html>