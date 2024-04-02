<table>
    <thead>
    <tr>
        <th>genre</th>
        <th>Prénom</th>
        <th>Nom</th>
        <th>email</th>
        <th>Pro phone</th>
        <th>Pro mobile</th>
        <th>Fonction</th> 
        <th>Entité</th> 
    </tr>
    </thead>
    <tbody>
    @foreach($personnes as $personne)
        <tr>
            <td>{{ $personne->gender }}</td>
            <td>{{ $personne->firstname }}</td>
            <td>{{ $personne->lastname }}</td>
            <td>{{ $personne->email }}</td>
            <td>{{ $personne->pro_phone }}</td>
            <td>{{ $personne->pro_mobile }}</td>
            <td>{{ $personne->function }}</td> 
            <td>{{ ($personne->entitie->entity_type == 'S')? $personne->entitie->name : '' }}</td> 
        </tr>
    @endforeach
    </tbody>
</table>