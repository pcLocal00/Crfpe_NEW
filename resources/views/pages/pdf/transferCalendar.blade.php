<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Rapport de transfert Calendrier Document</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }
        th {
            background-color: #dddddd;
        }
        h3{
            text-align: center;
            font-size: 24px;
            color: #333;
            margin-top: 30px;
            margin-bottom: 30px;
        }
        h4{
            text-align: center;
            font-size: 20px;
            color: #333;
            margin-top: 30px;
            margin-bottom: 30px;
        }
        span{
            text-align: center;
            font-size: 20px;
            color: #3333339c;
        }
    </style>
</head>
<body>
    <h3>Rapport de transfert Calendrier Document</h3>
    <h4><span>Pré planification Titre : </span> {{ $preplanning['title'] }}</h4>
    <table>
        <thead>
            <tr>
                <th>Titre</th>
                <th>Date ( Heure début / Heure fin ) </th>
                <th>Group/Regroupement</th>
                <th>Formateur principal / type</th>
                <th>Tarif</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tableData as $item)
                <tr>
                    <td>{{ $item['title'] }}</td>
                    <td>{{ date("Y-m-d", strtotime($item['start_hour'])) }} ( {{ date("H:i", strtotime($item['start_hour'])) }} / {{ date("H:i", strtotime($item['end_hour'])) }} )</td>
                    <td>{{ $item['group'] }}</td>
                    <td>{{ $item['formateur'] }} / {{ $item['formateur_type'] }} </td>
                    <td>{{ $item['price'] }} </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div style="text-align:right;font-size:16px;margin-top:50px;">
        Date de téléchargement du Rapport: {{ date("Y-m-d") }}
    </div>
</body>
</html>
