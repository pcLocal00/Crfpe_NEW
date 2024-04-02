@php
$percent = '';
if($discount_amount_type=='percentage'){
$percent = '('.$discount_amount.'%)';
}
@endphp
<!-- DISCOUNT FORM -->
<div class="row">
    <div class="col-md-12" id="BLOCK_DISCOUNT"></div>
</div>
<!-- DISCOUNT FORM -->

<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th class="pl-0 font-weight-bold text-muted text-uppercase">
                    Designation</th>
                <th class="text-right font-weight-bold text-muted text-uppercase">
                    Quantité</th>
                <th class="text-right font-weight-bold text-muted text-uppercase">
                    Prix unitaire</th>
                <th class="text-right pr-0 font-weight-bold text-muted text-uppercase">
                    Total</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="TBODY_TABLE_ITEMS">
            @if(count($items)>0)
            @foreach($items as $item)
            <tr class="">
                <td class="pl-0 pt-7">
                    <p><strong>{{$item->title}}</strong></p>
                    <p class="mb-0">{!!$item->description!!}</p>
                </td>
                <td class="text-right pt-7">{{$item->quantity}} {{$item->unit_type}}</td>
                <td class="text-right pt-7">{{number_format($item->rate,2)}} €</td>
                <td class="text-success pr-0 pt-7 text-right">{{number_format($item->total,2)}} €</td>
                <th>
                    <button type="button" class="btn btn-sm btn-clean btn-icon"
                        onclick="_formEstimateItem({{$item->id}},{{$item->estimate_id}})" title="Edition"><i
                            class="flaticon-edit"></i></button>
                </th>
            </tr>
            @endforeach
            <!-- BEGIN:ESTIMATE FOOTER -->
            <tr class="font-weight-boldest">
                <td colspan="2" class="text-right pt-7">Sous-Total</td>
                <td colspan="2" class="text-success pr-0 pt-7 text-right">{{$calcul['sous_total']}} €</td>
                <td></td>
            </tr>

            @if($discount_type =='before_tax')

            <tr class="font-weight-boldest">
                <!-- <td colspan="2" class="text-right pt-7">Remise {{$percent}}</td> -->
                <td colspan="2" class="text-right pt-7">{{$discount_label ?? 'Remise'}} {{$percent}}</td>
                <td colspan="2" class="text-danger pr-0 pt-7 text-right">{{$calcul['discount_amount']}} €</td>
                <td>
                    <button type="button" class="btn btn-sm btn-clean btn-icon"
                        onclick="_formDiscount({{$item->estimate_id}})" data-toggle="tooltip"
                        title="Modifier la remise"><i class="flaticon-edit"></i></button>
                </td>
            </tr>
            @endif

            @if($tax_percentage>0)
            <tr class="font-weight-boldest">
                <td colspan="2" class="text-right pt-7">Tax ({{$tax_percentage}}%)</td>
                <td colspan="2" class="text-danger pr-0 pt-7 text-right">{{$calcul['tax_amount']}} €</td>
                <td></td>
            </tr>
            @endif

            @if($discount_type =='after_tax')
            <tr class="font-weight-boldest">
                <td colspan="2" class="text-right pt-7">Remise {{$percent}}</td>
                <td colspan="2" class="text-danger pr-0 pt-7 text-right">{{$calcul['discount_amount']}} €</td>
                <td>
                    <button type="button" class="btn btn-sm btn-clean btn-icon"
                        onclick="_formDiscount({{$item->estimate_id}})" data-toggle="tooltip"
                        title="Modifier la remise"><i class="flaticon-edit"></i></button>
                </td>
            </tr>
            @endif

            <tr class="font-weight-boldest">
                <td colspan="2" class="text-right pt-7">Total</td>
                <td colspan="2" class="text-success pr-0 pt-7 text-right">{{$calcul['total']}} €</td>
                <td></td>
            </tr>
            <!-- END:ESTIMATE FOOTER -->
            @else
            <tr>
                <td colspan="5" class="text-center">Pas d'élement sur ce devis</td>
            </tr>
            @endif
        </tbody>
    </table>
</div>