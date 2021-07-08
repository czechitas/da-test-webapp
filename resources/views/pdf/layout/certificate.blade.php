<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<style type="text/css">
  *{
    margin: auto;
    padding: 0px;
  }
  body{
    color: #000000;;
        font-family: OpenSans;
  }
  h1{
    font-size: 28px;
    text-align: center;
  }

  .row{ border-bottom: 1px solid black; padding-bottom: 10px; }
  .header{ margin: 15px 0 5px; }
  .value{ margin: 10px 0 5px; }

  table{
    border: 0;
    border-spacing: 0;
    border-collapse: collapse;
  }
  th,td{
    vertical-align: top;
  }
  th{
    font-weight: normal;
    text-align: left;
    width: 250px;
  }
  .space{ padding-bottom: 20px; }
  .firstCol{ width: 230px }
  .sign{
    vertical-align: bottom;
    width: 230px;
    text-align: right;
  }

</style>
</head>
<body>
<h1>@yield('title')<br>&quot;{{ $student->term->category->name }}&quot;</h1>

<div class="row">
    <div class="header">Jméno a příjmení dítěte:</div>
    <div class="value">Maria Falterová</div>
</div>

<div class="row">
    <div class="header">Datum narození:</div>
    <div class="value">1.1.1990</div>
</div>

<div class="row">
    <div class="header">Termín kurzu:</div>
    <div class="value">Od: xxx Do: xxx</div>
</div>
<div>
    <div class="header">
        <table>
            @yield('payment_date', '')
            <tr>
                <th class="space firstCol">Místo konání kurzu</th>
                <td colspan="2">Na kopci v Horní Dolní</td>
            </tr>
            <tr>
                <th class="firstCol">Organizátor</th>
                <td>Czechitas<br>
                    Dlouhá 123<br>
                    123 45 Horní Dolní
                </td>
                <td class="sign">
                    <img src="{{ public_path('img/logo.png') }}" width="200">
                </td>
            </tr>
        </table>
    </div>
</div>

</body>
</html>
