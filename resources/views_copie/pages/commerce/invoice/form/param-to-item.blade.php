<tr id="TR_ID_{{$i}}">
    <td>
        <div class="checkbox-inline">
            <label class="checkbox"><input type="checkbox" value="1" name="items[{{$i}}][active]" checked><span></span></label>
        </div>
    </td>
    <td>
        <select id="SELECT_PARAMS_{{$i}}" onchange="_onchangeSelectParams({{$i}})" class="form-control">
            @foreach($params as $p)
            <option value="{{$p->id}}">{{$p->name}}</option>
            @endforeach
        </select>
        <input type="hidden" name="items[{{$i}}][title]" id="title_p_{{$i}}">
    </td>
    <td><input type="text" class="form-control" name="items[{{$i}}][description]" value="" /></td>
    <td><input type="text" class="form-control" name="items[{{$i}}][accounting_code]" value="" id="accounting_code_p_{{$i}}" /></td>
    <td><input type="text" class="form-control" name="items[{{$i}}][analytical_code]" value="" id="analytical_code_p_{{$i}}" /></td>
    <td><input type="number" class="form-control" name="items[{{$i}}][rate]" value="" id="rate_p_{{$i}}"/></td>
    <td>
        <button type="button" class="btn btn-sm btn-clean btn-icon" onclick="_deleteItem({{$i}})" title="Supprimer"><i class="flaticon-delete"></i></button>
    </td>
</tr>