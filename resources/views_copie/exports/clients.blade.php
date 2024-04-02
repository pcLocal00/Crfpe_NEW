<table>
    <thead>
    <tr>
        <th>Ref</th>
        <th>Type</th>
        <th>Nom</th>
        <th>Etablissement</th>
        <th>Siren</th>
        <th>Siret</th>
        <th>Acronym</th>
        <th>Naf_code</th>
        <th>TVA</th>
        <th>Pro phone</th>
        <th>Pro mobile</th>
        <th>fax</th>
        <th>email</th>
    </tr>
    </thead>
    <tbody>
    @foreach($entities as $entitie)
        <tr>
            <td>{{ $entitie->ref }}</td>
            <td>{{ $entitie->type }}</td>
            <td>{{ $entitie->name }}</td>
            <td>{{ $entitie->type_establishment }}</td>
            <td>{{ $entitie->siren }}</td>
            <td>{{ $entitie->siret }}</td>
            <td>{{ $entitie->acronym }}</td>
            <td>{{ $entitie->naf_code }}</td>
            <td>{{ $entitie->tva }}</td>
            <td>{{ $entitie->pro_phone }}</td>
            <td>{{ $entitie->pro_mobile }}</td>
            <td>{{ $entitie->fax }}</td>
            <td>{{ $entitie->email }}</td>
        </tr>
    @endforeach
    </tbody>
</table>